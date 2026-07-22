<?php

namespace Loom73\Yarn;

use InvalidArgumentException;
use Loom73\Woodframe\Config;
use Throwable;

class AssetUploader
{
    protected array $config;

    protected AssetValidator $Validator;

    protected AssetStorage $Storage;

    protected Asset $Asset;

    protected AssetPolicy $Policy;

    public function __construct(
        ?array $config = null,
        ?AssetValidator $Validator = null,
        ?AssetStorage $Storage = null,
        ?Asset $Asset = null,
        ?AssetPolicy $Policy = null
    ) {
        $this->config = $config ?? Config::get('yarn');

        $this->Validator = $Validator ?? new AssetValidator($this->config);
        $this->Storage = $Storage ?? new AssetStorage($this->config);
        $this->Asset = $Asset ?? new Asset();
        $this->Policy = $Policy ?? new AssetPolicy();
    }

    public function upload(
        array $file,
        string $ownerType,
        string $ownerId,
        string $ownerSlot,
        ?string $assetType = null,
        ?int $uploadedBy = null,
        ?string $visibility = null,
        ?bool $replaceExisting = null
    ): AssetUploadResult {
        try {
            $policy = $this->Policy->resolve(
                ownerType: $ownerType,
                ownerSlot: $ownerSlot,
                assetType: $assetType,
                visibility: $visibility,
                replaceExisting: $replaceExisting
            );
        } catch (Throwable $e) {
            return AssetUploadResult::failure(error: $e);
        }

        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return AssetUploadResult::failure(
                error: new InvalidArgumentException('No uploaded file was provided.')
            );
        }

        $validation = $this->Validator->validateUploadedFile($file);

        if ($validation->fails()) {
            return AssetUploadResult::failure(
                validation: $validation,
                error: new InvalidArgumentException(implode(' ', $validation->errors))
            );
        }

        try {
            $stored = $this->Storage->storeUploadedFile(
                tmpPath: $file['tmp_name'],
                extension: $validation->extension,
                originalName: $validation->originalName,
            );
        } catch (Throwable $e) {
            return AssetUploadResult::failure(
                validation: $validation,
                error: $e
            );
        }

        /*
         * replace_existing will be implemented next.
         * For now the policy resolves it, but the uploader does not act on it yet.
         */
        $asset = $this->Asset->register(
            storedFile: $stored,
            validation: $validation,
            ownerType: $policy['owner_type'],
            ownerId: $ownerId,
            ownerSlot: $policy['owner_slot'],
            assetType: $policy['asset_type_id'],
            uploadedBy: $uploadedBy,
            visibility: $policy['visibility']
        );

        if ($asset->fails()) {
            $this->cleanupStoredFile($stored);

            return AssetUploadResult::failure(
                asset: $asset,
                storedFile: $stored,
                validation: $validation,
                error: $asset->exception()
            );
        }

        if ($asset->passes() && $policy['replace_existing']) {
            $this->Asset->deactivateForOwnerSlotExcept(
                ownerType: $policy['owner_type'],
                ownerId: $ownerId,
                ownerSlot: $policy['owner_slot'],
                exceptAssetId: $asset->insertId
            );
        }

        return AssetUploadResult::success(
            asset: $asset,
            storedFile: $stored,
            validation: $validation
        );
    }

    /**
     * Upload many files in one go. The method return an array of AssetUploadResult objects
     * @param array $files the array of files normalized by Ctrl::uploadedFiles()
     * @param string|null $ownerType The Owner of the resource, at the system level (i.e. the logical entity in the database)
     * @param string|null $ownerId Id of the Owner (unique identifier of the record within the logical)
     * @param string|null $ownerSlot Slot of the owner (avatar, report, pictures...)
     * @param int|null $assetType
     * @param int|null $uploadedBy
     * @param string $visibility
     * @param bool $replaceExisting
     * @return array
     */
    public function uploadMany(
        array $files,
        ?string $ownerType = null,
        ?string $ownerId = null,
        ?string $ownerSlot = null,
        ?int $assetType = null,
        ?int $uploadedBy = null,
        string $visibility = Asset::VISIBILITY_PRIVATE,
        bool $replaceExisting = false
    ): array {
        $results = [];

        foreach ($files as $file) {
            $results[] = $this->upload(
                file: $file,
                ownerType: $ownerType,
                ownerId: $ownerId,
                ownerSlot: $ownerSlot,
                assetType: $assetType,
                uploadedBy: $uploadedBy,
                visibility: $visibility,
                replaceExisting: $replaceExisting
            );
        }

        return $results;
    }

    protected function cleanupStoredFile(array $stored): void
    {
        $path = $stored['absolute_path'] ?? null;

        if (!$path || !is_file($path)) {
            return;
        }

        @unlink($path);
    }
}
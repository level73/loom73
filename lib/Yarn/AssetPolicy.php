<?php

namespace Loom73\Yarn;

use InvalidArgumentException;
use Loom73\Woodframe\OwnerRegistry;

class AssetPolicy
{
    protected OwnerRegistry $Owners;
    protected AssetType $AssetType;

    public function __construct(
        ?OwnerRegistry $Owners = null,
        ?AssetType $AssetType = null
    )
    {
        $this->Owners = $Owners ?? new OwnerRegistry();
        $this->AssetType = $AssetType ?? new AssetType();
    }

    public function resolve(
        string $ownerType,
        string $ownerSlot,
        ?string $assetType = null,
        ?string $visibility = null,
        ?bool $replaceExisting = null
    ): array {
        if (!$this->Owners->has($ownerType)) {
            throw new InvalidArgumentException("Unknown owner type: {$ownerType}");
        }

        if (!$this->Owners->hasAssetSlot($ownerType, $ownerSlot)) {
            throw new InvalidArgumentException(
                "Unknown asset slot '{$ownerSlot}' for owner '{$ownerType}'."
            );
        }

        $slot = $this->Owners->assetSlot($ownerType, $ownerSlot);

        $assetType ??= $slot['default_asset_type'] ?? null;

        if (!$assetType) {
            throw new InvalidArgumentException(
                "Missing asset type for slot '{$ownerSlot}' on owner '{$ownerType}'."
            );
        }

        $assetTypeId = $this->AssetType->idFromSlug($assetType);

        if ($assetTypeId === null) {
            throw new InvalidArgumentException("Unknown asset type: {$assetType}");
        }

        if (!$this->Owners->slotAllowsAssetType($ownerType, $ownerSlot, $assetType)) {
            throw new InvalidArgumentException(
                "Asset type '{$assetType}' is not allowed for slot '{$ownerSlot}' on owner '{$ownerType}'."
            );
        }

        $visibility ??= $slot['default_visibility'] ?? Asset::VISIBILITY_PRIVATE;

        $this->assertVisibility($visibility);

        $allowedVisibility = $slot['allowed_visibility'] ?? [];

        if (!empty($allowedVisibility) && !in_array($visibility, $allowedVisibility, true)) {
            throw new InvalidArgumentException(
                "Visibility '{$visibility}' is not allowed for slot '{$ownerSlot}' on owner '{$ownerType}'."
            );
        }

        return [
            'owner_type' => $ownerType,
            'owner_slot' => $ownerSlot,
            'asset_type_slug' => $assetType,
            'asset_type_id' => $assetTypeId,
            'visibility' => $visibility,
            'multiple' => (bool) ($slot['multiple'] ?? false),
            'replace_existing' => $replaceExisting
                ?? (bool) ($slot['replace_existing'] ?? false),
        ];
    }

    protected function assertVisibility(string $visibility): void
    {
        if (!in_array($visibility, [
            Asset::VISIBILITY_PRIVATE,
            Asset::VISIBILITY_RESTRICTED,
            Asset::VISIBILITY_PUBLIC,
        ], true)) {
            throw new InvalidArgumentException("Unsupported asset visibility: {$visibility}");
        }
    }
}
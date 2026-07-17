<?php

namespace Loom73\Yarn;

use InvalidArgumentException;
use Loom73\Beam\Model;
use Loom73\Beam\QueryResult;
use PDO;

class Asset extends Model
{
    protected string $table = 'assets';

    protected string $pkey = 'idasset';

    protected bool $dates = true;

    public const VISIBILITY_PRIVATE = 'private';
    public const VISIBILITY_RESTRICTED = 'restricted';
    public const VISIBILITY_PUBLIC = 'public';

    protected bool $softDeletes = true;

    protected string $statusColumn = 'status';

    /**
     * Register a stored file into the assets table.
     *
     * Asset does not validate or move files.
     * It only records metadata for a file that already exists in storage.
     */
    public function register(
        array $storedFile,
        object $validation,
        ?string $ownerType = null,
        ?string $ownerId = null,
        ?string $ownerSlot = null,
        ?string $assetType = null,
        ?int $uploadedBy = null,
        string $visibility = self::VISIBILITY_PRIVATE
    ): QueryResult {
        $this->assertStoredFile($storedFile);
        $this->assertVisibility($visibility);

        $absolutePath = $storedFile['absolute_path'];

        $data = [
            'uuid' => [
                'value' => $this->uuid(),
                'type' => PDO::PARAM_STR,
            ],

            'owner_type' => [
                'value' => $ownerType,
                'type' => $ownerType === null ? PDO::PARAM_NULL : PDO::PARAM_STR,
            ],

            'owner_id' => [
                'value' => $ownerId,
                'type' => $ownerId === null ? PDO::PARAM_NULL : PDO::PARAM_STR,
            ],

            'owner_slot' => [
                'value' => $ownerSlot,
                'type' => $ownerSlot === null ? PDO::PARAM_NULL : PDO::PARAM_STR,
            ],

            'asset_type' => [
                'value' => $assetType,
                'type' => $assetType === null ? PDO::PARAM_NULL : PDO::PARAM_INT,
            ],

            'original_name' => [
                'value' => $validation->originalName,
                'type' => PDO::PARAM_STR,
            ],

            'stored_name' => [
                'value' => $storedFile['stored_name'],
                'type' => PDO::PARAM_STR,
            ],

            'disk_path' => [
                'value' => $storedFile['relative_path'],
                'type' => PDO::PARAM_STR,
            ],

            'mime_type' => [
                'value' => $validation->mimeType,
                'type' => PDO::PARAM_STR,
            ],

            'extension' => [
                'value' => $validation->extension,
                'type' => PDO::PARAM_STR,
            ],

            'size_bytes' => [
                'value' => $validation->sizeBytes,
                'type' => PDO::PARAM_INT,
            ],

            'checksum_sha256' => [
                'value' => hash_file('sha256', $absolutePath),
                'type' => PDO::PARAM_STR,
            ],

            'visibility' => [
                'value' => $visibility,
                'type' => PDO::PARAM_STR,
            ],

            'uploaded_by' => [
                'value' => $uploadedBy,
                'type' => $uploadedBy === null ? PDO::PARAM_NULL : PDO::PARAM_INT,
            ],
        ];

        return $this->create($data);
    }

    public function getByUuid(string $uuid): QueryResult
    {
        return $this->getBy([
            'uuid' => [
                'operator' => '=',
                'value' => $uuid,
                'type' => PDO::PARAM_STR,
            ],
        ], limit: 1);
    }

    public function getByOwner(
        int $ownerType,
        string $ownerId,
        ?string $ownerSlot = null
    ): QueryResult {
        $conditions = [
            'owner_type' => [
                'operator' => '=',
                'value' => $ownerType,
                'type' => PDO::PARAM_INT,
            ],

            'owner_id' => [
                'operator' => '=',
                'value' => $ownerId,
                'type' => PDO::PARAM_STR,
            ],
        ];

        if ($ownerSlot !== null) {
            $conditions['owner_slot'] = [
                'operator' => '=',
                'value' => $ownerSlot,
                'type' => PDO::PARAM_STR,
            ];
        }

        return $this->getBy(
            conditions: $conditions,
            orderBy: 'created_at',
            direction: 'DESC'
        );
    }

    public function getPublicByUuid(string $uuid): QueryResult
    {
        return $this->getBy([
            'uuid' => [
                'operator' => '=',
                'value' => $uuid,
                'type' => PDO::PARAM_STR,
            ],

            'visibility' => [
                'operator' => '=',
                'value' => self::VISIBILITY_PUBLIC,
                'type' => PDO::PARAM_STR,
            ],
        ], limit: 1);
    }


    /**
     * Get the latest asset for the slot (i.e. the Avatar of a user)
     * @param string $ownerType the owner type as the slug
     * @param string $ownerId the actual record id of the owner
     * @param string $ownerSlot the slot slug
     * @return QueryResult
     */
    public function latestForOwnerSlot(
        string $ownerType,
        string $ownerId,
        string $ownerSlot
    ): QueryResult {
        return $this->getBy(
            conditions: [
                'owner_type' => [
                    'operator' => '=',
                    'value' => $ownerType,
                    'type' => PDO::PARAM_STR,
                ],
                'owner_id' => [
                    'operator' => '=',
                    'value' => $ownerId,
                    'type' => PDO::PARAM_STR,
                ],
                'owner_slot' => [
                    'operator' => '=',
                    'value' => $ownerSlot,
                    'type' => PDO::PARAM_STR,
                ],
                'status' => [
                    'operator' => '=',
                    'value' => self::STATUS_ACTIVE,
                    'type' => PDO::PARAM_INT,
                ],
            ],
            limit: 1,
            orderBy: 'created_at',
            direction: 'DESC'
        );
    }

    /**
     * Deactivate (soft delete) based on owner slots (i.e. deactivate older avatars. preserve latest upload)
     * @param string $ownerType
     * @param string $ownerId
     * @param string $ownerSlot
     * @param int $exceptAssetId
     * @return QueryResult
     */
    public function deactivateForOwnerSlotExcept(
        string $ownerType,
        string $ownerId,
        string $ownerSlot,
        int $exceptAssetId
    ): QueryResult {
        $sql = '
        UPDATE ' . $this->tableName() . '
        SET `status` = :inactive
        WHERE `owner_type` = :owner_type
        AND `owner_id` = :owner_id
        AND `owner_slot` = :owner_slot
        AND `' . $this->pkey . '` != :except_asset
    ';

        return $this->query($sql, [
            'inactive' => [
                'value' => self::STATUS_INACTIVE,
                'type' => PDO::PARAM_INT,
            ],
            'owner_type' => [
                'value' => $ownerType,
                'type' => PDO::PARAM_STR,
            ],
            'owner_id' => [
                'value' => $ownerId,
                'type' => PDO::PARAM_STR,
            ],
            'owner_slot' => [
                'value' => $ownerSlot,
                'type' => PDO::PARAM_STR,
            ],
            'except_asset' => [
                'value' => $exceptAssetId,
                'type' => PDO::PARAM_INT,
            ],
        ]);
    }

    /**
     * Soft delete by UUID
     */
    public function deactivateByUuidForOwner(
        string $uuid,
        string $ownerType,
        string $ownerId,
        ?string $ownerSlot = null
    ): QueryResult {
        $conditions = [
            'uuid' => [
                'operator' => '=',
                'value' => $uuid,
                'type' => PDO::PARAM_STR,
            ],
            'owner_type' => [
                'operator' => '=',
                'value' => $ownerType,
                'type' => PDO::PARAM_STR,
            ],
            'owner_id' => [
                'operator' => '=',
                'value' => $ownerId,
                'type' => PDO::PARAM_STR,
            ],
        ];

        if ($ownerSlot !== null) {
            $conditions['owner_slot'] = [
                'operator' => '=',
                'value' => $ownerSlot,
                'type' => PDO::PARAM_STR,
            ];
        }

        return $this->updateWhere([
            'status' => [
                'value' => self::STATUS_INACTIVE,
                'type' => PDO::PARAM_INT,
            ],
        ], $conditions);
    }

    protected function assertStoredFile(array $storedFile): void
    {
        foreach (['stored_name', 'relative_path', 'absolute_path'] as $key) {
            if (empty($storedFile[$key])) {
                throw new InvalidArgumentException("Stored file is missing required key: {$key}");
            }
        }

        if (!is_file($storedFile['absolute_path'])) {
            throw new InvalidArgumentException('Stored file does not exist on disk.');
        }
    }

    protected function assertVisibility(string $visibility): void
    {
        if (!in_array($visibility, [
            self::VISIBILITY_PRIVATE,
            self::VISIBILITY_RESTRICTED,
            self::VISIBILITY_PUBLIC,
        ], true)) {
            throw new InvalidArgumentException("Unsupported asset visibility: {$visibility}");
        }
    }

    protected function uuid(): string
    {
        $data = random_bytes(16);

        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
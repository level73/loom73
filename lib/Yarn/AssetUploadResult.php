<?php

namespace Loom73\Yarn;

use Loom73\Beam\QueryResult;
use Throwable;

class AssetUploadResult
{
    public function __construct(
        public readonly bool $success,
        public readonly ?QueryResult $asset = null,
        public readonly ?array $storedFile = null,
        public readonly ?AssetValidationResult $validation = null,
        public readonly ?Throwable $error = null,
    ) {}

    public static function success(
        QueryResult $asset,
        array $storedFile,
        AssetValidationResult $validation
    ): self {
        return new self(
            success: true,
            asset: $asset,
            storedFile: $storedFile,
            validation: $validation,
        );
    }

    public static function failure(
        ?Throwable $error = null,
        ?QueryResult $asset = null,
        ?array $storedFile = null,
        ?AssetValidationResult $validation = null
    ): self {
        return new self(
            success: false,
            asset: $asset,
            storedFile: $storedFile,
            validation: $validation,
            error: $error,
        );
    }

    public function passes(): bool
    {
        return $this->success;
    }

    public function fails(): bool
    {
        return !$this->success;
    }

    public function insertId(): ?int
    {
        return $this->asset?->insertId;
    }

    public function errorMessage(): ?string
    {
        if ($this->error) {
            return $this->error->getMessage();
        }

        if ($this->validation?->fails()) {
            return implode(' ', $this->validation->errors);
        }

        if ($this->asset?->fails()) {
            return $this->asset->errorMessage();
        }

        return null;
    }
}
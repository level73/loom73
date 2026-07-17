<?php

namespace Loom73\Yarn;

class AssetValidationResult
{
    public function __construct(
        public readonly bool $valid,
        public readonly array $errors = [],
        public readonly ?string $originalName = null,
        public readonly ?string $extension = null,
        public readonly ?string $mimeType = null,
        public readonly ?int $sizeBytes = null,
    ) {}

    public function fails(): bool
    {
        return !$this->valid;
    }

    public function passes(): bool
    {
        return $this->valid;
    }

    public function firstError(): ?string
    {
        return $this->errors[0] ?? null;
    }
}
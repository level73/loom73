<?php

namespace Loom73\Beam;
use Throwable;

class QueryResult
{
    public function __construct(
        public readonly bool $success,
        public readonly int $rows = 0,
        public readonly array $data = [],
        public readonly ?int $insertId = null,
        public readonly ?Throwable $error = null,
    ) {}

    public static function success(
        int $rows = 0,
        array $data = [],
        ?int $insertId = null
    ): self {
        return new self(
            success: true,
            rows: $rows,
            data: $data,
            insertId: $insertId
        );
    }

    public static function failure(Throwable $error): self
    {
        return new self(
            success: false,
            error: $error
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

    public function first(): ?object
    {
        return $this->data[0] ?? null;
    }

    public function all(): array
    {
        return $this->data;
    }

    public function count(): int
    {
        return count($this->data);
    }

    public function hasData(): bool
    {
        return !empty($this->data);
    }

    public function isEmpty(): bool
    {
        return empty($this->data);
    }

    public function map(callable $callback): array
    {
        return array_map($callback, $this->data);
    }

    public function pluck(string $property): array
    {
        return array_map(
            fn (object $row) => $row->{$property} ?? null,
            $this->data
        );
    }

    public function errorMessage(): ?string
    {
        return $this->error?->getMessage();
    }

    public function errorCode(): int|string|null
    {
        return $this->error?->getCode();
    }

    public function exception(): ?Throwable
    {
        return $this->error;
    }

    public function databaseErrorCode(): int|string|null
    {
        if ($this->error instanceof \PDOException) {
            return $this->error->errorInfo[1] ?? $this->error->getCode();
        }

        return $this->error?->getCode();
    }
}
<?php

namespace Loom73\Yarn;

use RuntimeException;

class AssetStorage
{
    protected string $storagePath;

    protected string $uploadPath;

    protected string $tempPath;

    protected string $directoryStrategy;

    protected string $storedNameStrategy;

    protected bool $preserveOriginalName;

    public function __construct(array $config)
    {
        $this->storagePath = rtrim($config['storage_path'] ?? '', '/\\');
        //$this->storagePath = rtrim($config['storage_path'] ?? '', DIRECTORY_SEPARATOR);
        $this->uploadPath = trim($config['upload_path'] ?? 'uploads', DIRECTORY_SEPARATOR);
        $this->tempPath = trim($config['temp_path'] ?? 'tmp', DIRECTORY_SEPARATOR);

        $this->directoryStrategy = $config['directory_strategy'] ?? 'date';

        $this->storedNameStrategy = $config['naming']['stored_name_strategy'] ?? 'uuid';
        $this->preserveOriginalName = (bool) ($config['naming']['preserve_original_name'] ?? false);

        if ($this->storagePath === '') {
            throw new RuntimeException('Yarn storage_path is not configured.');
        }
    }

    /**
     * Store a file uploaded through HTTP POST.
     *
     * This method should be used with $_FILES['field']['tmp_name'].
     */
    public function storeUploadedFile(
        string $tmpPath,
        string $extension,
        ?string $originalName = null
    ): array {
        if (!is_uploaded_file($tmpPath)) {
            throw new RuntimeException('The given file was not uploaded through HTTP POST.');
        }

        $targetDirectory = $this->getUploadDirectory();
        $this->ensureDirectoryExists($targetDirectory);

        $storedName = $this->makeStoredName($extension, $originalName);
        $absolutePath = $targetDirectory . DIRECTORY_SEPARATOR . $storedName;

        if (!move_uploaded_file($tmpPath, $absolutePath)) {
            throw new RuntimeException('Unable to move uploaded file.');
        }

        return [
            'stored_name' => $storedName,
            'relative_path' => $this->makeRelativePath($absolutePath),
            'absolute_path' => $absolutePath,
        ];
    }

    /**
     * Store a local file that is already on disk.
     *
     * Useful for tests, generated files, imports, conversions,
     * or future derivative generation.
     */
    public function storeLocalFile(
        string $sourcePath,
        string $extension,
        ?string $originalName = null
    ): array {
        if (!is_file($sourcePath)) {
            throw new RuntimeException("Source file not found: {$sourcePath}");
        }

        $targetDirectory = $this->getUploadDirectory();
        $this->ensureDirectoryExists($targetDirectory);

        $storedName = $this->makeStoredName($extension, $originalName);
        $absolutePath = $targetDirectory . DIRECTORY_SEPARATOR . $storedName;

        if (!copy($sourcePath, $absolutePath)) {
            throw new RuntimeException('Unable to copy local file.');
        }

        return [
            'stored_name' => $storedName,
            'relative_path' => $this->makeRelativePath($absolutePath),
            'absolute_path' => $absolutePath,
        ];
    }

    public function delete(string $relativePath): bool
    {
        $absolutePath = $this->absolutePath($relativePath);

        if (!is_file($absolutePath)) {
            return false;
        }

        return unlink($absolutePath);
    }

    public function exists(string $relativePath): bool
    {
        return is_file($this->absolutePath($relativePath));
    }

    public function absolutePath(string $relativePath): string
    {
        $relativePath = trim($relativePath, DIRECTORY_SEPARATOR);

        $absolutePath = $this->storagePath . DIRECTORY_SEPARATOR . $relativePath;

        $this->assertPathIsInsideStorage($absolutePath);

        return $absolutePath;
    }

    public function storagePath(): string
    {
        return $this->storagePath;
    }

    public function uploadRootPath(): string
    {
        return $this->storagePath . DIRECTORY_SEPARATOR . $this->uploadPath;
    }

    public function tempRootPath(): string
    {
        return $this->storagePath . DIRECTORY_SEPARATOR . $this->tempPath;
    }

    protected function getUploadDirectory(): string
    {
        $base = $this->uploadRootPath();

        return match ($this->directoryStrategy) {
            'date' => $base . DIRECTORY_SEPARATOR . date('Y') . DIRECTORY_SEPARATOR . date('m'),
            'flat' => $base,
            default => throw new RuntimeException("Unsupported Yarn directory strategy: {$this->directoryStrategy}"),
        };
    }

    protected function ensureDirectoryExists(string $directory): void
    {
        $this->assertPathIsInsideStorage($directory);

        if (is_dir($directory)) {
            return;
        }

        $parent = dirname($directory);

        if (!is_dir($parent)) {
            $this->ensureDirectoryExists($parent);
        }
        if (is_dir($parent) && !is_writable($parent)) {
            throw new RuntimeException("Directory is not writable: {$parent}");
        }
        if (!mkdir($directory, 0755) && !is_dir($directory)) {
            throw new RuntimeException("Unable to create directory: {$directory}");
        }
    }

    protected function makeStoredName(string $extension, ?string $originalName = null): string
    {
        $extension = $this->normalizeExtension($extension);

        if ($this->preserveOriginalName && $originalName) {
            return $this->sanitizeFilename($originalName, $extension);
        }

        return match ($this->storedNameStrategy) {
            'uuid' => $this->uuid() . '.' . $extension,
            'random' => bin2hex(random_bytes(16)) . '.' . $extension,
            default => throw new RuntimeException("Unsupported Yarn stored name strategy: {$this->storedNameStrategy}"),
        };
    }

    protected function normalizeExtension(string $extension): string
    {
        $extension = strtolower(trim($extension));
        $extension = ltrim($extension, '.');

        if ($extension === '') {
            throw new RuntimeException('File extension cannot be empty.');
        }

        if (!preg_match('/^[a-z0-9]+$/', $extension)) {
            throw new RuntimeException("Invalid file extension: {$extension}");
        }

        return $extension;
    }

    protected function sanitizeFilename(string $filename, string $fallbackExtension): string
    {
        $filename = basename($filename);

        $name = pathinfo($filename, PATHINFO_FILENAME);
        $extension = pathinfo($filename, PATHINFO_EXTENSION);

        $name = strtolower($name);
        $name = preg_replace('/[^a-z0-9_-]+/i', '-', $name);
        $name = trim($name, '-_');

        if ($name === '') {
            $name = $this->uuid();
        }

        $extension = $extension ? $this->normalizeExtension($extension) : $fallbackExtension;

        return $name . '-' . bin2hex(random_bytes(4)) . '.' . $extension;
    }

    protected function makeRelativePath(string $absolutePath): string
    {
        $this->assertPathIsInsideStorage($absolutePath);

        $relativePath = substr($absolutePath, strlen($this->storagePath));
        $relativePath = trim($relativePath, DIRECTORY_SEPARATOR);

        return str_replace(DIRECTORY_SEPARATOR, '/', $relativePath);
    }

    protected function assertPathIsInsideStorage(string $path): void
    {
        $storageRoot = realpath($this->storagePath);

        if ($storageRoot === false) {
            return;
        }

        $directory = is_dir($path) ? $path : dirname($path);
        $resolvedDirectory = realpath($directory);

        if ($resolvedDirectory === false) {
            return;
        }

        if (!str_starts_with($resolvedDirectory, $storageRoot)) {
            throw new RuntimeException('Resolved path is outside the configured storage directory.');
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
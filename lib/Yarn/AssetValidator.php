<?php

namespace Loom73\Yarn;

class AssetValidator
{
    protected int $maxUploadSize;

    protected array $allowedMimes;

    public function __construct(array $config)
    {
        $this->maxUploadSize = (int) ($config['max_upload_size'] ?? 10 * 1024 * 1024);
        $this->allowedMimes = $config['allowed_mimes'] ?? [];
    }

    /**
     * Validate one uploaded file from the $_FILES array.
     *
     * Expected shape:
     * [
     *   'name' => 'document.pdf',
     *   'type' => 'application/pdf',
     *   'tmp_name' => '/tmp/php123',
     *   'error' => UPLOAD_ERR_OK,
     *   'size' => 12345,
     * ]
     */
    public function validateUploadedFile(array $file): AssetValidationResult
    {
        $errors = [];

        $originalName = $file['name'] ?? null;
        $tmpPath = $file['tmp_name'] ?? null;
        $uploadError = $file['error'] ?? UPLOAD_ERR_NO_FILE;
        $reportedSize = isset($file['size']) ? (int) $file['size'] : null;

        if ($uploadError !== UPLOAD_ERR_OK) {
            $errors[] = $this->uploadErrorMessage($uploadError);

            return new AssetValidationResult(
                valid: false,
                errors: $errors,
                originalName: $originalName,
                sizeBytes: $reportedSize,
            );
        }

        if (!$originalName || !is_string($originalName)) {
            $errors[] = 'Missing original filename.';
        }

        if (!$tmpPath || !is_string($tmpPath)) {
            $errors[] = 'Missing temporary upload path.';
        }

        if ($tmpPath && !is_uploaded_file($tmpPath)) {
            $errors[] = 'The file was not uploaded through HTTP POST.';
        }

        if ($tmpPath && !is_file($tmpPath)) {
            $errors[] = 'Uploaded temporary file not found.';
        }

        $actualSize = null;

        if ($tmpPath && is_file($tmpPath)) {
            $actualSize = filesize($tmpPath);

            if ($actualSize === false) {
                $errors[] = 'Unable to determine uploaded file size.';
                $actualSize = null;
            }
        }

        $sizeBytes = $actualSize ?? $reportedSize;

        if ($sizeBytes !== null && $sizeBytes > $this->maxUploadSize) {
            $errors[] = 'Uploaded file exceeds the maximum allowed size.';
        }

        if ($sizeBytes !== null && $sizeBytes <= 0) {
            $errors[] = 'Uploaded file is empty.';
        }

        $extension = $originalName
            ? $this->extensionFromFilename($originalName)
            : null;

        if (!$extension) {
            $errors[] = 'Uploaded file has no valid extension.';
        }

        $mimeType = null;

        if ($tmpPath && is_file($tmpPath)) {
            $mimeType = $this->detectMimeType($tmpPath);

            if (!$mimeType) {
                $errors[] = 'Unable to detect uploaded file MIME type.';
            }
        }

        if ($mimeType && !$this->isAllowedMime($mimeType)) {
            $errors[] = "File type is not allowed: {$mimeType}.";
        }

        if ($mimeType && $extension && !$this->isAllowedExtensionForMime($extension, $mimeType)) {
            $errors[] = "File extension .{$extension} is not allowed for MIME type {$mimeType}.";
        }

        return new AssetValidationResult(
            valid: count($errors) === 0,
            errors: $errors,
            originalName: $originalName,
            extension: $extension,
            mimeType: $mimeType,
            sizeBytes: $sizeBytes,
        );
    }

    public function validateLocalFile(string $path, ?string $originalName = null): AssetValidationResult
    {
        $errors = [];

        if (!is_file($path)) {
            return new AssetValidationResult(
                valid: false,
                errors: ['Local file not found.'],
                originalName: $originalName,
            );
        }

        $sizeBytes = filesize($path);

        if ($sizeBytes === false) {
            $errors[] = 'Unable to determine file size.';
            $sizeBytes = null;
        }

        if ($sizeBytes !== null && $sizeBytes > $this->maxUploadSize) {
            $errors[] = 'File exceeds the maximum allowed size.';
        }

        if ($sizeBytes !== null && $sizeBytes <= 0) {
            $errors[] = 'File is empty.';
        }

        $nameForExtension = $originalName ?? basename($path);
        $extension = $this->extensionFromFilename($nameForExtension);

        if (!$extension) {
            $errors[] = 'File has no valid extension.';
        }

        $mimeType = $this->detectMimeType($path);

        if (!$mimeType) {
            $errors[] = 'Unable to detect file MIME type.';
        }

        if ($mimeType && !$this->isAllowedMime($mimeType)) {
            $errors[] = "File type is not allowed: {$mimeType}.";
        }

        if ($mimeType && $extension && !$this->isAllowedExtensionForMime($extension, $mimeType)) {
            $errors[] = "File extension .{$extension} is not allowed for MIME type {$mimeType}.";
        }

        return new AssetValidationResult(
            valid: count($errors) === 0,
            errors: $errors,
            originalName: $originalName ?? basename($path),
            extension: $extension,
            mimeType: $mimeType,
            sizeBytes: $sizeBytes,
        );
    }

    protected function detectMimeType(string $path): ?string
    {
        if (!function_exists('finfo_open')) {
            return null;
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);

        if (!$finfo) {
            return null;
        }

        $mimeType = finfo_file($finfo, $path);

        finfo_close($finfo);

        return is_string($mimeType) ? $mimeType : null;
    }

    protected function extensionFromFilename(string $filename): ?string
    {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $extension = ltrim($extension, '.');

        if ($extension === '') {
            return null;
        }

        if (!preg_match('/^[a-z0-9]+$/', $extension)) {
            return null;
        }

        return $extension;
    }

    protected function isAllowedMime(string $mimeType): bool
    {
        return array_key_exists($mimeType, $this->allowedMimes);
    }

    protected function isAllowedExtensionForMime(string $extension, string $mimeType): bool
    {
        if (!$this->isAllowedMime($mimeType)) {
            return false;
        }

        return in_array(
            strtolower($extension),
            array_map('strtolower', $this->allowedMimes[$mimeType]),
            true
        );
    }

    protected function uploadErrorMessage(int $error): string
    {
        return match ($error) {
            UPLOAD_ERR_INI_SIZE => 'Uploaded file exceeds the server upload_max_filesize limit.',
            UPLOAD_ERR_FORM_SIZE => 'Uploaded file exceeds the form MAX_FILE_SIZE limit.',
            UPLOAD_ERR_PARTIAL => 'Uploaded file was only partially uploaded.',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary upload directory.',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write uploaded file to disk.',
            UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload.',
            default => 'Unknown upload error.',
        };
    }
}
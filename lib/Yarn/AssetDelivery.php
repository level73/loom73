<?php

namespace Loom73\Yarn;

use Loom73\Woodframe\Config;

class AssetDelivery
{
    protected array $config;

    public function __construct(?array $config = null)
    {
        $this->config = $config ?? Config::get('yarn');
    }

    public function inline(object $asset): never
    {
        $this->deliver($asset, 'inline');
    }

    public function download(object $asset): never
    {
        $this->deliver($asset, 'attachment');
    }

    protected function deliver(object $asset, string $disposition): never
    {
        $path = $this->absolutePath($asset);

        if (!is_file($path)) {
            http_response_code(404);
            exit;
        }

        if (ob_get_length()) {
            ob_clean();
        }

        header('Content-Type: ' . $asset->mime_type);
        header('Content-Length: ' . filesize($path));
        header(
            'Content-Disposition: '
            . $disposition
            . '; filename="'
            . basename($asset->original_name)
            . '"'
        );
        header('X-Content-Type-Options: nosniff');

        readfile($path);
        exit;
    }

    protected function absolutePath(object $asset): string
    {
        $basePath = rtrim(
            $this->config['storage_path'],
            DIRECTORY_SEPARATOR
        );

        return $basePath . DIRECTORY_SEPARATOR . $asset->disk_path;
    }
}
<?php

namespace Loom73\Weave\Controllers;

use Loom73\Woodframe\Config;
use Loom73\Woodframe\Ctrl;
use Loom73\Yarn\Asset;
use Loom73\Yarn\AssetDelivery;

class AssetCtrl extends Ctrl
{
    protected Asset $Asset;

    public function __construct(
        string $model,
        string $controller,
        string $method
    ) {
        parent::__construct($model, $controller, $method);

        $this->Asset = new Asset();
    }

    public function view(string $uuid): void
    {
        $this->disableRender();

        $asset = $this->Asset->getByUuid($uuid);

        if ($asset->fails() || $asset->isEmpty()) :
            http_response_code(404);
            exit;
        endif;

        $file = $asset->first();

        if ((int) ($file->status ?? STATUS_INACTIVE) !== STATUS_ACTIVE) :
            http_response_code(404);
            exit;
        endif;

        if (!$this->canBeDeliveredPublicly($file)):
            $this->requireAuth();
        endif;

        $Delivery = new AssetDelivery();

        $Delivery->inline($file);
    }

    public function download(string $uuid): void
    {
        $this->disableRender();
        $asset = $this->Asset->getByUuid($uuid);

        if ($asset->fails() || $asset->isEmpty()) :
            http_response_code(404);
            exit;
        endif;

        $file = $asset->first();

        if ((int) ($file->status ?? STATUS_INACTIVE) !== STATUS_ACTIVE) :
            http_response_code(404);
            exit;
        endif;

        if (!$this->canBeDeliveredPublicly($file)):
            $this->requireAuth();
        endif;

        $Delivery = new AssetDelivery();

        $Delivery->download($file);
    }


    protected function canBeDeliveredPublicly(object $asset): bool {
        $publicDeliveryEnabled = (bool) Config::get(
            'yarn.public_delivery_enabled',
            false
        );

        return $publicDeliveryEnabled
            && ($asset->visibility ?? null)
            === Asset::VISIBILITY_PUBLIC;
    }
}
<?php

namespace Loom73\Weave\Controllers;

use Loom73\Gauge\OptIn;
use Loom73\Woodframe\Ctrl;

final class GaugeCtrl extends Ctrl
{
    public function opt_in(): void
    {
        if (!$this->validPost()):
            return;
        endif;

        if (
            !$this->isAuthenticated
            || $this->Auth?->can($this->user, 'view_gauge') !== true
        ):
            http_response_code(403);
            $this->disableRender();
            return;
        endif;

        if (!OptIn::optIn()):
            http_response_code(409);
            $this->disableRender();
            return;
        endif;

        $this->redirect('/');
    }

    public function opt_out(): void
    {
        if (!$this->validPost()):
            return;
        endif;

        OptIn::optOut();
        $this->redirect('/');
    }

    private function validPost(): bool
    {
        if ($this->requestMethod() !== 'POST'):
            header('Allow: POST');
            http_response_code(405);
            $this->disableRender();
            return false;
        endif;

        if (!$this->validateToken()):
            http_response_code(403);
            $this->disableRender();
            return false;
        endif;

        return true;
    }
}
<?php

namespace Loom73\Weave\Controllers;

use Loom73\Woodframe\Ctrl;

class ComponentsCtrl extends Ctrl
{
    public function index(): void
    {
        $this->set('title', "Components");
        $this->set('meta_description', "A listing of the Loom73 components that handle the recurring parts of application development.");

    }

    public function stitch_icons(): void
    {
        $this->set('title', "Stitch Icons");
        $this->set('meta_description', "Stitch - the Loom73 icons family");

    }

    public function tables(): void
    {
        $this->set('title', "Tables");
        $this->set('meta_description', "An example of how the Loom73 Data Tables component works.");

    }
}
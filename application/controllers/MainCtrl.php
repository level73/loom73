<?php

namespace Loom73\Weave\Controllers;
use Loom73\Woodframe\Ctrl;
class MainCtrl extends Ctrl
{

    public function __construct($model, $controller, $method)
    {
        parent::__construct($model, $controller, $method);
    }

    public function index(): void
    {
       $this->set('title', "An evolving application blueprint");
       $this->set('meta_description', "Loom73 is Level73's blueprint to jumpstart projects. Minimal dependencies, modern object-oriented PHP MVC, pure css and vanilla JS.");
    }

    public function architecture(): void
    {
        $this->set('title', "Architecture");
        $this->set('meta_description', "A closer look at the architectural conventions of the Loom73 application blueprint.");
    }

    public function technical_requirements(): void
    {
        $this->set('title', "Requirements");
        $this->set('meta_description', "Requirements to run Loom73 on your VPS.");
    }

}
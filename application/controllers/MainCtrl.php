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
       $this->set('meta_description', "Loom73 is Level73's microframework to jumpstart projects. Minimal dependencies, modern object-oriented PHP MVC, pure css and vanilla JS.");
    }

}
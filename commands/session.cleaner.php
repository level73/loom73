<?php
namespace Loom73\Shuttle;

class SessionCleaner
{
    public function __construct()
    {
        session_name($_SERVER['APPNAME']);
        session_start();
        print_r($_SESSION);
        session_gc();
        session_destroy();
        echo 'Session destroyed.' . PHP_EOL;
        print_r( $_SESSION );
    }
}
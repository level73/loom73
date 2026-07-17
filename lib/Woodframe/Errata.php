<?php

namespace Loom73\Woodframe;

class Errata extends \Exception
{
    private $codes = array(
        100 => 'success',
        200 => 'info',
        400 => 'warning',
        500 => 'danger'
    );

    /** Returns an html element treated as an alert with information on the error.
     * @return string
     */
    public function errorMessage(): string {
        //error message
        $code = $this->getCode();
        if($_SERVER['DEBUG']) {
            $errorMsg = '<span class="debug-code">Error on line ' . $this->getLine() . ' in ' . $this->getFile() . '</span><br />';
            $errorMsg .= $this->getMessage();
        }
        else {
            $errorMsg = $this->getMessage();
        }
        return '<div class="alert alert-' . $this->codes[$code] . ' alert-dismissible fade show" role="alert">' . $errorMsg . '</div>';
    }
}
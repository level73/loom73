<?php

namespace Loom73\Woodframe;
use Loom73\Woodframe\Errata;

class Template
{
    protected array $variables = array();
    protected $_controller;
    protected $_method;

    /** array to exclude certain routes (like csv exports if there are any. Just add to the list) **/
    private array $exclude = array(
        'export'
    );

    public function __construct($controller, $method)
    {
        $this->_controller = $controller;
        $this->_method = $method;
    }

    public function set($name, $value)
    {
        $this->variables[$name] = $value;
    }

    /** Display Template **/
    public function render(): void
    {
        extract($this->variables);

        if(!isset($title)):
            $title = $_SERVER['APPNAME'];
        endif;


        /** Check existence of the View file */
        $viewsDirectory = ROOT_DIR
            . DIRECTORY_SEPARATOR
            . 'application'
            . DIRECTORY_SEPARATOR
            . 'views';

        $dirname = strtolower($this->_controller);

        $viewPath = $viewsDirectory
            . DIRECTORY_SEPARATOR
            . $dirname
            . DIRECTORY_SEPARATOR
            . $this->_method
            . '.php';

        if (!file_exists($viewPath)):
            http_response_code(500);

            $missingView = $viewPath;

            $title = 'Something went wrong';
            $meta_description = 'The requested page cannot be displayed.';
            $bodyClass = 'error error-500';

            $dirname = 'errors';

            $viewPath = $viewsDirectory
                . DIRECTORY_SEPARATOR
                . 'errors'
                . DIRECTORY_SEPARATOR
                . '500.php';
        endif;

        // Check for excluded routes
        if( !in_array(  $this->_method, $this->exclude  ) &&
            $this->_controller != 'ajax'  &&
            $this->_controller != 'api' &&
            $this->_method != 'download' &&
            $this->_method != 'access' &&
            $this->_method != 'save'
        ):
            /* Include Base Head @ view/head.php */
            include (ROOT_DIR . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'head.php');

            /** Include Header **/
            if (file_exists(ROOT_DIR . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . $dirname . DIRECTORY_SEPARATOR . 'header.php')) {
                include (ROOT_DIR . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . $dirname . DIRECTORY_SEPARATOR . 'header.php');
            } else {
                include (ROOT_DIR . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'header.php');
            }
            /** check for sub-menus **/
            if( file_exists(ROOT_DIR . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . $dirname . DIRECTORY_SEPARATOR . 'sub-menu.php' )){
                include(ROOT_DIR . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . $dirname . DIRECTORY_SEPARATOR . 'sub-menu.php' );
            }

            /** If we have any errors, this is where we print them without breaking the whole thing ***/
            if( isset($errors) && $errors instanceof Errata ):
                include(ROOT_DIR . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . '_partials' . DIRECTORY_SEPARATOR . 'errors.php');
            endif;

            /** Include desired center template **/
            include $viewPath;

            /** Include footer **/
            if (file_exists(ROOT_DIR . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . $dirname . DIRECTORY_SEPARATOR . 'footer.php')):
                include (ROOT_DIR . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . $dirname . DIRECTORY_SEPARATOR . 'footer.php');
            else:
                include (ROOT_DIR . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'footer.php');
            endif;

            /* Include Base Foot @ view/foot.php */
            include (ROOT_DIR . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'foot.php');

        else:
            /** requested file should not include header and footer, might be different mime or AJAX content **/
            if(file_exists(ROOT_DIR . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . $dirname . DIRECTORY_SEPARATOR . $this->_method . '.php')):
                include (ROOT_DIR . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . $dirname . DIRECTORY_SEPARATOR . $this->_method . '.php');
            endif;
        endif;
    }
}
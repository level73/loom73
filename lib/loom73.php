<?php
use Loom73\Woodframe\Errata;
use Loom73\Woodframe\Debugger;
use Loom73\Woodframe\Config;
use Loom73\Woodframe\Template;

/** Basic Error Reporting Config */
function errorReporting(): void
{
    if (($_SERVER['SYSTEM_STATUS'] ?? 'production') === 'development'):
        ini_set('display_errors', 1);
        ini_set('error_reporting', E_ALL);
    else:
        ini_set('display_errors', 0);
        ini_set('error_reporting', 0);
    endif;
}

function namespaceLoader(string $className): bool
{
    $prefix = 'Loom73\\';

    if (!str_starts_with($className, $prefix)):
        return false;
    endif;

    $relativeClass = substr($className, strlen($prefix));

    $path = ROOT_DIR
        . DIRECTORY_SEPARATOR
        . 'lib'
        . DIRECTORY_SEPARATOR
        . str_replace('\\', DIRECTORY_SEPARATOR, $relativeClass)
        . '.php';

    if (!file_exists($path)) :
        return false;
    endif;

    require_once $path;

    return true;
}

/** Directory Looper
 *  Looks for class files in the Loom73 directory structure,
 *  based on the LOOM73['directories'] constant.
 */
function directoryLooper(string $className): bool
{
    foreach (LOOM73['directories'] as $directory => $suffix) {
        $path = ROOT_DIR
            . DIRECTORY_SEPARATOR
            . $directory
            . DIRECTORY_SEPARATOR
            . $className
            . $suffix
            . '.php';

        if (!file_exists($path)) {
            continue;
        }

        require_once $path;

        return true;
    }

    return false;
}

/** Loom73 Autoloader */
function Loom73_autoloader(string $className): void
{

    if (namespaceLoader($className)) {
        return;
    }

    $shortClassName = explode("\\", $className);
    $shortClassName = array_pop($shortClassName);

    if (directoryLooper($shortClassName)) {
        return;
    }
    /*
     * Class not found.
     *
     * The autoloader must fail silently.
     * The caller or PHP itself will determine whether
     * the missing class is actually an error.
     */

    return;
}

spl_autoload_register('Loom73_autoloader');

/** Render Error Pages */
function renderNotFound(): void
{
    http_response_code(404);
    $Template = new Template( 'errors', '404');
    $Template->set('title', 'Page not found');
    $Template->set('meta_description', 'The requested page could not be found.');
    $Template->set('bodyClass', 'error error-404');
    $Template->render();
}

/** Start the Application */
function Loom73(): void {
    // Set error reporting levels
    errorReporting();

    // Load module configs
    Config::load(ROOT_DIR . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'modules');



    // Capture URL
    global $url;

    // Set fragments to exclude
    $fragments_to_exclude = [ 'js', 'css', 'resources', 'assets'];

    /** Explode the URL to dynamically build the Route
     *  [0] => Controller
     *  [1] => Method
     *  [2...] => Params to send to the Method
     * */
    $url_fragments = explode('/', $url);

    // the Controller is the first element of our Route
    $route = $url_fragments[0];
    array_shift($url_fragments);

    // Check for a method and (eventually) a querystring
    if (!empty($url_fragments)) :
        $method = toMachine($url_fragments[0], 'method');
        array_shift($url_fragments);
        $queryString = $url_fragments;
    else :
        // Set default method
        $method = $_SERVER['DEFAULT_METHOD'];
        $queryString = array();
    endif;

    // Empty controller? go to default Route
    if (empty($route)) :
        $route  = $_SERVER['DEFAULT_CONTROLLER'];
        $method = $_SERVER['DEFAULT_METHOD'];
    endif;

    // Parse the route to set up the Controller and Model name to load
    $baseClassName = toMachine($route);
    $controller = 'Loom73\Weave\Controllers\\' . $baseClassName . 'Ctrl';
    $model = 'Loom73\Weave\Models\\' . $baseClassName;

    // avoid dispatching for JavaScript, CSS and Resources
    if(!in_array($route, $fragments_to_exclude)):
        // Create Instance
        // refer to lib/Ctrl.class.php to see how the controller handles the business logic
        // Check if Controller and Method exist, otherwise render 404 page
        if (
            !class_exists($controller) ||
            !method_exists($controller, $method)
        ):
            renderNotFound();

            return;
        endif;

        $dispatch = new $controller(
            $model,
            $route,
            $method
        );

        call_user_func_array(
            [$dispatch, $method],
            $queryString
        );
        /*if(class_exists($controller)):
            try
            {
                $dispatch = new $controller($model, $route, $method);
                if (method_exists($controller, $method)):
                    // Execute method
                    call_user_func_array(array($dispatch, $method), $queryString);
                else:
                    throw new Errata('This page does not exist.', 500);
                endif;
            }
            catch (Errata $e) {
                $e->errorMessage();
            }
        endif;*/
    endif;
}
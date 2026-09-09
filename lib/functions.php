<?php
/** Parse a string, convert to convenient method/class name
 * @param string $string the url fragment to convert
 * @param string $type default is class, and in that case it will uppercase the first letter. In all other cases it will swap dashes with underscores.
 *
 * */
function toMachine($string, $type = 'class'): string
{
    if($type == 'class'):
        return  implode(
            array_map('ucfirst',
                explode("-", $string)
            )
        );
    else:
        return  str_replace("-", "_", $string);
    endif;
}

/** Pretty Print for frontend debbuging.
 * @param array $array an array with the data to look into
 * */
function prettyPrint($array): void
{
    $backtrace = debug_backtrace();
    echo "<details class='pretty-print'>";
    echo "<summary>Debug function called from <span style='color: #ff342f;'>" . $backtrace[0]['file'] . "</span> at line " . $backtrace[0]['line'] . "</summary>";
    echo '<pre>';
    print_r($array);
    echo '</pre>';
    echo "</details>";
}
/** Pretty Print SQL strings - for debugging only
 *  @param string $sql the sql string you want to see compiled on screen
 */
function prettySQL($sql): void
{
    $trace = debug_backtrace();
    echo '<div class="debugger sql">
            <span>' . $sql . '</span><hr />';
    echo '<pre>' . $trace [0]['file'] . ':' . $trace [0]['line'] . '</pre>';
    echo '</div>';
}
/** Pretty Print Strings - for debugging only
 *  @param string $string the string you want to see compiled on screen
 */
function prettyString($string): void
{
    $trace = debug_backtrace();
    echo '<div class="debugger string">
            <span>' . $string . '</span><hr />';
    echo '<pre>' . $trace [0]['file'] . ':' . $trace [0]['line'] . '</pre>';
    echo '</div>';
}


/** Include partial views and components */
function partial($name): void
{
    if(file_exists(ROOT_DIR . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . '_partials' . DIRECTORY_SEPARATOR . $name . '.php')):
        include_once (ROOT_DIR . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . '_partials' . DIRECTORY_SEPARATOR . $name . '.php');
    endif;
}

/** this is a repeateble HTML component,  such as a badge or a specific button.
 * @param string $name The name of the component. it must match the file name in /application/views/components
 * @param string|null $label text to print within the component
 * @param array|null $options an array of key/value pairs that can be used in component. this can vary however you wish
 */
function component(string $name, ?string $label, ?array $options): void
{
    $set_options = $options;
    $set_label = $label;
    if(file_exists(ROOT_DIR . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . '_components' . DIRECTORY_SEPARATOR . $name . '.php')):
        include (ROOT_DIR . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . '_components' . DIRECTORY_SEPARATOR . $name . '.php');
    endif;
}

/** CSRF Protection Stuff */
function set_CSRF_Token(): string
{
    if(empty($_SESSION[$_SERVER['APPNAME']]['xss'])):
        $token = bin2hex(random_bytes(32));
        $_SESSION[$_SERVER['APPNAME']]['xss'] = $token;
    else:
        $token = $_SESSION[$_SERVER['APPNAME']]['xss'];
    endif;
    return $token;
}
/** Prints hidden input field with hash token for csrf check, or prints the token
 * @param bool $token_only set to true if you only want to the token
 */
function csrf( $token_only = false): bool
{
    if($token_only):
        echo set_CSRF_Token();
        return true;
    endif;
    echo '<input type="hidden" name="csrf" value="'.set_CSRF_Token().'">';
    return true;
}
/** Print Hidden ID field  **/
function idfield($id): void {
    echo '<input type="hidden" name="id" value="'.$id.'" readonly>';

}

/** Frontend Helpers */
/** Add an Icon in the Flash Messages
 * @param string $type can be success, danger, warning, info
 * @param string $size null or a Stitch icon size multiplier
 * */
function flashIcon(
    $type,
    $size = null
): void
{
    switch ($type):
        case 'success':
            echo '<i class="stitch stitch--check ' . (!is_null($size) ? $size : ''). '"></i>';
            break;
        case 'danger':
            echo '<i class="stitch stitch--error ' . (!is_null($size) ? $size : ''). '"></i>';
            break;
        case 'warning':
            echo '<i class="stitch stitch--danger ' . (!is_null($size) ? $size : ''). '"></i>';
            break;
        case 'info':
            echo '<i class="stitch stitch--info ' . (!is_null($size) ? $size : ''). '"></i>';
            break;
        default:
            break;
    endswitch;
}
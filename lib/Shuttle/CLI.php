<?php
namespace Loom73\Shuttle;

/**
 * Class that does things in the CLI
 * thanks to Miguel.V on stackoverflow: https://stackoverflow.com/a/69368922
 */
class CLI
{
    /** Colorize the CLI
     * @var $content string the message to colorize
     * @var $color string|int  nullable color, either as a string or as an integer
     * @return string
     */
    public function cout_color(string $content, ?string $color=null)
    {

        // if a color is set use the color set.
        if(!empty($color)):
            // if our color string is not a numeric value
            if(!is_numeric($color)):
                //lowercase our string value.
                $c = strtolower($color);
            else:
                // check if our color value is not empty.
                if(!empty($color)):
                    $c = $color;
                else:
                    // no color was set so lets pick a random one...
                    $c = rand(1,14);
                endif;
            endif;
        else:    // no color argument was passed, so lets pick a random one w00t
            $c = rand(1,14);
        endif;

        $cheader = '';
        $cfooter = "\033[0m";

        // let check which color code was used so we can then wrap our content.
        switch($c):
            case 1:
            case 'red':
                // color code header.
                $cheader .= "\033[31m";
                break;
            case 2:
            case 'green':
                // color code header.
                $cheader .= "\033[32m";
                break;
            case 3:
            case 'yellow':
                // color code header.
                $cheader .= "\033[33m";
                break;
            case 4:
            case 'blue':
                // color code header.
                $cheader .= "\033[34m";
                break;
            case 5:
            case 'magenta':
                // color code header.
                $cheader .= "\033[35m";
                break;
            case 6:
            case 'cyan':
                // color code header.
                $cheader .= "\033[36m";
                break;
            case 7:
            case 'light grey':
                // color code header.
                $cheader .= "\033[37m";
                break;
            case 8:
            case 'dark grey':
                // color code header.
                $cheader .= "\033[90m";
                break;
            case 9:
            case 'light red':
                // color code header.
                $cheader .= "\033[91m";
                break;
            case 10:
            case 'light green':
                // color code header.
                $cheader .= "\033[92m";
                break;
            case 11:
            case 'light yellow':
                // color code header.
                $cheader .= "\033[93m";
                break;
            case 12:
            case 'light blue':
                // color code header.
                $cheader .= "\033[94m";
                break;
            case 13:
            case 'light magenta':
                // color code header.
                $cheader .= "\033[95m";
                break;
            case 14:
            case 'light cyan':
                // color code header.
                $cheader .= "\033[92m";
                break;
        endswitch;

        // wrap our content.
        $content = $cheader.$content.$cfooter;

        //return our new content.
        return $content;
    }

    /** Print a confirmation message to the CLI and evaluate the answer
     *  It performs an output buffer cleanup if return is false
     * @var $message ?string the question to ask
     * @return bool
     */
    public function confirm(?string $message = null): bool
    {
        do {
            echo ltrim($message . " (y/n): ");
            $response = strtolower(trim(fgets(STDIN)));
        } while (null === $result = match ($response) {
            'y', 'yes' => true,
            'n', 'no' => false,
            default => null,
        });
        if(!$result):
            if (ob_get_level() > 0) {
                ob_flush();
            }
        endif;

        return $result;
    }

    public function ask(?string $question = null): string
    {
        return readline($question);
    }
}
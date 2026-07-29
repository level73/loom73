<?php

namespace Loom73\Woodframe;

class Debugger
{
    /** Triggers the debugger
     * @param $error array The output of debug_backtrace
     * */
    public static function dbg(array $error): void{
        if(!empty($error)):
            echo '<div class="debugger">';
            foreach($error as $e):
                $file = $e['file'] ?? '';
                $line = $e['line'] ?? '';

                echo '<details>';
                    echo '<summary>' . $file . ': line ' . $line . ' (function: ' . $e['function'] . ')</summary>';
                    echo '<pre>';
                        print_r($e);
                    echo '</pre>';
                echo '</details>';
            endforeach;
            echo '</div>';
        endif;
    }
}
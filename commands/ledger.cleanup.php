<?php
/**
 *  This command cleans up the ledger based on teh retention period that is set in
 *  /config/modules/ledger.php
 *  it can be executed as a Cron job, but that will need to be installed manually
 *  by the sysadmin.
 */
namespace Loom73\Shuttle;

use Loom73\Ledger\LedgerEvent;
use Loom73\Woodframe\Config;
use Loom73\Woodframe\Logger;

class LedgerCleanup
{

    protected string $table = 'ledger_events';
    protected bool $dates = false;
    public function __construct()
    {

        $CLI = new CLI();

        $retention = Config::get('ledger.retention');

        $force = in_array('--force', $_SERVER['argv'], true);
        $purge = in_array('--purge', $_SERVER['argv'], true);

        $interactive = function_exists('stream_isatty')
            && stream_isatty(STDIN);


        /*
         * Purge all ledger records, after confirmation
         * This should only be used in the CLI, not in CRON
         */
        if ($purge):

            if (!$force && !$interactive):
                $this->logError($purge, $retention);
                exit(1);
            endif;

            if (!$force):
                $confirm_purge = $CLI->confirm(
                    'Are you sure you want to permanently delete all Ledger events?'
                );

                if (!$confirm_purge):
                    echo "Purge cancelled." . PHP_EOL;
                    return;
                endif;
            endif;

        endif;

        /*
         * Retention disabled.
         */
        if ((int) $retention === 0):
            echo "Ledger retention is disabled. No cleanup required." . PHP_EOL;
            return;
        endif;

        /*
         * Non-interactive execution requires explicit --force.
         */
        if (!$force && !$interactive):
            $this->logError($purge, $retention);
            exit(1);

        endif;


        /*
         * Interactive execution requires confirmation.
         */
        if (!$force && !$purge):
            echo 'The retention period set for Ledger is '
                . $CLI->cout_color((string) $retention, 'yellow')
                . ' days.'
                . PHP_EOL;

            $confirm = $CLI->confirm(
                'Do you want to delete all Ledger records older than '
                . $retention
                . ' days?'
            );

            if (!$confirm):
                echo "Cleanup cancelled." . PHP_EOL;
                if (ob_get_level() > 0) : ob_flush(); endif;
                return;
            endif;
        endif;

        /*
         * Cleanup runs both interactively and with --force.
         */
        $Model = new LedgerEvent();
        if($purge):
            $QueryResult = $Model->purge();
        else:
            $QueryResult = $Model->deleteByRetentionPolicy();
        endif;

        if ($QueryResult->fails()):
            echo "Something went wrong while cleaning up Ledger." . PHP_EOL;
            return;
        endif;

        echo "Ledger cleaned up." . PHP_EOL;
    }


    protected function logError($purge, $retention): void
    {
        Logger::error(
            'Shuttle',
            'Ledger cleanup aborted: non-interactive execution requires --force',
            [
                'command' => 'ledger.cleanup',
                'purge' => $purge,
                'retention' => $retention,
            ]
        );
    }

}
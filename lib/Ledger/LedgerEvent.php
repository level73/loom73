<?php

namespace Loom73\Ledger;

use Loom73\Beam\Model;
use Loom73\Beam\QueryResult;
use Loom73\Woodframe\Config;

class LedgerEvent extends Model
{
    protected string $table = 'ledger_events';
    protected string $pkey = "idledger_event";
    protected bool $dates = false;

    public  function deleteByRetentionPolicy(): QueryResult
    {
        $retention = Config::get('ledger.retention');
        $SQL = 'DELETE FROM ' . $this->table . ' WHERE created_at < DATE_SUB( NOW(), INTERVAL ' . $retention . ' DAY)';
        return $this->query($SQL);
    }

    public function purge(): QueryResult
    {
        $sql = 'DELETE FROM ' .  $this->table;
        return $this->query($sql);
    }

}
<?php

namespace Loom73\Ledger;

use Loom73\Beam\Model;

class LedgerEvent extends Model
{
    protected string $table = 'ledger_events';
    protected string $pkey = "idledger_event";
    protected bool $dates = false;

}
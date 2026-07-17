<?php

namespace Loom73\Yarn;

use Loom73\Beam\Model;
use Loom73\Beam\QueryResult;
use PDO;

class AssetType extends Model
{
    protected string $table = 'asset_types';

    protected string $pkey = 'idasset_type';

    protected bool $dates = true;

    public function getBySlug(string $slug): QueryResult
    {
        return $this->getBy([
            'slug' => [
                'operator' => '=',
                'value' => $slug,
                'type' => PDO::PARAM_STR,
            ],
        ], limit: 1);
    }

    public function idFromSlug(string $slug): ?int
    {
        $result = $this->getBySlug($slug);

        if ($result->fails() || $result->isEmpty()) {
            return null;
        }

        return (int) $result->first()->idasset_type;
    }
}
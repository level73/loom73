<?php

namespace Loom73\Shuttle;



use Loom73\Shuttle\CLI;
use Loom73\Yarn\AssetType;
use PDO;


class Asset_typeNew
{
    protected string $table = 'asset_types';

    protected string $pkey = 'idasset_type';

    protected bool $dates = true;
    public function __construct()
    {
        $CLI = new CLI();

        $slug = $CLI->ask('Enter the slug: ');
        $label = $CLI->ask('Enter the label: ');
        $description = $CLI->ask('Enter the description: ');

        $Data = [
            'slug' =>   ['value' => $slug, 'type' => PDO::PARAM_STR],
            'label' =>      ['value' => $label, 'type' => PDO::PARAM_STR],
            'description' =>   ['value' => $description, 'type' => PDO::PARAM_STR],
        ];

        $AssetType = new AssetType();
        $Query = $AssetType->create($Data);
        if($Query->fails()):
            echo $CLI->cout_color( "Something went wrong. ", 'red') . PHP_EOL;
        else:
            echo  $CLI->cout_color( "New Asset Type with slug/label ", 'green');
            echo $CLI->cout_color( $slug . "/" . $label , 'cyan');
            echo  $CLI->cout_color( " created ", 'green') . PHP_EOL;
        endif;
    }

}
<?php
require_once('./vendor/autoload.php');

$monsterByDroppingItem = [];

$endpoints = [[
    'url' => '/monster',
    'postProcessing' => function (&$currentItem) use (&$monsterByDroppingItem) {
        $currentItem['icon'] = Flyffdatabase\ContentGeneration\ImageHelper::QueueDownloadImage(
            'https://api.flyff.com/image/monster/' . $currentItem['icon'], 
            '/icon/monster', 
            $currentItem['icon']
        );

        foreach($currentItem['drops'] as $currentItemDrop) {
            if (!isset($monsterByDroppingItem[$currentItemDrop['item']])) {
                $monsterByDroppingItem[$currentItemDrop['item']] = [];
            }

            array_push($monsterByDroppingItem[$currentItemDrop['item']], $currentItem['id']);
        }
    },
],[
    'url' => '/item',
    'postProcessing' => function (&$currentItem) use (&$monsterByDroppingItem) {
        $currentItem['flyffdb_dropped_by'] = [];

        $currentItem['icon'] = Flyffdatabase\ContentGeneration\ImageHelper::QueueDownloadImage(
            'https://api.flyff.com/image/item/' . $currentItem['icon'], 
            '/icon/item', 
            $currentItem['icon']
        );

        if (isset($monsterByDroppingItem[$currentItem['id']])) {
            $currentItem['flyffdb_dropped_by'] = $monsterByDroppingItem[$currentItem['id']];
        }
    },
],[
    'url' => '/world',
    'postProcessing' => function (&$currentWorld) {
        for($x = 0; $x < $currentWorld['width']; $x = $x +  $currentWorld['tileSize']) {
            for($y = 0; $y < $currentWorld['height']; $y = $y + $currentWorld['tileSize']) {
                $tileFileName = $currentWorld['tileName'] . ($x / $currentWorld['tileSize']) . '-' . ($y / $currentWorld['tileSize']) . '-0.png';
                Flyffdatabase\ContentGeneration\ImageHelper::QueueDownloadImage(
                    'https://api.flyff.com/image/world/' . $tileFileName, 
                    '/icon/world', 
                    $tileFileName
                );
            }
        }
    },
],[
    'url' => '/class',
    'postProcessing' => null,
],[
    'url' => '/equipset',
    'postProcessing' => null,
],[
    'url' => '/skill',
    'postProcessing' => null,
],[
    'url' => '/partyskill',
    'postProcessing' => null,
],[
    'url' => '/npc',
    'postProcessing' => function (&$currentItem) {
        $currentItem['image'] = Flyffdatabase\ContentGeneration\ImageHelper::QueueDownloadImage(
            'https://api.flyff.com/image/npc/' . $currentItem['image'], 
            '/icon/npc', 
            $currentItem['image']
        );
    },
],[
    'url' => '/quest',
    'postProcessing' => null,
],[
    'url' => '/achievement',
    'postProcessing' => null,
]];
$timeStart = microtime(true);
foreach($endpoints as $currentEndpoint) {
    echo "Downloading ".$currentEndpoint['url'].PHP_EOL;
    Flyffdatabase\ContentGeneration\APIHelper::BatchDownloadFromApi($currentEndpoint['url'], function ($currentItem) use ($currentEndpoint) {
        if (!is_dir('./content')) mkdir('./content');
        if (!is_dir('./content'. $currentEndpoint['url'] .'s')) mkdir('./content'. $currentEndpoint['url'] .'s');
        $currentItem['flyffdb_meta_id'] = substr($currentEndpoint['url'] . '_' . $currentItem['id'], 1);

        //make sure we dont use these fields for input data because they cant be indexed as in api
        $fulltextSearchFields = ['title', 'description', 'slug', 'text'];
        foreach($fulltextSearchFields as $currentFieldToAvoid) {
            if (isset($currentItem[$currentFieldToAvoid])) {
                $currentItem['raw_' . $currentFieldToAvoid] = $currentItem[$currentFieldToAvoid];
                unset($currentItem[$currentFieldToAvoid]);
            }
        }

        if (is_callable($currentEndpoint['postProcessing'])) {
            $currentEndpoint['postProcessing']($currentItem);
        }

        file_put_contents('./content'. $currentEndpoint['url'] .'s' . $currentEndpoint['url'] . '_' . $currentItem['id'] . '.json', json_encode($currentItem, JSON_PRETTY_PRINT));
    });
}
echo "Downloading Images..." . PHP_EOL;
Flyffdatabase\ContentGeneration\ImageHelper::ProcessDownloadImageQueue();
echo "Took: " . (microtime(true) - $timeStart)/60 . 'minutes' . PHP_EOL;

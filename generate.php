<?php
require_once('./vendor/autoload.php');
/*
$monsterByDroppingItem = [];

$endpoints = [[
    'url' => '/monster',
    'postProcessing' => function (&$currentItem) use (&$monsterByDroppingItem) {
        $currentItem['icon'] = Flyffdatabase\ContentGeneration\ImageHelper::QueueDownloadImage(
            'https://flyff-api.sniegu.fr/image/monster/' . $currentItem['icon'], 
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
            'https://flyff-api.sniegu.fr/image/item/' . $currentItem['icon'], 
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
                    'https://flyff-api.sniegu.fr/image/world/' . $tileFileName, 
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
            'https://flyff-api.sniegu.fr/image/npc/' . $currentItem['image'], 
            '/icon/npc', 
            $currentItem['image']
        );
    },
],[
    'url' => '/quest',
    'postProcessing' => null,
],[
    'url' => '/karma',
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

*/

// Go through all content directories that we just generated and bulk index them into zinc
/*
$contentBaseDir = './content';
if (is_dir($contentBaseDir)) {
    $dirListing = scandir($contentBaseDir);

    foreach($dirListing as $currentSubDirectory) {
        if ($currentSubDirectory == "." || $currentSubDirectory == "..") {
            continue;
        }

        //process subfolder
        //Delete index for $currentSubDirectory
        
        $subDirListing = scandir($contentBaseDir . "/" . $currentSubDirectory);
        
        $bulkBuffer = [];
        $bulkBufferCallback = function ($index, &$bulkBuffer) {
            if (count($bulkBuffer) == 0) return;

            // compile $bulkBuffer into zinc bulk payload
            // { "index" : { "_index" : "olympics" } } 
            $payloadBuffer = "";
            foreach($bulkBuffer as $currentBufferItem) {
                $payloadBuffer .= json_encode(["index" => ["_index" => $index]])."\n";
                $payloadBuffer .= json_encode($currentBufferItem)."\n";
            }

            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL,            "http://mvs-1.flyffdb.info:4080/api/_bulk" );
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 );
            curl_setopt($ch, CURLOPT_POST,           1 );
            curl_setopt($ch, CURLOPT_POSTFIELDS,     $payloadBuffer ); 
            curl_setopt($ch, CURLOPT_HTTPHEADER,     array('Content-Type: text/plain')); 
            curl_setopt($ch, CURLOPT_USERPWD, "admin:testpass"); 

            $result=curl_exec ($ch);

            $bulkBuffer = [];
        };
        $idx = 0;
        foreach($subDirListing as $currentObjectFileName) {
            if ($currentObjectFileName == "." || $currentObjectFileName == "..") {
                continue;
            }

            $objectDataRaw = file_get_contents($contentBaseDir . "/" . $currentSubDirectory . "/" . $currentObjectFileName);
            $objectData = json_decode($objectDataRaw, true);

            // Push 
            array_push($bulkBuffer, $objectData);

            if (count($bulkBuffer) == 25) {
                $bulkBufferCallback($currentSubDirectory, $bulkBuffer);
            }
            
            $idx++;
        }
        $bulkBufferCallback($currentSubDirectory, $bulkBuffer);
    }
}
//indexing end
*/

$query = "staff";

$payloadBuffer = [
    "search_type" => "fuzzy",
    "query" =>
    [
        "term" => $query
    ],
    "from" => 0, # use together with max_results for paginated results.
    "max_results" => 20
];
$ch = curl_init();

curl_setopt($ch, CURLOPT_URL,            "http://mvs-1.flyffdb.info:4080/api/items/_search" );
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 );
curl_setopt($ch, CURLOPT_POST,           1 );
curl_setopt($ch, CURLOPT_POSTFIELDS,     json_encode($payloadBuffer) ); 
curl_setopt($ch, CURLOPT_HTTPHEADER,     array('Content-Type: text/plain')); 
curl_setopt($ch, CURLOPT_USERPWD, "admin:testpass"); 

$result=curl_exec ($ch);
echo json_encode(json_decode($result), \JSON_PRETTY_PRINT);

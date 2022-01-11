<?php
require_once('./vendor/autoload.php');

$contentBaseDir = './content';
if (is_dir($contentBaseDir)) {
    $dirListing = scandir($contentBaseDir);

    foreach($dirListing as $currentSubDirectory) {
        if ($currentSubDirectory == "." || $currentSubDirectory == "..") {
            continue;
        }

        //process subfolder
        //TODO: Delete index for $currentSubDirectory
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "http://mvs-1.flyffdb.info:4080/api/index/" . $currentSubDirectory );
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($ch, CURLOPT_USERPWD, "admin:testpass"); 

        $result=curl_exec ($ch);
        
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

            if (count($bulkBuffer) == 100) {
                $bulkBufferCallback($currentSubDirectory, $bulkBuffer);
            }
            
            $idx++;
        }
        $bulkBufferCallback($currentSubDirectory, $bulkBuffer);
    }
}

<?php

namespace Flyffdatabase\ContentGeneration {
    class APIHelper {
        public static function DownloadFromApi($endpointUrl) {
            if (!is_dir('./apicache'))
                mkdir('./apicache');
            if (file_exists('./apicache/' .  md5($endpointUrl))) {
                $content = file_get_contents('./apicache/' . md5($endpointUrl));
        
                return json_decode($content, true);
            }
        
            $baseApiUrl = 'https://flyff-api.sniegu.fr';
            $retryCount = 0;
            $downloadSuccess = false;
            $content = '';
            while (!$content) {
                if ($retryCount > 15)
                    break;
        
                usleep(250);
                $content = @file_get_contents($baseApiUrl . $endpointUrl);
                if ($content) {
                    $downloadSuccess = true;
                }
                $retryCount++;
            }
        
            if (!$downloadSuccess) {
                return false;
            }
            
            file_put_contents('./apicache/' .  md5($endpointUrl), $content);
            return json_decode($content, true);
        }

        public static function BatchDownloadFromApi ($dataTypeApiPrefix, $dataCallbackFunction) {
            $itemIds = self::DownloadFromApi($dataTypeApiPrefix);
            $batchItemIds = [];
            
            for($i = 0; $i < count($itemIds); $i++) {
                array_push($batchItemIds, $itemIds[$i]);
            
                if ((count($batchItemIds) == 25) || ($i == (count($itemIds) - 1))) {
                    $returnedData = self::DownloadFromApi($dataTypeApiPrefix . '/' . implode(',', $batchItemIds));
        
                    if (count($returnedData) > 0 && is_callable($dataCallbackFunction)) {
                        foreach($returnedData as $currentReturnedElement) {
                            $dataCallbackFunction($currentReturnedElement);
                        }
                    }
        
                    ProgressBarHelper::PercentBar($i + 1, count($itemIds), "processed");
                    $batchItemIds = [];
                }
            }
            echo PHP_EOL;
        }
    }
}
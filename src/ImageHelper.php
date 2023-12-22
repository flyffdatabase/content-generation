<?php

namespace Flyffdatabase\ContentGeneration {
    class ImageHelper {
        static protected $imageQueue=[];
        static protected $curlHandle = null;

        public static function QueueDownloadImage($remoteImage, $folderName, $fileName) {
            foreach(self::$imageQueue as $currentQueueItem) {
                if ($currentQueueItem['folderName'] == $folderName && $currentQueueItem['fileName'] == $fileName) {
                    return self::GenerateCDNUrl($folderName . '/' . $fileName);
                }
            }
            array_push(self::$imageQueue, [
                'remoteImage' => $remoteImage,
                'folderName' => $folderName,
                'fileName' => $fileName
            ]);

            return self::GenerateCDNUrl($folderName . '/' . $fileName);
        }

        public static function ProcessDownloadImageQueue() {
            $i = 0;
            foreach(self::$imageQueue as $currentQueueItem) {
                $i++;
                self::DownloadImage(
                    $currentQueueItem['remoteImage'], 
                    $currentQueueItem['folderName'], 
                    $currentQueueItem['fileName']
                );
                
                ProgressBarHelper::PercentBar($i, count(self::$imageQueue), $currentQueueItem['fileName']);
            }
            echo PHP_EOL;

            if (self::$curlHandle == null) {
            } else {
                \curl_close(self::$curlHandle);
            }
        }

        public static function DownloadImage($remoteImage, $folderName, $fileName) {
            // skip this image download if file already exists locally
            if (is_file('./images' . $folderName . '/' . $fileName)) {
                return self::GenerateCDNUrl($folderName . '/' . $fileName);
            }

            if (self::$curlHandle == null) {
                $ch = \curl_init();
                self::$curlHandle = $ch;
            } else {
                $ch = self::$curlHandle;
            }
            $downloadSuccess = false;
            while(!$downloadSuccess) {
                usleep(250);
                \curl_setopt($ch, CURLOPT_URL, $remoteImage);
                \curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                \curl_setopt($ch, CURLOPT_AUTOREFERER, false);
                \curl_setopt($ch, CURLOPT_HEADER, 0);
                $result = \curl_exec($ch);

                $info = \curl_getinfo($ch);
                $httpCode = $info['http_code'];
                
                if ($httpCode == 200) {
                    $downloadSuccess = true;
                }
            }
        
            if (!is_dir('./images' . $folderName)) mkdir('./images' . $folderName, 0777, true);
        
            // the following lines write the contents to a file in the same directory (provided permissions etc)
            $fp = fopen('./images' . $folderName . '/' . $fileName, 'w');
            fwrite($fp, $result);
            fclose($fp);

            return self::GenerateCDNUrl($folderName . '/' . $fileName);
        }

        public static function GenerateCDNUrl($filePath) {
            return '' . $filePath;
        }
    }
}

<?php

class ImageHelper {
    static protected $imageQueue=[];

    public static function QueueDownloadImage($remoteImage, $folderName, $fileName) {
        array_push(self::$imageQueue, [
            'remoteImage' => $remoteImage,
            'folderName' => $folderName,
            'fileName' => $fileName
        ]);

        return self::GenerateCDNUrl($folderName . '/' . $fileName);
    }

    public static function ProcessDownloadImageQueue() {
        foreach(self::$imageQueue as $currentQueueItem) {
            self::DownloadImage(
                $currentQueueItem['remoteImage'], 
                $currentQueueItem['folderName'], 
                $currentQueueItem['fileName']
            );
        }
    }

    public static function DownloadImage($remoteImage, $folderName, $fileName) {
        $retryCount = 0;
        $downloadSuccess = false;
        $remoteImageRaw = '';
        while (!$remoteImageRaw) {
            if ($retryCount < 10) {
                usleep(250);
                $remoteImageRaw = @file_get_contents($remoteImage);
                if ($remoteImageRaw) {
                    $downloadSuccess = true;
                }
                $retryCount++;
            }
        }

        if (!$downloadSuccess) {
            return false;
        }

        if (!is_dir('./images' . $folderName)) mkdir('./images' . $folderName, 0777, true);
        file_put_contents('./images' . $folderName . '/' . $fileName, $remoteImageRaw);
    
        return self::GenerateCDNUrl($folderName . '/' . $fileName);
    }

    public static function GenerateCDNUrl($filePath) {
        return 'https://ik.imagekit.io/flyffdb' . $filePath;
    }
}
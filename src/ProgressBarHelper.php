<?php

namespace Flyffdatabase\ContentGeneration {
    class ProgressBarHelper {
        public static function PercentBar($done, $total, $info="", $width=50)
        {
            $perc = round(($done * 100) / $total);
            $bar = round(($width * $perc) / 100);
            if (strlen($info) > 20) {
                $info = substr($info, 0, 16);
            }
            $info = $info . str_repeat(" ", 20 - strlen($info));

            echo sprintf("%s%%(%s/%s)[%s>%s]%s\r", $perc, $done, $total, str_repeat("=", $bar), str_repeat(" ", $width-$bar), $info);
        }
    }
}
<?php

class ProgressBarHelper {
    public static function PercentBar($done, $total, $info="", $width=50)
    {
        $perc = round(($done * 100) / $total);
        $bar = round(($width * $perc) / 100);
        echo sprintf("%s%%(%s/%s)[%s>%s]%s\r", $perc, $done, $total, str_repeat("=", $bar), str_repeat(" ", $width-$bar), $info);
    }
}

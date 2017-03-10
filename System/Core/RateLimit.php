<?php
    if (!defined("ROOT")) { exit(); }

    class RateLimit
    {
        public function Call($Data)
        {
            $Export = explode('.', $Data);

            $Key    = $Export[0] . '_' . $_SERVER['REMOTE_ADDR'];
            $Time   = microtime(true) * 1000;
            $Result = apcu_fetch($Key);

            if ($Result == false || $Time - $Result['Created'] > $Export[2])
            {
                apcu_store($Key, ['Remaining' => ($Export[1] - 1), 'Created' => $Time], 180);
                return;
            }

            if ($Result['Remaining'] >= 1)
            {
                apcu_store($Key, ['Remaining' => $Result['Remaining'] - 1, 'Created' => $Result['Created']], 180);
                return;
            }

            Tracer("Flood.log", $Key);
            JSON(["Message" => 2000]);
        }
    }
?>
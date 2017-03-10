<?php
    if (!defined("ROOT")) { exit(); }

    class Upload
    {
        public static function GetBestServer()
        {
            $Result = array();

            foreach (self::$ServerList as $Server)
            {
                $Channel = curl_init();
                curl_setopt($Channel, CURLOPT_URL, $Server);
                curl_setopt($Channel, CURLOPT_HEADER, false);
                curl_setopt($Channel, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($Channel, CURLOPT_POSTFIELDS, ["ACTION" => "UPLOAD_SPACE", "TOKEN" => self::GetServerToken($Server)]);
                $Result[$Server] = curl_exec($Channel);
                curl_close($Channel);
            }

            return array_keys($Result, max($Result))[0];
        }

        public static function GetServerToken($ServerID)
        {
            switch ($ServerID)
            {
                case 'http://10.48.9.81:8080/': return 'Access1'; break;
                case 'http://10.48.9.81:8081/': return 'Access2'; break;
            }
        }

        private static $ServerList =
        [
            'http://10.48.9.81:8080/',
            'http://10.48.9.81:8081/'
        ];
    }
?>
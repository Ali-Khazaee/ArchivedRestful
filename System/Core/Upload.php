<?php
    if (!defined("ROOT")) { exit(); }

    class Upload
    {
        private static $ServerList =
        [
            "1" => 'http://10.48.9.85:8080/',
            "2" => 'http://10.48.9.85:8081/'
        ];

        public static function GetBestServerID()
        {
            $Result = array();

            foreach (self::$ServerList as $ID => $Server)
            {
                $Channel = curl_init();
                curl_setopt($Channel, CURLOPT_URL, $Server);
                curl_setopt($Channel, CURLOPT_HEADER, false);
                curl_setopt($Channel, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($Channel, CURLOPT_POSTFIELDS, ["ACTION" => "UPLOAD_SPACE", "TOKEN" => self::GetServerToken($ID)]);
                $Result[$ID] = curl_exec($Channel);
                curl_close($Channel);
            }

            return array_keys($Result, max($Result))[0];
        }

        public static function GetServerURL($ID)
        {
           return $ServerList[$ID];
        }

        public static function DeleteFile($ID, $URL)
        {
            $Server = $ServerList[$ID];

            $Channel = curl_init();
            curl_setopt($Channel, CURLOPT_URL, $Server);
            curl_setopt($Channel, CURLOPT_HEADER, false);
            curl_setopt($Channel, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($Channel, CURLOPT_POSTFIELDS, ["ACTION" => "UPLOAD_REMOVE", "URL" => $Server . $URL, "TOKEN" => self::GetServerToken($ID)]);
            curl_exec($Channel);
            curl_close($Channel);
        }

        public static function GetServerToken($ID)
        {
            switch ($ID)
            {
                case "1": return 'Access1'; break;
                case "2": return 'Access2'; break;
            }
        }
    }
?>
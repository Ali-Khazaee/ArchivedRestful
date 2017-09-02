<?php
    if (!defined("ROOT")) { exit(); }

    class Upload
    {
        private static $ServerList =
        [
            "0" => UPLOAD_SERVER_1,
            "1" => UPLOAD_SERVER_2
        ];

        public static function GetBestServerID()
        {
            $Result = array();

            foreach (self::$ServerList as $ID => $Server)
            {
                $Channel = curl_init();
                curl_setopt($Channel, CURLOPT_URL, $Server . "StorageSpace");
                curl_setopt($Channel, CURLOPT_POST, true);
                curl_setopt($Channel, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($Channel, CURLOPT_POSTFIELDS, "Password=" . self::GetServerToken($ID));
                $ServerResult = json_decode(curl_exec($Channel));
                curl_close($Channel);

                $Result[$ID] = $ServerResult->Space;
            }

            return array_keys($Result, max($Result))[0];
        }

        public static function GetServerURL($ID)
        {
            if (array_key_exists($ID, self::$ServerList))
                return self::$ServerList[$ID];

            return "";
        }

        public static function DeleteFile($ID, $URL)
        {
            $Server = self::$ServerList[$ID];

            $Channel = curl_init();
            curl_setopt($Channel, CURLOPT_URL, $Server . "DeleteFile");
            curl_setopt($Channel, CURLOPT_POST, true);
            curl_setopt($Channel, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($Channel, CURLOPT_POSTFIELDS, "Password=" . self::GetServerToken($ID) . "&Path=" . $URL);
            $ServerResult = json_decode(curl_exec($Channel));
            curl_close($Channel);

            return $ServerResult->Result;
        }

        public static function GetServerToken($ID)
        {
            switch ($ID)
            {
                case "0": return UPLOAD_SERVER_1_TOKEN; break;
                case "1": return UPLOAD_SERVER_2_TOKEN; break;
            }
        }
    }
?>
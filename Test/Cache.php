<?php
    class Cache
    {
        // Cache Name
        private static $CacheName = 'default';

        // Store Data
        public static function Store($Key, $Data, $Expiration = 0)
        {
            $StoreData = array('time' => time(), 'expire' => $Expiration, 'data' => serialize($Data));

            $DataArray = self::LoadCache();

            if (true === is_array($DataArray))
            {
                $DataArray[$Key] = $StoreData;
            }
            else
            {
                $DataArray = array($Key => $StoreData);
            }

            $CacheData = json_encode($DataArray);
            file_put_contents(self::GetCacheDir(), $CacheData);
        }

        // Is Cached
        public static function IsCached($Key)
        {
            if (false != self::LoadCache())
            {
                $CachedData = self::LoadCache();
                return isset($CachedData[$Key]['data']);
            }
        }

        // Get Cache Data
        public static function GetData($Key, $TimeStamp = false)
        {
            $CachedData = self::LoadCache();

            (false === $TimeStamp) ? $Type = 'data' : $Type = 'time';

            if (!isset($CachedData[$Key][$Type]))
                return null; 

            return unserialize($CachedData[$Key][$Type]);
        }

        // Load Cache
        private static function LoadCache()
        {
            if (true === file_exists(self::GetCacheDir()))
            {
                $File = file_get_contents(self::GetCacheDir());
                return json_decode($File, true);
            }
            else
            {
                return false;
            }
        }

        // Get Cache Dir
        private static function GetCacheDir()
        {
            if (true === self::CheckCacheDir())
            {
                $Filename = self::$CacheName;
                $Filename = preg_replace('/[^0-9a-z\.\_\-]/i', '', strtolower($Filename));

                return "Cache/" . sha1($Filename) . ".cache";
            }
        }

        // Check Cache Dire
        private static function CheckCacheDir()
        {
            if (!is_dir("Cache/") && !mkdir("Cache/", 0775, true))
            {
                throw new Exception("Unable To Create Cache Directory Cache/");
            }
            elseif (!is_readable("Cache/") || !is_writable("Cache/"))
            {
                if (!chmod("Cache/", 0775))
                {
                    throw new Exception("Cache/ must be readable and writeable");
                }
            }

            return true;
        }
      
        // Set Cache Name
        public static function SetCacheName($Name)
        {
            self::$CacheName = $Name;
        }

        // Erase Cache
        public static function Erase($Key)
        {
            $CacheData = self::LoadCache();

            if (true === is_array($CacheData))
            {
                if (true === isset($CacheData[$Key]))
                {
                    unset($CacheData[$Key]);
                    $CacheData = json_encode($CacheData);
                    file_put_contents(self::GetCacheDir(), $CacheData);
                }
                else
                    return;
            }
        }

        // Erase Expired Cache
        public static function EraseExpired()
        {
            $CacheData = self::LoadCache();

            if (true === is_array($CacheData))
            {
                $Counter = 0;

                foreach ($CacheData as $Key => $Entry)
                {
                    if (true === self::CheckExpired($Entry['time'], $Entry['expire']))
                    {
                        unset($CacheData[$Key]);
                        $Counter++;
                    }
                }

                if ($Counter > 0)
                {
                    $CacheData = json_encode($CacheData);
                    file_put_contents(self::GetCacheDir(), $CacheData);
                }

                return $Counter;
            }
        }

        private static function CheckExpired($TimeStamp, $Expiration)
        {
            $Result = false;

            if ($Expiration !== 0)
            {
                $timeDiff = time() - $TimeStamp;
                ($timeDiff > $Expiration) ? $Result = true : $Result = false;
            }

            return $Result;
        }
    }
?>
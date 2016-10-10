<?php

    class RateLimit
    {
        // Call Rate limiter
        public function Call($MaxNumberOfRequests, $MilliSeconds)
        {
            // Get User Ip Address
            $Ip = $_SERVER['REMOTE_ADDR'];

            // Check if User Is Requested Before
            $Data = $this->Fetch($Ip);

            // If User Does not Requested Before : Create new Cache
            if ($Data == false) {

                $Remaining = ($MaxNumberOfRequests - 1);
                $this->Save($Ip, ['Remaining' => $Remaining, 'Created' => microtime(true)*1000]);

            } else {

                // If More Than $MilliSeconds milliseconds has passed : reset number of requests
                if((microtime(true)*1000 - $Data['Created']) > $MilliSeconds){
                    $this->Reset($Ip, $MaxNumberOfRequests);
                } else {
                    // If User still Allow to request : decrease number of requests and save
                    if ( $Data['Remaining'] >= 1) {
                        $Remaining = $Data['Remaining'] - 1;
                        $this->Save($Ip, ['Remaining' => $Remaining, 'Created'   => $Data['Created']]);
                    // If Number of requests Exceeded :  Fail
                    } else {
                        $this->Fail();
                    }
                }

            }

        }

        // Fetch User info from cache
        protected function Fetch($key)
        {
            return apcu_fetch($key);
        }

        // Save User info to Cache
        protected function Save($key, $value)
        {
            // Expire cache after 1 hour
            apcu_store($key, $value, 3600);
        }

        // Reset cache and then expire after 1 hour
        protected function Reset($Ip, $MaxNumberOfRequests)
        {
            apcu_store($Ip, ['Remaining' => $MaxNumberOfRequests -1 , 'Created'   => microtime(true)*1000], 3600);
        }

        // Fail : Number Of Allowed Requests Exceeded
        protected function Fail()
        {
            JSON(["Status" => "Fail", "Message" => Lang("RATELIMIT_MAX_REQUESTS_EXCEED")], 429);
        }
    }
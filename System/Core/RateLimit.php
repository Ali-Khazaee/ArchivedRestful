<?php

    class RateLimit
    {
        public function Call($MaxNumberOfRequests, $ExpireSeconds)
        {
            $Ip = $_SERVER['REMOTE_ADDR'];

            $Data = $this->Fetch($Ip);

            if ($Data == false) {

                $Remaining = ($MaxNumberOfRequests - 1);
                $this->Save($Ip, ['Remaining' => $Remaining, 'Created' => time()], $ExpireSeconds);

            } else {

                // Take the current request rate limit and update it
                if (($Data['Remaining'] - 1) >= 0) {
                    $Remaining = $Data['Remaining'];
                } else {
                    $Remaining = -1;
                }

                $Expire = $ExpireSeconds - (time() - $Data['Created']);
                $this->Save($Ip, ['Remaining' => $Remaining, 'Created'   => $Data['Created']], $Expire);
            }

            // Check if the current Request is allowed to pass
            if ($Remaining < 0) {
                // Too Many Requests
                $this->Fail();
            }
        }

        protected function Fetch($key)
        {
            return apc_fetch($key);
        }

        protected function Save($key, $value, $expire)
        {
            apc_store($key, $value, $expire);
        }


        protected function Fail()
        {
            JSON(["Status" => "Fail", "Message" => Lang("RATELIMIT_MAX_REQUESTS_EXCEED")], 429);
        }
    }
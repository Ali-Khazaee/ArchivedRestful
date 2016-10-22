<?php
    if (!defined("ROOT")) { exit(); }

    class RateLimit
    {
        public function Call($Request, $Time)
        {
            $IP = $_SERVER['REMOTE_ADDR'];
            $CreatedTime = microtime(true) * 1000;
            $Result = $this->Fetch($IP);

            // Create New Limit
            if ($Result == false)
            {
                $this->Save($IP, ['Remaining' => ($Request - 1), 'Created' => $CreatedTime]);
            }
            else
            {
                // Reset The Limit
                if ($CreatedTime - $Result['Created'] > $Time)
                {
                    $this->Update($IP, $Request);
                }
                else
                {
                    // Decrease Remaining
                    if ($Result['Remaining'] >= 1)
                    {
                        $this->Save($IP, ['Remaining' => $Result['Remaining'] - 1, 'Created' => $Result['Created']]);
                    }
                    else
                    {
                        $this->Failed();
                    }
                }
            }
        }

        private function Fetch($Key)
        {
            return apcu_fetch($Key);
        }

        private function Save($Key, $Value)
        {
            apcu_store($Key, $Value, 3600);
        }

        private function Update($IP, $Request)
        {
            apcu_store($IP, ['Remaining' => $Request - 1, 'Created' => microtime(true) * 1000], 3600);
        }

        private function Failed()
        {
            JSON(["Status" => "Failed", "Message" => "RATELIMIT_MAX_REQUESTS_EXCEED"], 429);
        }
    }
?>
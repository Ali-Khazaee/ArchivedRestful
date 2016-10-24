<?php
    if (!defined("ROOT")) { exit(); }

    class RateLimit
    {
        public function Call($Input)
        {
            $Input = explode('.', $Input);
            $RequestName = $Input[0];
            $Request = $Input[1];
            $Time = $Input[2];
            $IP = $_SERVER['REMOTE_ADDR'];
            $Key = $RequestName . '_' . $IP;
            $CreatedTime = microtime(true) * 1000;
            $Result = $this->Fetch($Key);

            // Create New Limit
            if ($Result == false)
            {
                $this->Save($Key, ['Remaining' => ($Request - 1), 'Created' => $CreatedTime]);
            }
            else
            {
                // Reset The Limit
                if ($CreatedTime - $Result['Created'] > $Time)
                {
                    $this->Update($Key, $Request);
                }
                else
                {
                    // Decrease Remaining
                    if ($Result['Remaining'] >= 1)
                    {
                        $this->Save($Key, ['Remaining' => $Result['Remaining'] - 1, 'Created' => $Result['Created']]);
                    }
                    else
                    {
                        $this->Failed($Key);
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

        private function Update($Key, $Request)
        {
            apcu_store($Key, ['Remaining' => $Request - 1, 'Created' => microtime(true) * 1000], 3600);
        }

        private function Failed($Key)
        {
            Tracer("Flood.log" , $Key);
            JSON(["Status" => "Failed", "Message" => "RATELIMIT_MAX_REQUESTS_EXCEED"], 429);
        }
    }
?>
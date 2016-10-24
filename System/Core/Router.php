<?php
    if (!defined("ROOT")) { exit(); }

    class Router
    {
        public $Routes = array();
        public $SkipAuth = array();
        public $CallBacks = array();
        public $RateLimit = array();

        public function Execute($App)
        {
            $RouteName = substr($_SERVER['REQUEST_URI'], 1);

            if (in_array($RouteName, $this->Routes))
            {
                $Key = array_keys($this->Routes, $RouteName)[0];

                if ($this->SkipAuth[$Key] == false)
                    $App->Auth->CheckToken();

                $App->RateLimit->Call($this->RateLimit[$Key]);

                call_user_func($this->CallBacks[$Key]);
            }
        }

        public function __call($Method, $Params)
        {
            array_push($this->Routes, $Params[0]);
            array_push($this->CallBacks, $Params[1]);
            array_push($this->SkipAuth, isset($Params[2]) ? $Params[2] : false);
            array_push($this->RateLimit, isset($Params[3]) ? $Params[3] : $Params[0].'.20.1000');
        }
    }
?>
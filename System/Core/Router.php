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

                if ($this->SkipAuth[$Key])
                    $App->Auth->CheckToken();

                $App->RateLimit->Call($this->RateLimit[$Key]);

                call_user_func($this->CallBacks[$Key]);
            }
        }

        public function Call($Route, $CallBack, $Auth, $Rate)
        {
            array_push($this->Routes, $Route);
            array_push($this->CallBacks, $CallBack);
            array_push($this->SkipAuth, $Auth);
            array_push($this->RateLimit, $Rate);
        }
    }
?>
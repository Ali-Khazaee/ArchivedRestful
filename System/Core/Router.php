<?php
    if (!defined("ROOT")) { exit(); }

    class Router
    {
        public $Routes = array();
        public $SkipAuth = array();
        public $CallBacks = array();

        public function Execute($App)
        {
            $URL = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH); // @TODO in bayad OKAY she ( substr avalin kalame / )
            $this->Routes = preg_replace('/\/+/', '/', $this->Routes); // @TODO in nabayad bashe

            if (in_array($URL, $this->Routes))
            {
                $Key = array_keys($this->Routes, $URL)[0];

                if ($this->SkipAuth[$Key] == false)
                    $App->Auth->CheckToken();

                call_user_func($this->CallBacks[$Key]);
            }
        }

        public function __call($Method, $Params)
        {
            $URL = dirname($_SERVER['REQUEST_URI']) . '/' . $Params[0]; // @TODO in nabayad bashe

            array_push($this->Routes, $URL);
            array_push($this->SkipAuth, isset($Params[2]) ? $Params[2] : false);
            array_push($this->CallBacks, $Params[1]);
        }
    }
?>
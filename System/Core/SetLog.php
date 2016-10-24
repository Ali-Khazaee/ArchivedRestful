<?php
    if (!defined("ROOT")) { exit(); }

    class SetLog
    {
        private $App;

        public function __construct($App)
        {
            $this->App = $App;
        }

        public function Create($Type, $Data)
        {
            $this->App->DB->Insert('log', ['IP' => $_SERVER['REMOTE_ADDR'], 'Type' => $Type, 'Data' => $Data, 'CreatedTime' => time()]);
        }
    }
?>
<?php
    // Don't Allow Access Directly
    if (!defined("ROOT")) { exit(); }

    class Log
    {
        protected $App;

        public function __construct($App){
            $this->App = $App;
        }

        // Create Log
        public function Create($Type,$Data){

            // Get User Ip Address
            $IP = $_SERVER['REMOTE_ADDR'];
            $this->App->DB->Insert('logs', ['IP' => $IP, 'Type' => $Type, 'Data' => $Data, 'CreatedTime' => time()] );
        }

    }
?>
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
        public function Create($UserID,$EventName){
            $this->App->DB->Insert('logs', ['UserID' => $UserID, 'EventName' => $EventName, 'CreationTime' => time()] );
        }

    }
?>
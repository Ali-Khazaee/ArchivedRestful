<?php
    class Application
    {
        // DataBase Variable
        protected $DB;

        public function __construct()
        {
            // Connecting To DataBase
            $this->DB = new DataBase();

            // Route The POST Request
            if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST["Action"]))
            {
                switch ($_POST["Action"])
                {
                    case "AccountRegister":
                        Account::Register($this);
                        break;
                }
            }
        }
    }
?>
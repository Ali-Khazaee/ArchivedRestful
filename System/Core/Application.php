<?php
    class Application
    {
        public function __construct()
        {
            if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST["Action"]))
            {
                switch ($_POST["Action"])
                {
                    case "AccountRegister":
                        Account::Register();
                        break;
                }
            }
        }
    }
?>
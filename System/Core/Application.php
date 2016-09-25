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





        // testing: insert data to table
        public function testInsert($table_name, array $data)
        {
            $this->DB->insert($table_name, ['name'], $data);
        }

        // testing: fetch all data from a specific table
        public function testTable($table_name)
        {
            $results = $this->DB->all($table_name);
            return $results;
        }


        
        
        
        
    }
?>
<?php

    class Application
    {

        protected $db;

        public function __construct()
        {

            // connecting to database
            $this->db = new Database();

            if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST["Action"])) {
                switch ($_POST["Action"]) {
                    case "AccountRegister":
                        Account::Register();
                        break;
                }
            }
        }





        // testing: insert data to table
        public function testInsert($table_name, array $data){
            $db = new Database();
            $db->insert($table_name, ['name'], $data);
        }

        // testing: fetch all data from a specific table
        public function testTable($table_name)
        {

            $db = new Database();
            $results = $db->all($table_name);
            return $results;

        }


    }

?>
<?php

    use MongoDB\Driver\Manager;
    use MongoDB\Driver\Command;
    use MongoDB\Driver\Query;
    use MongoDB\Driver\BulkWrite;
    use MongoDB\Driver\Exception\Exception;

    class Database
    {
        protected $manager;
        protected $command;
        protected $query;

        public function __construct()
        {
            try {

                $this->manager = new Manager("mongodb://localhost:27017");

            } catch (Exception $e) {

                echo "Database Connection Error: \n";

                echo "Message:", $e->getMessage(), "\n";

            }

        }


        public function insert($tableName, array $data)
        {

            $bulk = new BulkWrite;

            $data = array_merge(['_id' => new MongoDB\BSON\ObjectID], $data);
            $bulk->insert($data);

            $this->manager->executeBulkWrite( DB_NAME.'.'. $tableName, $bulk);

        }


        public function all($table_name){

                $query = new MongoDB\Driver\Query([]);

                $results = $this->manager->executeQuery(DB_NAME.".".$table_name, $query);

                return $results;
        }


    }

?>
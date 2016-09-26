<?php
    // DataBase Driver
    use MongoDB\Driver\Manager;
    use MongoDB\Driver\Query;
    use MongoDB\Driver\BulkWrite;


    class DataBase
    {
        protected $manager;
        protected $command;
        protected $query;

        public function __construct()
        {

            try {

                $this->manager = new Manager("mongodb://localhost:27017",
                    [
                        'username' => DB_USERNAME,
                        'password' => DB_PASSWORD
                    ]);

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

            $this->manager->executeBulkWrite(DB_NAME . '.' . $tableName, $bulk);

        }




        public function all($tableName)
        {

            $query = new Query([]);

            $results = $this->manager->executeQuery(DB_NAME . "." . $tableName, $query);

            return $results;
        }




        public function delete($tableName, array $condition)
        {

            $bulk = new BulkWrite;

            $bulk->delete($condition);

            $this->manager->executeBulkWrite(DB_NAME . '.' . $tableName, $bulk);

        }




        public function find($tableName, array $condition)
        {

            $query = new Query($condition);

            $results = $this->manager->executeQuery(DB_NAME . "." . $tableName, $query);

            return $results;

        }





        public function update($tableName, array $conditions, array $data)
        {

            $bulk = new BulkWrite;

            $bulk->update( $conditions, ['$set' => $data]);

            $this->manager->executeBulkWrite(DB_NAME . '.' . $tableName, $bulk);

        }




    }

?>
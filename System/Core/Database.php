<?php
    // DataBase Driver
    use MongoDB\Driver\Manager;
    use MongoDB\Driver\Query;
    use MongoDB\Driver\BulkWrite;

    class DataBase
    {
        // DataBase Manager
        protected $Manager;

        // DataBase Query
        protected $query;

        // Connection
        public function __construct()
        {
            try
            {
                $this->Manager = new Manager("mongodb://" . DB_HOST . ":" . DB_PORT, [ 'username' => DB_USERNAME, 'password' => DB_PASSWORD ]);
            }
            catch (Exception $e)
            {
                Tracer("DataBaseError.log", "Connection Error:" . $e->getMessage());
                exit("Connection Error:" . $e->getMessage());
            }
        }

        public function insert($tableName, array $data)
        {

            $bulk = new BulkWrite;

            $data = array_merge(['_id' => new MongoDB\BSON\ObjectID], $data);
            $bulk->insert($data);

            $this->Manager->executeBulkWrite(DB_NAME . '.' . $tableName, $bulk);

        }

        public function all($tableName)
        {

            $query = new Query([]);

            $results = $this->Manager->executeQuery(DB_NAME . "." . $tableName, $query);

            return $results;
        }

        public function delete($tableName, array $condition)
        {

            $bulk = new BulkWrite;

            $bulk->delete($condition);

            $this->Manager->executeBulkWrite(DB_NAME . '.' . $tableName, $bulk);

        }

        public function find($tableName, array $condition)
        {

            $query = new Query($condition);

            $results = $this->Manager->executeQuery(DB_NAME . "." . $tableName, $query);

            return $results;

        }

        public function update($tableName, array $conditions, array $data)
        {

            $bulk = new BulkWrite;

            $bulk->update( $conditions, ['$set' => $data]);

            $this->Manager->executeBulkWrite(DB_NAME . '.' . $tableName, $bulk);

        }
    }
?>
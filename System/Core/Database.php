<?php
    // DataBase Driver
    use MongoDB\Driver\Manager;
    use MongoDB\Driver\Query;
    use MongoDB\Driver\BulkWrite;

    class DataBase
    {
        // DataBase Manager
        protected $Manager;

        // Connection
        public function __construct()
        {
            try
            {
                $this->Manager = new Manager("mongodb://" . DB_HOST . ":" . DB_PORT,
                [
                    'username' => DB_USERNAME,
                    'password' => DB_PASSWORD
                ]);
            }
            catch (Exception $e)
            {
                Tracer("DataBaseError.log", "Connection Error:" . $e->getMessage());
                exit("Connection Error:" . $e->getMessage());
            }
        }

        // Insert Into Collection
        public function Insert($Collection, $Data)
        {
            $Bulk = new BulkWrite;

            $Data = array_merge(['_id' => new MongoDB\BSON\ObjectID], $Data);
            $Bulk->insert($Data);

            $this->Manager->executeBulkWrite(DB_NAME . '.' . $Collection, $Bulk);
        }

        // Delete From Collection
        public function Delete($Collection, $Condition)
        {
            $Bulk = new BulkWrite;

            $Bulk->delete($Condition);

            $this->Manager->executeBulkWrite(DB_NAME . '.' . $Collection, $Bulk);
        }

        // Update The Collection
        public function Update($Collection, $Conditions, $Data)
        {
            $Bulk = new BulkWrite;

            $Bulk->update($Conditions, ['$set' => $Data]);

            $this->Manager->executeBulkWrite(DB_NAME . '.' . $Collection, $Bulk);
        }

        // Find Item In Collection
        public function Find($Collection, $Conditions)
        {
            $Query = new Query($Conditions);

            $Result = $this->Manager->executeQuery(DB_NAME . "." . $Collection, $Query);

            return $Result;
        }
    }
?>
<?php
    // Don't Allow Access Directly
    if (!defined("ROOT")) { exit(); }

    // DataBase Driver
    use MongoDB\Driver\Manager;
    use MongoDB\Driver\Query;
    use MongoDB\Driver\BulkWrite;

    class DataBase
    {
        protected $Manager;

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
                JSON(["Status" => "Failed", "Message" => Lang("DATABASE_CONNECTION")], 500);
            }
        }

        public function Insert($Collection, $Data)
        {
            $Bulk = new BulkWrite;

            $Data = array_merge(['_id' => new MongoDB\BSON\ObjectID], $Data);

            $Bulk->insert($Data);

            $this->Manager->executeBulkWrite(DB_NAME . '.' . $Collection, $Bulk);
        }

        public function Delete($Collection, $Condition)
        {
            $Bulk = new BulkWrite;

            $Bulk->delete($Condition);

            $this->Manager->executeBulkWrite(DB_NAME . '.' . $Collection, $Bulk);
        }

        public function Update($Collection, $Conditions, $Data)
        {
            $Bulk = new BulkWrite;

            $Bulk->update($Conditions, $Data);

            $this->Manager->executeBulkWrite(DB_NAME . '.' . $Collection, $Bulk);
        }

        public function Find($Collection, $Conditions)
        {
            $Query = new Query($Conditions);

            $Result = $this->Manager->executeQuery(DB_NAME . "." . $Collection, $Query);

            return $Result;
        }
    }
?>
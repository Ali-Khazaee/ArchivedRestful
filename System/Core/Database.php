<?php
    if (!defined("ROOT")) { exit(); }

    use MongoDB\Driver\Manager;
    use MongoDB\Driver\Query;
    use MongoDB\Driver\BulkWrite;

    class DataBase
    {
        private $Manager;

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
                JSON(["Status" => "Failed", "Message" => Lang("DATABASE_CONNECTION")]);
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

        public function Update($Collection, $Condition, $Data)
        {
            $Bulk = new BulkWrite;

            $Bulk->update($Condition, $Data);

            $this->Manager->executeBulkWrite(DB_NAME . '.' . $Collection, $Bulk);
        }

        public function Find($Collection, $Condition)
        {
            $Query = new Query($Condition);

            $Result = $this->Manager->executeQuery(DB_NAME . "." . $Collection, $Query);

            return $Result;
        }
    }
?>
<?php
    if (!defined("ROOT")) { exit(); }

    use MongoDB\Driver\BulkWrite;

    class DataBase
    {
        private $Manager;

        public function __construct()
        {
            try
            {
                $this->Manager = new MongoDB\Driver\Manager("mongodb://" . DB_USERNAME . ":" . DB_PASSWORD . "@" . DB_HOST . ":" . DB_PORT . "/" . DB_NAME);
            }
            catch (Exception $e)
            {
                Tracer("DataBaseError.log", "Connection Error:" . $e->getMessage());
                JSON(["Message" => 2001]);
            }
        }

        public function Command($Collection, $Query)
        {
            $Command = new MongoDB\Driver\Command($Query);

            $Result = $this->Manager->executeCommand(DB_NAME . '.' . $Collection, $Command);

            return $Result;
        }

        public function Insert($Collection, $Query)
        {
            $Bulk = new BulkWrite;

            $ID = new MongoDB\BSON\ObjectID;

            $Query = array_merge(['_id' => $ID], $Query);

            $Bulk->insert($Query);

            $this->Manager->executeBulkWrite(DB_NAME . '.' . $Collection, $Bulk);

            return $ID;
        }

        public function Remove($Collection, $Condition)
        {
            $Bulk = new BulkWrite;

            $Bulk->delete($Condition);

            $this->Manager->executeBulkWrite(DB_NAME . '.' . $Collection, $Bulk);
        }

        public function Update($Collection, $Condition, $Query)
        {
            $Bulk = new BulkWrite;

            $Bulk->update($Condition, $Query);

            $this->Manager->executeBulkWrite(DB_NAME . '.' . $Collection, $Bulk);
        }

        public function Find($Collection, $Condition, $Option = [])
        {
            $Query = new MongoDB\Driver\Query($Condition, $Option);

            $Result = $this->Manager->executeQuery(DB_NAME . "." . $Collection, $Query);

            return $Result;
        }
    }
?>
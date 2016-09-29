<?php
    class Application
    {
        // DataBase Variable
        public $DB;
        // Router Instance
        protected $Router;

        public function __construct()
        {
            // Connecting To DataBase
            $this->DB = new DataBase();

            $this->Router = new Router();

            // Route For Registering Users
            $this->Router->post(BIOGRAM_BASE_ROUTE.'AccountRegister', function(){
                Account::Register($this);
            });




            $this->Router->run();


            
        }






    }
?>
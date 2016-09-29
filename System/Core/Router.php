<?php
    class Router
    {
        // Variables
        public $Routes = array();
        public $CallBacks = array();

        // Execute Request
        public function Execute()
        {
            $URL = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            $this->Routes = preg_replace('/\/+/', '/', $this->Routes);

            // Route Is Defined
            if (in_array($URL, $this->Routes))
            {
                // Find Route Index
                $Key = array_keys($this->Routes, $URL)[0];

                // Allow Post Method Only
                if ($_SERVER['REQUEST_METHOD'] == "POST")
                    call_user_func($this->CallBacks[$Key]);
            }
        }

        // Default Function
        public function __call($Method, $Params)
        {
            $URL = dirname($_SERVER['REQUEST_URI']) . '/' . $Params[0];

            array_push($this->Routes, $URL);
            array_push($this->CallBacks, $Params[1]);
        }
    }
?>
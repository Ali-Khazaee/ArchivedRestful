<?php
    class Account
    {
        /*
         * Result Translate
         * - 1 = Username Empty
         * - 2 = Password Empty
         * - 3 = Email Empty
         * - 4 = Email Wrong
         * - 5 = Username Taken
         * - 100 = Success
         */
        public static function Register($App)
        {
            // Getting Data
            $Data = json_decode(file_get_contents("php://input"));

            // Username Filter
            if (!isset($Data->Username) || empty($Data->Username))
                JSON(["Status" => "Failed", "Message" => 1]);

            // Password Filter
            if (!isset($Data->Password) || empty($Data->Password))
                JSON(["Status" => "Failed", "Message" => 2]);

            // Email Filter
            if (!isset($Data->Email) || empty($Data->Email))
                JSON(["Status" => "Failed", "Message" => 3]);

            if (!filter_var($Data->Email, FILTER_VALIDATE_EMAIL))
                JSON(["Status" => "Failed", "Message" => 4]);

            ///@TODO Filter More Data -- Length - Characters

            // Variables
            $Username = $Data->Username;
            $Password = $Data->Password; // Encrypt Me Later !!
            $Email = $Data->Email;

            // Getting Data
            $User = $App->DB->find('account', ['Username' => $Username])->toArray();

            // Username Filter
            if (empty($User))
            {
                $App->DB->Insert('account', ['Username' => $Username, 'Password' => $Password, 'Email' => $Email]);

                JSON("Status" => "Success", "Message" => 100);
            }

            JSON(["Status" => "Failed", "Message" => 5]);
        }
    }
?>
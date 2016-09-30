<?php
    class Account
    {
        /*
         * Result Translate
         * - 1 = Username Empty
         * - 2 = Password Empty
         * - 3 = Email Empty
         * - 4 = Email Wrong
         * - 5 = Username Short
         * - 6 = Username Long
         * - 7 = Password Short
         * - 8 = Password Long
         * - 9 = Username Invalid
         * - 10 = Username Taken
         * - 100 = Success
         */
        public static function Register($App)
        {
            // Getting Data
            $Data = json_decode(file_get_contents("php://input"));

            if (!isset($Data->Username) || empty($Data->Username))
                JSON(["Status" => "Failed", "Message" => 1]);

            if (!isset($Data->Password) || empty($Data->Password))
                JSON(["Status" => "Failed", "Message" => 2]);

            if (!isset($Data->Email) || empty($Data->Email))
                JSON(["Status" => "Failed", "Message" => 3]);

            if (!filter_var($Data->Email, FILTER_VALIDATE_EMAIL))
                JSON(["Status" => "Failed", "Message" => 4]);

            if (strlen($Data->Username) <= 2)
                JSON(["Status" => "Failed", "Message" => 5]);

            if (strlen($Data->Username) >= 33)
                JSON(["Status" => "Failed", "Message" => 6]);

            if (strlen($Data->Password) <= 4)
                JSON(["Status" => "Failed", "Message" => 7]);

            if (strlen($Data->Password) >= 33)
                JSON(["Status" => "Failed", "Message" => 8]);

            if (!preg_match('/[^A-Za-z0-9]/', $Data->Username))
                JSON(["Status" => "Failed", "Message" => 9]);

            // Variables
            $Username = $Data->Username;
            $Password = $Data->Password; // Encrypt Me Later !!
            $Email = $Data->Email;

            // Getting Data
            $User = $App->DB->find('account', ['Username' => $Username])->toArray();

            if (empty($User))
            {
                $App->DB->Insert('account', ['Username' => $Username, 'Password' => $Password, 'Email' => $Email]);

                JSON("Status" => "Success", "Message" => 100);
            }

            JSON(["Status" => "Failed", "Message" => 5]);
        }
    }
?>
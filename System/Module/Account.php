<?php
    class Account
    {
        public static function Register($App)
        {
            $Data = json_decode(file_get_contents("php://input"));

            if (!isset($Data->Username) || empty($Data->Username))
                JSON(["Status" => "Failed", "Message" => $Lang["ACC_REG_ERROR_EMPTY_USERNAME"]]);

            if (!isset($Data->Password) || empty($Data->Password))
                JSON(["Status" => "Failed", "Message" => $Lang["ACC_REG_ERROR_EMPTY_PASSWORD"]]);

            if (!isset($Data->Email) || empty($Data->Email))
                JSON(["Status" => "Failed", "Message" => $Lang["ACC_REG_ERROR_EMPTY_EMAIL"]]);

            if (!filter_var($Data->Email, FILTER_VALIDATE_EMAIL))
                JSON(["Status" => "Failed", "Message" => $Lang["ACC_REG_ERROR_INVALID_EMAIL"]]);

            if (strlen($Data->Username) <= 2)
                JSON(["Status" => "Failed", "Message" => $Lang["ACC_REG_ERROR_SHORT_USERNAME"]]);

            if (strlen($Data->Username) >= 33)
                JSON(["Status" => "Failed", "Message" => $Lang["ACC_REG_ERROR_LONG_USERNAME"]]);

            if (strlen($Data->Password) <= 4)
                JSON(["Status" => "Failed", "Message" => $Lang["ACC_REG_ERROR_SHORT_PASSWORD"]]);

            if (strlen($Data->Password) >= 33)
                JSON(["Status" => "Failed", "Message" => $Lang["ACC_REG_ERROR_LONG_PASSWORD"]]);

            if (strlen($Data->Email) >= 65)
                JSON(["Status" => "Failed", "Message" => $Lang["ACC_REG_ERROR_LONG_EMAIL"]]);

            if (!preg_match("/^(?![^A-Za-z])(?!.*\.\.)[A-Za-z0-9_.]+(?<![^A-Za-z])$/", $Data->Username))
                JSON(["Status" => "Failed", "Message" => $Lang["ACC_REG_ERROR_INVALID_USERNAME"]]);

            $Username = $Data->Username;
            $Password = password_hash($Data->Password, PASSWORD_BCRYPT);
            $Email = $Data->Email;
            $CreationTime = time();

            $_Username = $App->DB->find('account', ['Username' => $Username])->toArray();

            if (!empty($_Username))
                JSON(["Status" => "Failed", "Message" => $Lang["ACC_REG_ERROR_ALREADY_USERNAME"]]);

            $_Email = $App->DB->find('account', ['Email' => $Email])->toArray();

            if (!empty($_Email))
                JSON(["Status" => "Failed", "Message" => $Lang["ACC_REG_ERROR_ALREADY_EMAIL"]]);

            $App->DB->Insert('account', ['Username' => $Username, 'Password' => $Password, 'Email' => $Email, 'CreationTime' => $CreationTime, 'LastOnlineTime' => $CreationTime]);

            JSON(["Status" => "Success", "Message" => $Lang["SUCCESS"]]);
        }

        /*
         * - 1 = Username Empty
         * - 2 = Password Empty
         * - 3 = Session Empty
         * - 4 = Username Short
         * - 5 = Username Long
         * - 6 = Password Short
         * - 7 = Password Long
         * - 8 = Username Invalid
         * - 12 = Email Taken
         */

        public static function Login($App)
        {
            $Data = json_decode(file_get_contents("php://input"));

            if (!isset($Data->Username) || empty($Data->Username))
                JSON(["Status" => "Failed", "Message" => 1]);

            if (!isset($Data->Password) || empty($Data->Password))
                JSON(["Status" => "Failed", "Message" => 2]);

            if (!isset($Data->Session) || empty($Data->Session))
                JSON(["Status" => "Failed", "Message" => 3]);

            if (strlen($Data->Username) <= 2)
                JSON(["Status" => "Failed", "Message" => 4]);

            if (strlen($Data->Username) >= 33)
                JSON(["Status" => "Failed", "Message" => 5]);

            if (strlen($Data->Password) <= 4)
                JSON(["Status" => "Failed", "Message" => 6]);

            if (strlen($Data->Password) >= 33)
                JSON(["Status" => "Failed", "Message" => 7]);

//            if (!preg_match('/[^A-Za-z0-9]/', $Data->Username))
//                JSON(["Status" => "Failed", "Message" => 8]);

            $Username = $Data->Username;
            $Password = $Data->Password;
            $Session = $Data->Session;

            $Account = $App->DB->find('account', ['Username' => $Username])->toArray();
            $UserId =  $Account[0]->_id->__toString();

            if (empty($Account))
                JSON(["Status" => "Failed", "Message" => 9]);

            if ($Account[0]->Password != $Password)
                JSON(["Status" => "Failed", "Message" => 11]);

            $Token = $App->Auth->CreateToken(['UserId' => $UserId]);

            // Save Token to database
            $App->Auth->SaveToken(['UserId' => $UserId, 'Session' => $Session, 'Token' => $Token], $App);

            JSON(["Status" => "Success", "Message" => 100, "Token" => $Token]);
        }

        // Logout and Delete Token From DataBase
        public static function Logout($App)
        {
            // Token ---> CheckToken() is done for this route before!
            $Token = $_SERVER['HTTP_TOKEN'];

            // Delete Token From DataBase
            $App->DB->Delete('tokens',  ['Token' => $Token]);

            JSON(["Status" => "Success", "Message" => 100]);
        }
    }
?>
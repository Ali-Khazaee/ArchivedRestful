<?php
    class Account
    {
        public static function Register($App)
        {
            $Data = json_decode(file_get_contents("php://input"));

            if (!isset($Data->Username) || empty($Data->Username))
                JSON(["Status" => "Failed", "Message" => $Lang["GEN_EMPTY_USERNAME"]]);

            if (!isset($Data->Password) || empty($Data->Password))
                JSON(["Status" => "Failed", "Message" => $Lang["GEN_EMPTY_PASSWORD"]]);

            if (!isset($Data->Email) || empty($Data->Email))
                JSON(["Status" => "Failed", "Message" => $Lang["ACC_REG_EMPTY_EMAIL"]]);

            if (!filter_var($Data->Email, FILTER_VALIDATE_EMAIL))
                JSON(["Status" => "Failed", "Message" => $Lang["ACC_REG_INVALID_EMAIL"]]);

            if (strlen($Data->Username) <= 2)
                JSON(["Status" => "Failed", "Message" => $Lang["ACC_REG_SHORT_USERNAME"]]);

            if (strlen($Data->Username) >= 33)
                JSON(["Status" => "Failed", "Message" => $Lang["ACC_REG_LONG_USERNAME"]]);

            if (strlen($Data->Password) <= 4)
                JSON(["Status" => "Failed", "Message" => $Lang["ACC_REG_SHORT_PASSWORD"]]);

            if (strlen($Data->Password) >= 33)
                JSON(["Status" => "Failed", "Message" => $Lang["ACC_REG_LONG_PASSWORD"]]);

            if (strlen($Data->Email) >= 65)
                JSON(["Status" => "Failed", "Message" => $Lang["ACC_REG_LONG_EMAIL"]]);

            if (!preg_match("/^(?![^A-Za-z])(?!.*\.\.)[A-Za-z0-9_.]+(?<![^A-Za-z])$/", $Data->Username))
                JSON(["Status" => "Failed", "Message" => $Lang["ACC_REG_INVALID_USERNAME"]]);

            $Username = $Data->Username;
            $Password = password_hash($Data->Password, PASSWORD_BCRYPT);
            $Email = $Data->Email;
            $CreationTime = time();

            $_Username = $App->DB->find('account', ['Username' => $Username])->toArray();

            if (!empty($_Username))
                JSON(["Status" => "Failed", "Message" => $Lang["ACC_REG_ALREADY_USERNAME"]]);

            $_Email = $App->DB->find('account', ['Email' => $Email])->toArray();

            if (!empty($_Email))
                JSON(["Status" => "Failed", "Message" => $Lang["ACC_REG_ALREADY_EMAIL"]]);

            $App->DB->Insert('account', ['Username' => $Username, 'Password' => $Password, 'Email' => $Email, 'CreationTime' => $CreationTime, 'LastOnlineTime' => $CreationTime]);

            // TODO SendMail
            // TODO Log

            JSON(["Status" => "Success", "Message" => $Lang["SUCCESS"]]);
        }

        public static function Login($App)
        {
            $Data = json_decode(file_get_contents("php://input"));

            if (!isset($Data->Username) || empty($Data->Username))
                JSON(["Status" => "Failed", "Message" => $Lang["ACC_LOGIN_EMPTY_USERNAME"]]);

            if (!isset($Data->Password) || empty($Data->Password))
                JSON(["Status" => "Failed", "Message" => $Lang["ACC_LOGIN_EMPTY_PASSWORD"]]);

            if (!isset($Data->Session) || empty($Data->Session))
                JSON(["Status" => "Failed", "Message" => $Lang["ACC_LOGIN_EMPTY_SESSION"]]);

            if (strlen($Data->Username) <= 2)
                JSON(["Status" => "Failed", "Message" => $Lang["ACC_LOGIN_SHORT_USERNAME"]]);

            if (strlen($Data->Username) >= 33)
                JSON(["Status" => "Failed", "Message" => $Lang["ACC_LOGIN_LONG_USERNAME"]]);

            if (strlen($Data->Password) <= 4)
                JSON(["Status" => "Failed", "Message" => $Lang["ACC_LOGIN_SHORT_PASSWORD"]]);

            if (strlen($Data->Password) >= 33)
                JSON(["Status" => "Failed", "Message" => $Lang["ACC_LOGIN_LONG_PASSWORD"]]);

            if (!preg_match("/^(?![^A-Za-z])(?!.*\.\.)[A-Za-z0-9_.]+(?<![^A-Za-z])$/", $Data->Username))
                JSON(["Status" => "Failed", "Message" => $Lang["ACC_LOGIN_INVALID_USERNAME"]]);

            $Username = $Data->Username;
            $Password = $Data->Password;
            $Session = $Data->Session;
            $LoginTime = time();

            $Account = $App->DB->find('account', ['Username' => $Username])->toArray();

            if (empty($Account))
                JSON(["Status" => "Failed", "Message" => $Lang["ACC_LOGIN_NOT_EXIST_USERNAME"]]);

            if (!password_verify($Password, $Account[0]->Password->__toString()))
                JSON(["Status" => "Failed", "Message" => $Lang["ACC_LOGIN_WRONG_USERNAME_PASSWORD"]]);

            $ID = $Account[0]->_id->__toString();

            $Token = $App->Auth->CreateToken(['_id' => $ID, 'Session' => $Session]); // Fix This

            $App->Auth->SaveToken(['_id' => $ID, 'Session' => $Session, 'Token' => $Token], $App); // And This

            // TODO SendMail
            // TODO Log

            JSON(["Status" => "Success", "Message" => $Lang["SUCCESS"], "Token" => $Token]);
        }

        public static function Logout($App)
        {
            // This Is Safe
            $Token = $_SERVER['HTTP_TOKEN'];

            $App->DB->Delete('account', ['Token' => $Token]); // Fix Me

            JSON(["Status" => "Success", "Message" => $Lang["SUCCESS"]]);
        }
    }
?>
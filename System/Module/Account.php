<?php
    class Account
    {
        /*
         * - 1 = Username Empty
         * - 2 = Password Empty
         * - 3 = Email Empty
         * - 4 = Email Wrong
         * - 5 = Username Short
         * - 6 = Username Long
         * - 7 = Password Short
         * - 8 = Password Long
         * - 9 = Email Long
         * - 10 = Username Invalid
         * - 11 = Username Taken
         * - 12 = Email Taken
         * - 100 = Success
         */

         /* Karaye Anjam Nashode Baraye In Method!
          * - Username Ba Adad Va . - Shoro Nashe!
          * - Username Ba Adad Va . - B Payan Narese!
          * - Username Bejoz Kalamate A-Za-z0-9 Va _ Va . nokhte Faghat betone yek Bar Tekrar she Poshte Sare Ham
          * - EnCrypt Kardane Password
          */

        public static function Register($App)
        {
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

            if (strlen($Data->Email) >= 65)
                JSON(["Status" => "Failed", "Message" => 9]);

            if (!preg_match('/[^A-Za-z0-9]/', $Data->Username))
                JSON(["Status" => "Failed", "Message" => 10]);

//            $AccountID = UNIQUEID!!!!!
            $Username = $Data->Username;
            $Password = $Data->Password;
            $Email = $Data->Email;
            $CreationTime = time();

            $_Username = $App->DB->find('account', ['Username' => $Username])->toArray();

            if (!empty($_Username))
                JSON(["Status" => "Failed", "Message" => 11]);

            $_Email = $App->DB->find('account', ['Email' => $Email])->toArray();

            if (!empty($_Email))
                JSON(["Status" => "Failed", "Message" => 12]);

            $App->DB->Insert('account', ['AccountID' => $AccountID, 'Username' => $Username, 'Password' => $Password, 'Email' => $Email, 'CreationTime' => $CreationTime]);

            JSON(["Status" => "Success", "Message" => 100]);
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

            if (!preg_match('/[^A-Za-z0-9]/', $Data->Username))
                JSON(["Status" => "Failed", "Message" => 8]);

            $Username = $Data->Username;
            $Password = $Data->Password;
            $Session = $Data->Session; // Ino Bayad Ezafe Konim

            $Account = $App->DB->find('account', ['Username' => $Username])->toArray();

            if (empty($Account))
                JSON(["Status" => "Failed", "Message" => 9]);

            if ($Account[0]->Password != $Password)
                JSON(["Status" => "Failed", "Message" => 11]);

            // in _id e khode Mongo DB khobe?? akharin bar didam 20 30 kalame bod! b darde ID mikhore ? Age Mikhore AccountID e Register o var darim
            $Token = $App->Auth->CreateToken(['UserId' => $Account[0]->_id->__toString()]);

            JSON(["Status" => "Success", "Message" => 100, "Token" => $Token]);
        }
    }
?>
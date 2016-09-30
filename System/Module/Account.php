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
            $Password = $Data->Password; // Encrypt Me Later !! why?
            $Email = $Data->Email;

            // Getting Data
            $User = $App->DB->find('account', ['Username' => $Username])->toArray();

            // Username Filter
            if (empty($User)) {
                $App->DB->Insert('account', ['Username' => $Username, 'Password' => $Password, 'Email' => $Email]);

                JSON(["Status" => "Success", "Message" => 100]);
            }

            JSON(["Status" => "Failed", "Message" => 5]);
        }


        /*
         * Result Translate
         * - 1 = Username or Password Incorrect
         * - 100 = Success
         */
        public static function Login($App)
        {

            // Getting Data
            $Data = json_decode(file_get_contents("php://input"));

            // Username Filter
            if (!isset($Data->Username) || empty($Data->Username))
                JSON(["Status" => "Failed", "Message" => 1]);

            // Password Filter
            if (!isset($Data->Password) || empty($Data->Password))
                JSON(["Status" => "Failed", "Message" => 2]);


            // Variables
            $Username = $Data->Username;
            $Password = $Data->Password;

            // Getting Data
            $User = $App->DB->find('account', ['Username' => $Username])->toArray();

            if (empty($User)) {
                JSON(["Status" => "Failed", "Message" => 1]);
            } else {
                self::CreateUserToken($User);
            }

        }


        protected static function CreateUserToken($User)
        {

            // time token is created
            $now = time();

            $expire = $now + 2592000;   // Adding 1 month (3600*24*30)

            // Create Token for Request - OAuth Standard
            $data = [
                // issued at: time token is created
                'iat'  => time(),
                // A unique identifier for token
                'jti'  => base64_encode(mcrypt_create_iv(32)),
                // Issuer Server
                'iss'  => "Biogram",
                // Token Is Not Valid  before "nbf" (not before)
                'nbf'  => $now,
                // Expire Date
                'exp'  => $expire,
                // Data related to the user
                'data' => [
                    'UserId'   => $User[0]->_id->__toString()
                ]
            ];

            $Auth = new Auth();

            // testing encode and decode
            $Token = $Auth->Encode($data);
            $Token = $Auth->Decode($Token);
            var_dump($Token); die;

        }


    }


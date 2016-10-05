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

		 /* Karaye Anjam Nashode Baraye In Method!
		  * - Username Ba Adad Va . - Shoro Nashe!
		  * - Username Ba Adad Va . - B Payan Narese!
		  * - EnCrypt Kardane Password
		  * - Ezafe Kardan Zamane Sakhte Account Va Email e Avaliye e account
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
            $Password = $Data->Password;
            $Email = $Data->Email;

            // Check for Username Duplication
            $User = $App->DB->find('account', ['Username' => $Username])->toArray();

            if (empty($User))
            {
                $App->DB->Insert('account', ['Username' => $Username, 'Password' => $Password, 'Email' => $Email]);

                JSON(["Status" => "Success", "Message" => 100]);
            }

            JSON(["Status" => "Failed", "Message" => 5]);
        }

        /*
         * Result Translate
         * - 1 = Username or Password Incorrect
         * - 2 = Password Empty
         * - 3 = DeviceName Empty
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

            // DeviceName Filter
            if (!isset($Data->DeviceName) || empty($Data->DeviceName))
                JSON(["Status" => "Failed", "Message" => 3]);


            // Variables
            $Username = $Data->Username;
            $Password = $Data->Password;
            $DeviceName = $Data->DeviceName;

            // Getting Data
            $User = $App->DB->find('account', ['Username' => $Username])->toArray();

            if (!empty($User) && $User[0]->Password == $Password) {

                /**
                 * Login Successful!
                 * User can use this generated token to access
                 * protected resources, by providing this token
                 * in future http requests in header !
                 *
                 * Header format must be as follow :
                 * Token:$token_key
                 */

                $RefreshToken = $App->Auth->CreateRefreshToken(['UserId' => $User[0]->_id->__toString(), 'DeviceName' => $DeviceName ], $App);

                $Token = $App->Auth->CreateToken(['UserId' => $User[0]->_id->__toString() ], $App);
                JSON([
                    "Status" => "Success",
                    "Message" => 100,
                    "Data" => [
                        "Token" => $Token,
                        "RefreshToken" => $RefreshToken
                    ]
                ]);

            } else {
                JSON(["Status" => "Failed", "Message" => 1]);
            }
        }


        // JUST FOR TESTING
        public static function UpdateUsername($App){


            // If Token is expired Server replies : JSON("Token is expired!", 403);
            // And Client should call /Authenticate Route with old TOKEN and old REFRESH_TOKEN in header
            // And Get A New Token And Refresh Token
            // And Call This (UpdateUserName) Route Again with new Access Token
            $Token = $App->Auth->GetToken();
            $UserId = $Token->Data->UserId;
            var_dump("Your Token is Valid. userId : \n ");
            print_r($UserId);
            // Then Update accounts table with provided UserId . . .


        }
    }
?>
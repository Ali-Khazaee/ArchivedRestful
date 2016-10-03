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

            if (!empty($User) && $User[0]->Password == $Password) {

                /**
                 * Login Successful!
                 * User can use this generated token to access
                 * protected resources, by providing this token
                 * in future http requests in header !
                 *
                 * Header format must be as follow :
                 * Authorization: Bearer $token_key (OAUTH Standard)
                 */
                $token = $App->Auth->CreateToken(['UserId' => $User[0]->_id->__toString() ], $App);
                JSON([
                    "Status" => "Success",
                    "Message" => 100,
                    "Data" => [
                        "Token" => $token
                    ]
                ]);

            } else {
                JSON(["Status" => "Failed", "Message" => 1]);
            }
        }


        // JUST FOR TESTING
        public static function UpdateUsername($App){

            /*
            * Result Translate
            *  1 = Token Expired, generates new token if expired and
            *  pass it to client, If not expired, continue executing the code
            */
            $App->Auth->RegenerateTokenIfExpired($App);

            /*
             * doing other works :
             * 1 - get request data (new username)
             * 2 - search in "accounts" table with token's UserId
             * 3 - update username
             */
            $Token = $App->Auth->Get();
            $UserId = $Token->data->UserId;
            var_dump("user id is : ". $UserId); die;

        }
    }
?>
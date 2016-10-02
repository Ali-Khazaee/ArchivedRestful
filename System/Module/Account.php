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
            $Password = $Data->Password; // Encrypt Me Later !! why?
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
                $token = self::CreateUserToken($User, $App);
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


        // Just for testing token auth
        public static function UpdateUsername($App)
        {
            // Getting Data
            $Data = json_decode(file_get_contents("php://input"));
            $NewUsername = $Data->NewUsername;

            // get userId from request  header token
            $UserId = self::GetUserIdFromToken($App);

            $App->DB->update('account', ['_id' =>new \MongoDB\BSON\ObjectID($UserId)], ['Username' => $NewUsername]);

        }


        protected static function CreateUserToken($User, $App)
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


            return $App->Auth->Encode($data);

        }



       /*
        * Result Translate
        *  1 = Provided Token is not valid, Try login and get a new token.
        */
        private static function GetUserIdFromToken($App)
        {

            // get token from request header
            $request_token = str_replace('Bearer ', '', $_SERVER['HTTP_AUTHORIZATION']);

            try{

                $decoded_token = $App->Auth->Decode($request_token);
                return $decoded_token->data->UserId;

            } catch(Exception  $e){

                // if can not decode token
                if($e instanceof UnexpectedValueException ){
                    JSON(["Status" => "Failed", "Message" => 1]);
                } else {
                    throw $e;
                }
            }
        }



    }
?>
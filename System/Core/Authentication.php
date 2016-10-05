<?php
    class Auth
    {

		// Get Token Data
		public function GetToken()
		{

            if (!isset($_SERVER['HTTP_TOKEN']) || empty($_SERVER['HTTP_TOKEN']))
                JSON("Empty Token!", 300);

			$Decoded = $this->Decode($_SERVER['HTTP_TOKEN']);

			if (!isset($Decoded->Data))
                JSON("Data Doesn't Exist In Token!", 300);

            // This line is acting as a filter to all requests
            $this->CheckTokenExpire($Decoded);

            return $Decoded;
		}


        // Get Refresh Token Data
        public function GetRefreshToken()
        {

            if (!isset($_SERVER['HTTP_REFRESHTOKEN']) || empty($_SERVER['HTTP_REFRESHTOKEN']))
                JSON("Empty Refresh Token!", 300);

            $Decoded = $this->Decode($_SERVER['HTTP_REFRESHTOKEN']);

            if (!isset($Decoded->Data))
                JSON("Data Doesn't Exist In Refresh Token!", 300);

            return $Decoded;
        }



        // Filter :  Check if Token is Expired
        public function CheckTokenExpire($Data)
        {
            // Check if Token is Expired
            if (isset($Data->Exp) && time() >= $Data->Exp){

                // Token is Expired And Client should update it's tokens (access and refresh)
                // By Calling /Authenticate Route
                // And then, call previous request with new tokens
                JSON("Token is expired!", 403);
            }

        }


        public function Authenticate($App){


            if (!isset($_SERVER['HTTP_TOKEN']) || empty($_SERVER['HTTP_TOKEN']))
                JSON("Empty Token!", 300);

            $Decoded = $this->Decode($_SERVER['HTTP_TOKEN']);

            if (!isset($Decoded->Data))
                JSON("Data Doesn't Exist In Token!", 300);

            $Token = $Decoded;

            $RefreshToken = $this->GetRefreshToken();

            if($Token->Data->UserId != $RefreshToken->Data->UserId)
                JSON("Refresh Token And Token does not match", 300);

            // Search refresh_tokens table if not exist : invalid refresh, login again!
            $OldRefresh = $App->DB->Find('refresh_tokens', ['UserId' => $RefreshToken->Data->UserId, 'DeviceName' => $RefreshToken->Data->DeviceName]);

            if(empty($OldRefresh)){
                JSON("Refresh Token is not valid!", 300);
            }

            // Create new refresh and update database
            $UpdatedRefreshToken = $this->UpdateRefreshToken(['UserId' => $RefreshToken->Data->UserId, 'DeviceName' => $RefreshToken->Data->DeviceName], $App);

            // Create new token
            $NewToken = $this->CreateToken(['UserId' => $Token->Data->UserId]);

            // Access Token And Refresh Token Created Successfully
            // User may use these tokens in future requests
            JSON([
                "Status" => "Successful",
                "Message" => 100,
                "Data" => [
                    "NewToken" => $NewToken,
                    "NewRefreshToken" => $UpdatedRefreshToken
                ]
            ]);

        }

		// Create Token
		public function CreateToken($CustomData)

        {
            // Token Created Time
            $CreateTime = time();


			// Token Expired Time - One Hour
            $ExpireTime = $CreateTime + 100; // TODO: changed to 100 seconds for testing replace to One Hour

            // Token Config
            $Config =
			[
				// Not Valid After
				'Exp'  => $ExpireTime,

                // Custom Data
                'Data' => $CustomData
            ];


			// Create Token
            return $this->Encode($Config);
        }


        // Create New Refresh Token And Update DataBase
        public function UpdateRefreshToken($CustomData, $App)
        {
            $RefreshToken = $this->CreateToken($CustomData);

            $App->DB->Update('refresh_tokens', ['UserId' => $CustomData['UserId'], 'DeviceName'=> $CustomData['DeviceName']], ['RefreshToken' => $RefreshToken]);

            return $RefreshToken;

        }



        // Create New Refresh Token And Insert into DataBase
        public function CreateRefreshToken($CustomData, $App)
        {
            $RefreshToken = $this->CreateToken($CustomData);

            $App->DB->Insert('refresh_tokens', ['UserId' => $CustomData['UserId'], 'DeviceName'=> $CustomData['DeviceName'], 'RefreshToken' => $RefreshToken ]);

            return $RefreshToken;
        }


		// Encode Data Into Token
        public function Encode($Data)
        {
            // Encode Data
            $Segments[] = $this->Base64Encode(json_encode($Data));

            // Sign Data With Key
            $Signature = $this->Sign($Segments[0]);

            // Insert Sign
            $Segments[] = $this->Base64Encode($Signature);

            // Return Encoded Data
            return implode('.', $Segments);
        }

        // Base64 Encode 
        public function Base64Encode($input)
        {
            return str_replace('=', '', strtr(base64_encode($input), '+/', '-_'));
        }

        // Sign The Token
        public function Sign($Message)
        {
            $Signature = '';
            $Success = openssl_sign($Message, $Signature, SSL_PRIVATE_KEY, 'SHA256');

            if ($Success)
                return $Signature;

            JSON("OpenSSL Unable To Sign!", 300);
        }

        // Decode Token Into Data
        public function Decode($Data)
        {
            $Segments = explode('.', $Data);

            // Count Segment
            if (count($Segments) != 2)
                JSON("Wrong Token Format!", 300);

            // List Data
            $Content = $Segments[0];
            $Crypt = $Segments[1];

            // Decode Content
            if (($ContentData = json_decode($this->Base64Decode($Content))) === NULL)
                JSON("Invalid Token Content!", 300);

            // Decode Signature
            $Signature = $this->Base64Decode($Crypt);

            // Verify Data
            if ($this->Verify($Content, $Signature))
                JSON("Invalid Token Signature Verification Failed!", 300);

            // Return Data As JSON
            return $ContentData;
        }

        // Base64 Decode
        public function Base64Decode($Message)
        {
            $Remainder = strlen($Message) % 4;

            if ($Remainder)
            {
                $PadLen = 4 - $Remainder;
                $Message .= str_repeat('=', $PadLen);
            }

            return base64_decode(strtr($Message, '-_', '+/'));
        }

        // Verify Data And Signature
        private function Verify($Message, $Signature)
        {
            $Success = openssl_verify($Message, $Signature, SSL_PUBLIC_KEY, 'SHA256');

            if ($Success)
                return false;

            JSON("OpenSSL Unable To Verify Data: " . openssl_error_string(), 300);
        }
    }
?>
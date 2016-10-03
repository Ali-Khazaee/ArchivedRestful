<?php
    class Auth
    {
        // Get Token
        public function GetToken()
        {
            if (!isset($_SERVER['HTTP_TOKEN']) || empty($_SERVER['HTTP_TOKEN']))
                JSON("Empty Token!", 300);

            return $_SERVER['HTTP_TOKEN'];
        }

        // Get Token Data
        public function Get()
        {
            $Decoded = $this->Decode($this->GetToken());

            if (isset($Decoded))
                return $Decoded;

            JSON("Data Doesn't Exist In Token!", 300);
        }



        /*
        * IF Old Token is Expired : Generate new Token from Old Token with SAME UserId !
        * ELSE : Continue the Code Execution.
        * Result Translate
        *  1 = Token Expired
        */
        public function RegenerateTokenIfExpired($App)
        {
            // Get Old Token Data
            $Data = $this->Get();

            // Check if Token is Expired
            if (isset($Data->exp) && time() >= $Data->exp){

                // Create New Token with the same UserId
                $NewToken = $this->CreateToken(['UserId' => $Data->data->UserId], $App);

                JSON([
                    "Status" => "Failed",
                    "Message" => 1,
                    "Data" => [
                        "NewToken" => $NewToken
                    ]
                ]);
            }

        }

        // Create Token
        public function CreateToken($CustomData, $App)
        {
            // Token Created Time
            $CreateTime = time();

            // Token Expired Time - One Hour
            $ExpireTime = $CreateTime + 3600;

            // Token Config
            $Config =
            [
                // Is User
                'iss'  => "Biogram",
                // Not Valid After
                'exp'  => $ExpireTime,
                // Not Valid Before
                'nbf'  => $CreateTime,
                // Created Time
                'iat'  => $CreateTime,
                // Unique Identify
                'jti'  => base64_encode(mcrypt_create_iv(32)),
                // Custom Data
                'data' => $CustomData
            ];

            // Create Token
            return $App->Auth->Encode($Config);
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
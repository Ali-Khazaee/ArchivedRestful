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
		public function Get($Data)
		{
			$Decoded = $this->Decode($this->GetToken());

			if (isset($Decoded->$Data)
                return $Decoded->$Data;

			JSON("Data Doesn't Exist In Token!", 300);
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
			// Custom Header - UseLess
            $Header = array('type' => 'Bio');

			// Encode Header
            $Segments[] = $this->Base64Encode(json_encode($Header));

			// Encode Data
            $Segments[] = $this->Base64Encode(json_encode($Data));

			// Prepare Segments
            $Signing = implode('.', $Segments);

			// Sign Data With Key
            $Signature = $this->Sign($Signing);

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

			throw new Exception("OpenSSL Unable To Sign!");
        }

        // Decode Token Into Data
        public function Decode($Data)
        {
            $Segments = explode('.', $Data);

			// Count Segment
            if (count($Segments) != 3)
                throw new Exception('Wrong Token Format!');

			// List Data
			$Header = $Segments[0];
			$Content = $Segments[1];
			$Crypt = $Segments[2];

			// Header Data - UseLess
            if (empty($this->Base64Decode($Header)))
                throw new Exception('Invalid Token Header!');

			// Decode Content
            if (($ContentData = json_decode($this->Base64Decode($Content))) === NULL)
                throw new Exception('Invalid Token Content!');

			// Decode Signature
            $Signature = $this->Base64Decode($Crypt);

            // Verify Data
            if ($this->Verify("$Header.$Content", $Signature))
                throw new Exception('Invalid Token Signature Verification Failed!');

            // Token Expire Time
            if (isset($ContentData->exp) && time() >= $ContentData->exp)
                throw new Exception('Token Expired!');

			// Return Data As JSON
            return $ContentData;
        }

		// Base64 Decode
        public function Base64Decode($input)
        {
            $remainder = strlen($input) % 4;
            if ($remainder) {
                $padlen = 4 - $remainder;
                $input .= str_repeat('=', $padlen);
            }
            return base64_decode(strtr($input, '-_', '+/'));
        }

		// Verify Data And Signature
        private static function Verify($Message, $Signature)
        {
            $Success = openssl_verify($Message, $Signature, SSL_PUBLIC_KEY, 'SHA256');

			if ($Success)
				return false;

			throw new Exception("OpenSSL Unable To Verify Data: " . openssl_error_string());
        }
    }
?>
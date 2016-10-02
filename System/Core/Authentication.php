<?php
    class Auth
    {
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

        // Decode Token string into PHP object
        public static function Decode($data)
        {
            $timestamp = time();

            $parts = explode('.', $data);
            if (count($parts) != 3) {
                throw new UnexpectedValueException('wrong token format!');
            }

            list($headb64, $bodyb64, $cryptob64) = $parts;

            if (null === ($header = json_decode(static::Base64Decode($headb64)))) {
                throw new UnexpectedValueException('Invalid token header');
            }

            if (null === $body = json_decode(static::Base64Decode($bodyb64))) {
                throw new UnexpectedValueException('Invalid token body');
            }

            $signature = static::Base64Decode($cryptob64);



            // Check if the Token signature is invalid
            if (!static::verify("$headb64.$bodyb64", $signature)) {
                throw new UnexpectedValueException('Invalid Token: Signature verification failed');
            }


            // Check if token has expired.
            if (isset($body->exp) && ($timestamp) >= $body->exp){
                throw new UnexpectedValueException('Expired token');
            }

            return $body;
        }



        // Encode PHP object into Token string
        public static function Encode($data)
        {

            $header = array('typ' => 'JWT', 'alg' => 'SHA256');

            $segments = array();
            $segments[] = static::Base64Encode(json_encode($header));
            $segments[] = static::Base64Encode(json_encode($data));

            $signing_input = implode('.', $segments);

            $signature = static::sign($signing_input);

            $segments[] = static::Base64Encode($signature);

            return implode('.', $segments);
        }



        // Sign the token header and body with a given key
        public static function sign($msg)
        {

            $signature_output = '';
            $success = openssl_sign($msg, $signature_output, SSL_PRIVATE_KEY, 'SHA256');

            if (!$success) {
                throw new DomainException("OpenSSL unable to sign data");
            } else {
                return $signature_output;
            }

        }




        private static function verify($msg, $signature)
        {

            $success = openssl_verify($msg, $signature, SSL_PUBLIC_KEY, 'SHA256');
            if (!$success) {
                throw new DomainException("OpenSSL unable to verify data: " . openssl_error_string());
            } else {
                return $success;
            }

        }




        // Decode a string with URL-safe Base64
        public static function Base64Decode($input)
        {
            $remainder = strlen($input) % 4;
            if ($remainder) {
                $padlen = 4 - $remainder;
                $input .= str_repeat('=', $padlen);
            }
            return base64_decode(strtr($input, '-_', '+/'));
        }


        //  Encode a string with URL-safe Base64
        public static function Base64Encode($input)
        {
            return str_replace('=', '', strtr(base64_encode($input), '+/', '-_'));
        }



    }
?>
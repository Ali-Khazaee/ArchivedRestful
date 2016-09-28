<?php


    // inspired from Library :
    // https://github.com/firebase/php-jwt/blob/master/src/JWT.php

    class Token
    {

        // Decode Token string into PHP object
        public static function Decode($data, $secret)
        {
            $timestamp = time();

            if (empty($secret)) {
                throw new InvalidArgumentException('Key may not be empty');
            }

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
            if (!static::verify("$headb64.$bodyb64", $signature, $secret)) {
                throw new UnexpectedValueException('Invalid Token: Signature verification failed');
            }


            // Check if token has expired.
            if (isset($body->exp) && ($timestamp) >= $body->exp){
                throw new UnexpectedValueException('Expired token');
            }

            return $body;
        }



        // Encode PHP object into Token string
        public static function Encode($data, $secret)
        {

            $header = array('typ' => 'JWT', 'alg' => 'SHA256');

            $segments = array();
            $segments[] = static::Base64Encode(json_encode($header));
            $segments[] = static::Base64Encode(json_encode($data));

            $signing_input = implode('.', $segments);

            $signature = static::sign($signing_input, $secret);

            $segments[] = static::Base64Encode($signature);

            return implode('.', $segments);
        }



        // Sign the token header and body with a given key
        public static function sign($msg, $secret)
        {

            $signature = '';
            $success = openssl_sign($msg, $signature, $secret, 'SHA256');
            if (!$success) {
                throw new DomainException("OpenSSL unable to sign data");
            } else {
                return $signature;
            }

        }




        private static function verify($msg, $signature, $secret)
        {

            $success = openssl_verify($msg, $signature, $secret, 'SHA256');
            if (!$success) {
                throw new DomainException("OpenSSL unable to verify data: " . openssl_error_string());
            } else {
                return $signature;
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
<?php
    /* Value Translate
     * 1 - OpenSSL Unable To Verify Data
     * 2 - Invalid Token Signature Verification Failed
     * 3 - Invalid Token Content
     * 4 - Wrong Token Format
     * 5 - OpenSSL Unable To Sign
     * 6 - Token Is Expired
     * 7 - Data Doesn't Exist In Token
     * 8 - Empty Token
     */

    class Auth
    {
        // Check Token
        public function CheckToken()
        {
            // Header Shouldn't Be Empty
            if (!isset($_SERVER['HTTP_TOKEN']) || empty($_SERVER['HTTP_TOKEN']))
                JSON(["Status" => "Failed", "Message" => 8], 401);

            // Decode Token
            $Decode = $this->Decode($_SERVER['HTTP_TOKEN']);

            // Token Data Shouldn't Be Empty
            if (!isset($Decode->Data))
                JSON(["Status" => "Failed", "Message" => 7], 401);

            // Check Expired
            if (isset($Decode->Exp) && time() >= $Decode->Exp)
                JSON(["Status" => "Failed", "Message" => 6], 401);
        }

        // Save Token to DataBase
        public function SaveToken($Data, $App)
        {
            $App->DB->Insert('tokens', ['UserId' => $Data['UserId'], 'Session' => $Data['Session'], 'Token' => $Data['Token']], false);
        }

        // Create Token
        public function CreateToken($CustomData)
        {
            // Token Expired Time - 60 Days
            $ExpireTime = time() + 5184000;

            // Token Config
            $Config =
            [
                // Not Valid After
                'Exp' => $ExpireTime,
                // Custom Data
                'Data' => $CustomData
            ];

            // Create Token
            return $this->Encode($Config);
        }

        // Encode Data Into Token
        private function Encode($Data)
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
        private function Base64Encode($input)
        {
            return str_replace('=', '', strtr(base64_encode($input), '+/', '-_'));
        }

        // Sign The Token
        private function Sign($Message)
        {
            $Signature = '';
            $Success = openssl_sign($Message, $Signature, SSL_PRIVATE_KEY, 'SHA256');

            if ($Success)
                return $Signature;

            JSON(["Status" => "Failed", "Message" => 5], 401);
        }

        // Decode Token Into Data
        public function Decode($Data)
        {
            // Explode Segments By .
            $Segments = explode('.', $Data);

            // Count Segment
            if (count($Segments) != 2)
                JSON(["Status" => "Failed", "Message" => 4], 401);

            // List Data
            $Content = $Segments[0];
            $Crypt = $Segments[1];

            // Decode Content
            if (($ContentData = json_decode($this->Base64Decode($Content))) === NULL)
                JSON(["Status" => "Failed", "Message" => 3], 401);

            // Decode Signature
            $Signature = $this->Base64Decode($Crypt);

            // Verify Data
            if ($this->Verify($Content, $Signature))
                JSON(["Status" => "Failed", "Message" => 2], 401);

            // Return Data As JSON
            return $ContentData;
        }

        // Base64 Decode
        private function Base64Decode($Message)
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

            JSON(["Status" => "Failed", "Message" => 1], 401);
        }
    }
?>
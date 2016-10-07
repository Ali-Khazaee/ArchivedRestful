<?php
    class Auth
    {
        public function CheckToken()
        {
            if (!isset($_SERVER['HTTP_TOKEN']) || empty($_SERVER['HTTP_TOKEN']))
                JSON(["Status" => "Failed", "Message" => $Lang["AUTH_ERROR_EMPTY_TOKEN"]], 401);

            $Decode = $this->Decode($_SERVER['HTTP_TOKEN']);

            if (!isset($Decode->Data))
                JSON(["Status" => "Failed", "Message" => $Lang["AUTH_ERROR_EMPTY_DATA"]], 401);

            if (!isset($Decode->Exp) || time() <= $Decode->Exp)
                JSON(["Status" => "Failed", "Message" => $Lang["AUTH_ERROR_EXPIRED_TOKEN"]], 401);
        }

        public function SaveToken($Data, $App) // Check This Function!
        {
            // Delete Similar Session
            $App->DB->Delete('account', ['_id' => $Data['ID'], 'Session' => $Data['Token']]);

            $App->DB->Insert('account', ['_id' => $Data['ID'], 'Session' => (['Name' => $Data['Name'], 'Token' => $Data['Token'], 'CreationTime' => time()])]);
        }

        public function CreateToken($CustomData)
        {
            // Token Expired Time - 180 Days
            $ExpireTime = time() + 15552000;

            $Config =
            [
                'Exp'  => $ExpireTime, // Not Valid After
                'Data' => $CustomData  // Custom Data
            ];

            return $this->Encode($Config);
        }

        private function Encode($Data)
        {
            $Segments[] = $this->Base64Encode(json_encode($Data));

            $Signature = $this->Sign($Segments[0]);

            $Segments[] = $this->Base64Encode($Signature);

            return implode('.', $Segments);
        }

        private function Base64Encode($input)
        {
            return str_replace('=', '', strtr(base64_encode($input), '+/', '-_'));
        }

        private function Sign($Message)
        {
            $Signature = '';
            $Success = openssl_sign($Message, $Signature, SSL_PRIVATE_KEY, 'SHA256');

            if ($Success)
                return $Signature;

            JSON(["Status" => "Failed", "Message" => $Lang["AUTH_ERROR_CANNOT_SIGN"]], 401);
        }

        public function Decode($Data)
        {
            $Segments = explode('.', $Data);

            if (count($Segments) != 2)
                JSON(["Status" => "Failed", "Message" => $Lang["AUTH_ERROR_WRONG_SEGMENT_COUNT"]], 401);

            if (($ContentData = json_decode($this->Base64Decode($Segments[0]))) === NULL)
                JSON(["Status" => "Failed", "Message" => $Lang["AUTH_ERROR_EMPTY_CONTENT"]], 401);

            $Signature = $this->Base64Decode($Segments[1]);

            if ($this->Verify($Segments[0], $Signature))
                JSON(["Status" => "Failed", "Message" => $Lang["AUTH_ERROR_VERIFY_FAILED"]], 401);

            return $ContentData;
        }

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

        private function Verify($Message, $Signature)
        {
            $Success = openssl_verify($Message, $Signature, SSL_PUBLIC_KEY, 'SHA256');

            if ($Success)
                return false;

            JSON(["Status" => "Failed", "Message" => $Lang["AUTH_ERROR_OPENSSL_VERIFY_FAILED"]], 401);
        }
    }
?>
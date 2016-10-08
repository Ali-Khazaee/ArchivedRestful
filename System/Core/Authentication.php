<?php
    // Don't Allow Access Directly
    if (!defined("ROOT")) { exit(); }

    class Auth
    {
        public function CheckToken()
        {
            if (!isset($_SERVER['HTTP_TOKEN']) || empty($_SERVER['HTTP_TOKEN']))
                JSON(["Status" => "Failed", "Message" => $Lang["AUTH_EMPTY_TOKEN"]], 401);

            $Decode = $this->Decode($_SERVER['HTTP_TOKEN']);

            if (!isset($Decode->ID))
                JSON(["Status" => "Failed", "Message" => $Lang["AUTH_EMPTY_DATA"]], 401);

            if (!isset($Decode->EXP) || time() >= $Decode->EXP)
                JSON(["Status" => "Failed", "Message" => $Lang["AUTH_EXPIRED_TOKEN"]], 401);
        }

        public function CreateToken($CustomData)
        {
            $ID = $CustomData["ID"];
            $ExpireTime = time() + 15552000; // 180 Days

            unset($CustomData["ID"]);

            $Config =
            [
                'ID'   => $ID,
                'EXP'  => $ExpireTime,
                'DATA' => $CustomData
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

            JSON(["Status" => "Failed", "Message" => $Lang["AUTH_CANNOT_SIGN"]], 401);
        }

        public function Decode($Data)
        {
            $Segments = explode('.', $Data);

            if (count($Segments) != 2)
                JSON(["Status" => "Failed", "Message" => $Lang["AUTH_WRONG_SEGMENT_COUNT"]], 401);

            if (($ContentData = json_decode($this->Base64Decode($Segments[0]))) === NULL)
                JSON(["Status" => "Failed", "Message" => $Lang["AUTH_EMPTY_CONTENT"]], 401);

            $Signature = $this->Base64Decode($Segments[1]);

            if ($this->Verify($Segments[0], $Signature))
                JSON(["Status" => "Failed", "Message" => $Lang["AUTH_VERIFY_FAILED"]], 401);

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

            JSON(["Status" => "Failed", "Message" => $Lang["AUTH_OPENSSL_VERIFY_FAILED"]], 401);
        }
    }
?>
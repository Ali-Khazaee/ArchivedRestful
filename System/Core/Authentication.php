<?php
    if (!defined("ROOT")) { exit(); }

    class Auth
    {
        public $ID = "NULL";

        public function CheckToken()
        {
            if (!isset($_SERVER['HTTP_TOKEN']) || empty($_SERVER['HTTP_TOKEN']))
                JSON(["Message" => 2002]);

            $Decode = $this->Decode($_SERVER['HTTP_TOKEN']);

            if (!isset($Decode->ID))
                JSON(["Message" => 2003]);

            if (!isset($Decode->EXP) || time() >= $Decode->EXP)
                JSON(["Message" => 2004]);

            $this->ID = $Decode->ID;
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

            JSON(["Message" => 2005]);
        }

        public function Decode($Data)
        {
            $Segments = explode('.', $Data);

            if (count($Segments) != 2)
                JSON(["Message" => 2006]);

            if (($Content = json_decode($this->Base64Decode($Segments[0]))) === NULL)
                JSON(["Message" => 2007]);

            $Signature = $this->Base64Decode($Segments[1]);

            if ($this->Verify($Segments[0], $Signature))
                JSON(["Message" => 2008]);

            return $Content;
        }

        private function Base64Decode($Message)
        {
            $Remainder = strlen($Message) % 4;

            if ($Remainder)
            {
                $PadLength = 4 - $Remainder;
                $Message .= str_repeat('=', $PadLength);
            }

            return base64_decode(strtr($Message, '-_', '+/'));
        }

        private function Verify($Message, $Signature)
        {
            $Success = openssl_verify($Message, $Signature, SSL_PUBLIC_KEY, 'SHA256');

            if ($Success)
                return false;

            return true;
        }
    }
?>
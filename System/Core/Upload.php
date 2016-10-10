<?php
    // Don't Allow Access Directly
    if (!defined("ROOT")) { exit(); }

    class Upload
    {
        public function DoUpload()
        {
            if(!isset($_FILES['UploadFile']))
                JSON("Upload Nashode File"); // @TODO Fix Me

            $FileName    = $_FILES['UploadFile']['name'];
            $FileSize    = $_FILES['UploadFile']['size'];
            $FileTemp    = $_FILES['UploadFile']['tmp_name'];
            $FileType    = $_FILES['UploadFile']['type'];                   // @TODO Check Me Later!!
            $FileMime    = explode('.', $FileName);
            $FileFormat  = strtolower(end($FileMime));
            $AllowFormat = array("jpeg", "jpg", "png");                     // @TODO Add More Formats

            if (!in_array($FileFormat, $AllowFormat))
                JSON("Not Allowed"); // @TODO Fix Me

            if ($FileSize > 2097152)
                JSON("Upload Sizesh Ziade"); // @TODO Fix Me

            // @TODO FILE NAME
            $EncodeFileName = "ali.png";

            $Channel = curl_init();
            curl_setopt($Channel, CURLOPT_URL, $this->GetRandomServer());
            curl_setopt($Channel, CURLOPT_HEADER, false);
            curl_setopt($Channel, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($Channel, CURLOPT_POSTFIELDS, ["ACTION" => "UPLOAD", "TOKEN" => "Access", "FILE" => new CurlFile($FileTemp, $FileType), "FILENAME" => $EncodeFileName]);
            $Result = curl_exec($Channel);
            curl_close($Channel);

            var_dump($Result);
            // @TODO Log
            // @TODO Save To DB
        }

        private function GetRandomServer() // @TODO Fix Me
        {
            return "http://127.0.0.1/1/Server/index.php";
        }
    }
?>
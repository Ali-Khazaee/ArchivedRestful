<?php
    if (!defined("ROOT")) { exit(); }

    class Upload
    {
        protected $Servers = [
          'http://biogram.dev/Server1/index.php',
          'http://biogram.dev/Server2/index.php',
        ];

        public function DoUpload($UserId)
        {
            if(!isset($_FILES['UploadFile']) || empty($_FILES['UploadFile']))
                JSON(["Status" => "Failed", "Message" => Lang('UPLOAD_EMPTY_FILE')]);

            $FileName    = $_FILES['UploadFile']['name'];
            $FileSize    = $_FILES['UploadFile']['size'];
            $FileTemp    = $_FILES['UploadFile']['tmp_name'];
            $FileType    = $_FILES['UploadFile']['type'];      // @TODO Check Me Later!!
            $FileFormat = strtolower(pathinfo($FileName, PATHINFO_EXTENSION));
            $AllowFormat = array("jpeg", "jpg", "png");                     // @TODO Add More Formats

            if (!in_array($FileFormat, $AllowFormat))
                JSON(["Status" => "Failed", "Message" => Lang('UPLOAD_NOT_ALLOWED_FORMAT')]);

            if ($FileSize > 2097152)
                JSON(["Status" => "Failed", "Message" => Lang('UPLOAD_MAX_SIZE_LIMIT')]);

            // Create File Name
            $EncodeFileName = $this->MakeFileName($UserId, $FileFormat);

            $Server = $this->GetBestServer();
            $Token = $this->GetServerToken($Server);

            $Channel = curl_init();
            curl_setopt($Channel, CURLOPT_URL, $Server);
            curl_setopt($Channel, CURLOPT_HEADER, false);
            curl_setopt($Channel, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($Channel, CURLOPT_POSTFIELDS, ["ACTION" => "UPLOAD", "TOKEN" => $Token, "FILE" => new CurlFile($FileTemp, $FileType), "FILENAME" => $EncodeFileName]);
            $Result = curl_exec($Channel);
            curl_close($Channel);

            return $Result;
        }

        private function GetBestServer()
        {
            $Result = array();
            foreach ($this->Servers as $Server ) {
                $Token = $this->GetServerToken($Server);
                $Channel = curl_init();
                curl_setopt($Channel, CURLOPT_URL, $Server);
                curl_setopt($Channel, CURLOPT_HEADER, false);
                curl_setopt($Channel, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($Channel, CURLOPT_POSTFIELDS, ["ACTION" => "HARD_SPACE", "TOKEN" => $Token]);
                $Result[$Server] = curl_exec($Channel);
                curl_close($Channel);
            }

            $BestServer = array_keys($Result, max($Result));
            return $BestServer[0];
        }

        public function makeFileName($UserId, $FileFormat){
            $randomString = uniqid();
            $Name = $UserId . '_' . $FileFormat . '_' . $randomString . '.' . $FileFormat;
            return $Name;
        }

        public function GetServerToken($Server){
            switch ($Server) {
                case 'http://biogram.dev/Server1/index.php': return 'server1RandomToken'; break;
                case 'http://biogram.dev/Server2/index.php': return 'server2RandomToken'; break;
            }
        }

    }
?>
<?php
    // Don't Allow Access Directly
    if (!defined("ROOT")) { exit(); }

    class Upload
    {
        public function __construct()
        {
            
        }
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        

//        protected $FileInfo;

//        public function __construct(){

                // Collect file info

//                    $_FILES[$File]['tmp_name'],
//                    $_FILES[$File]['name']
//        }

        public function UploadFile(){
//            if (!isset($_FILES[$File])) {
//                JSON(["Status" => "Failed", "Message" => Lang("UPLOAD_EMPTY_FILE")]);
//            }

            var_dump($_POST); die;
        }


        public function FileInfo($fullPathName){
            $this->FileInfo = new SplFileInfo($fullPathName);
        }


        public function GetMimetype()
        {
            if (isset($this->mimetype) === false) {
                $finfo = new finfo(FILEINFO_MIME);
                $mimetype = $finfo->file($this->getPathname());
                $mimetypeParts = preg_split('/\s*[;,]\s*/', $mimetype);
                $this->mimetype = strtolower($mimetypeParts[0]);
                unset($finfo);
            }

            return $this->mimetype;
        }



//        public function isUploadedFile()
//        {
//            return is_uploaded_file($this->getPathname());
//        }



        // Make Directory For Upload
        public function Make_Directory()
        {
            $Date = date('Y,m,d');
            $Parts = explode(',',$Date);
            $Year = $Parts[0];
            $Month = $Parts[1];
            $Day = $Parts[2];
            $Directory = $Year . DIRECTORY_SEPARATOR . $Month . DIRECTORY_SEPARATOR . $Day . DIRECTORY_SEPARATOR;
            return $Directory;
        }

//        public function Upload($File)
//        {
//            $Directory = $this->Make_Directory();
//        }



    }
?>
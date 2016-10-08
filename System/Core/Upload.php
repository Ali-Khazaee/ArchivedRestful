<?php
    // Don't Allow Access Directly
    if (!defined("ROOT")) { exit(); }

    class Upload
    {
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

        public function Upload($File)
        {
            $Directory = $this->Make_Directory();
        }
    }
?>
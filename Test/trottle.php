<?php
    //    session_start();
    //    if (isset($_SESSION['LAST_CALL'])) {
    //        $last = strtotime($_SESSION['LAST_CALL']);
    //        $curr = strtotime(date("Y-m-d h:i:s"));
    //        $sec =  abs($last - $curr);
    //        if ($sec <= 2) {
    //            $data = 'Rate Limit Exceeded';  // rate limit
    //            header('Content-Type: application/json');
    //            die (json_encode($data));
    //        }
    //    }
    //    $_SESSION['LAST_CALL'] = date("Y-m-d h:i:s");
    //
    //    // normal usage
    //    $data = "Data Returned from API";
    //    header('Content-Type: application/json');
    //    die(json_encode($data));
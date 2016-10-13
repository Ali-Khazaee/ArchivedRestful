<?php
    // Root Path And Key Access
    define("ROOT", dirname(__FILE__) . DIRECTORY_SEPARATOR, true);

    // Display Error Reporting
    ini_set('display_errors', 1);

    // Level Error Reporting
    ini_set('error_reporting', E_ALL);

    // Level Error Reporting
    error_reporting(E_ALL);

    // Set TimeZone ( Asia / Tehran )
    date_default_timezone_set("Asia/Tehran");

    function JSON($Message, $Code = 200)
    {
        // Clear Headers
        header_remove();

        // Set HTTP Code
        http_response_code($Code);

        // Set Content Type
        header('Content-Type: application/json');

        // Return The Encoded JSON
        exit(json_encode($Message));
    }

    if (!isset($_POST["TOKEN"]) || $_POST["TOKEN"] != "server1RandomToken") // @TODO Change Me Later
        JSON("no access"); // @TODO add me in language, will need it for clients

    if (!isset($_POST["ACTION"]))
        JSON("no route"); // @TODO add me in language, will need it for clients

    switch ($_POST["ACTION"])
    {
        case "UPLOAD":
        {
            $Date = date('Y,m,d');
            $Parts = explode(',', $Date);
            $Directory = $Parts[0] . DIRECTORY_SEPARATOR . $Parts[1] . DIRECTORY_SEPARATOR . $Parts[2] . DIRECTORY_SEPARATOR;

            // Create Directory
            if (!file_exists(ROOT . $Directory))
                mkdir(ROOT . $Directory, 0777, true);

            // Create File
            if(move_uploaded_file($_FILES["FILE"]["tmp_name"], ROOT . $Directory . $_POST["FILENAME"])){
                JSON(["Status" => "Success", "Data" => [
                    'ImagePath' => $Directory . $_POST["FILENAME"],
                    'ServerId' => 1
                ]]);
            } else {
                JSON(["Status" => "Failed",'Message' => 'error'],500); // TODO : add to language
            }
        }
        break;
        case "DELETE":
        {
            // @TODO Fix Me
        }
        break;
        case "DELETE_UPLOAD":
        {
            // @TODO Fix Me
        }
        break;
        case "HARD_SPACE":
        {
            echo disk_free_space("/");
        }
        break;
    }
?>
<?php
    if (!defined("ROOT")) { exit(); }

    function SignIn($App)
    {
        $Username = strtolower($_POST["Username"]);
        $Password = $_POST["Password"];
        $Session = $_POST["Session"];

        if (!isset($Username) || empty($Username))
            JSON(["Status" => "Failed", "Message" => Lang("SIGNIN_USERNAME_EMPTY")]);

        if (!isset($Password) || empty($Password))
            JSON(["Status" => "Failed", "Message" => Lang("SIGNIN_PASSWORD_EMPTY")]);

        if (strlen($Username) <= 2)
            JSON(["Status" => "Failed", "Message" => Lang("SIGNIN_USERNAME_SHORT")]);

        if (strlen($Username) >= 33)
            JSON(["Status" => "Failed", "Message" => Lang("SIGNIN_USERNAME_LONG")]);

        if (strlen($Password) <= 4)
            JSON(["Status" => "Failed", "Message" => Lang("SIGNIN_PASSWORD_SHORT")]);

        if (strlen($Password) >= 33)
            JSON(["Status" => "Failed", "Message" => Lang("SIGNIN_PASSWORD_LONG")]);

        if (!preg_match("/^(?![^A-Za-z])(?!.*\.\.)[A-Za-z0-9_.]+(?<![^A-Za-z])$/", $Username))
            JSON(["Status" => "Failed", "Message" => Lang("SIGNIN_USERNAME_INVALID")]);

        $Account = $App->DB->find('account', ['Username' => $Username])->toArray();

        if (empty($Account))
            JSON(["Status" => "Failed", "Message" => Lang("SIGNIN_USERNAME_NOT_EXIST")]);

        if (!password_verify($Password, $Account[0]->Password))
            JSON(["Status" => "Failed", "Message" => Lang("SIGNIN_DATA_WRONG")]);

        if (!isset($Session) || empty($Session))
            $Session = "Unknown - " . $_SERVER['REMOTE_ADDR'];
        else
            $Session .= " - " . $_SERVER['REMOTE_ADDR'];

        $ID = $Account[0]->_id->__toString();
        $Token = $App->Auth->CreateToken(["ID" => $ID]);

        $App->DB->Update('account', ['_id' => new MongoDB\BSON\ObjectID($ID)], ['$push' => ['Session' => ['Name' => $Session, 'Token' => $Token, 'CreatedTime' => time()]]]);

        $App->Logger->Create('SignIn', ['UserID' => $ID]);

        JSON(["Status" => "Success", "Message" => Lang("GEN_SUCCESS"), "Token" => $Token]);
    }

    function SignUp($App)
    {
        $Username = strtolower($_POST["Username"]);
        $Password = $_POST["Password"];
        $Email = $_POST["Email"];

        if (!isset($Username) || empty($Username))
            JSON(["Status" => "Failed", "Message" => Lang("GEN_EMPTY_USERNAME")]);

        if (!isset($Password) || empty($Password))
            JSON(["Status" => "Failed", "Message" => Lang("GEN_EMPTY_PASSWORD")]);

        if (!isset($Email) || empty($Email))
            JSON(["Status" => "Failed", "Message" => Lang("REGISTER_EMPTY_EMAIL")]);

        if (!filter_var($Email, FILTER_VALIDATE_EMAIL))
            JSON(["Status" => "Failed", "Message" => Lang("REGISTER_INVALID_EMAIL")]);

        if (strlen($Username) <= 2)
            JSON(["Status" => "Failed", "Message" => Lang("GEN_SHORT_USERNAME")]);

        if (strlen($Username) >= 33)
            JSON(["Status" => "Failed", "Message" => Lang("GEN_LONG_USERNAME")]);

        if (strlen($Password) <= 4)
            JSON(["Status" => "Failed", "Message" => Lang("GEN_SHORT_PASSWORD")]);

        if (strlen($Password) >= 33)
            JSON(["Status" => "Failed", "Message" => Lang("GEN_LONG_PASSWORD")]);

        if (strlen($Email) >= 65)
            JSON(["Status" => "Failed", "Message" => Lang("REGISTER_LONG_EMAIL")]);

        if (!preg_match("/^(?![^A-Za-z])(?!.*\.\.)[A-Za-z0-9_.]+(?<![^A-Za-z])$/", $Username))
            JSON(["Status" => "Failed", "Message" => Lang("GEN_INVALID_USERNAME")]);

        $Username = $Username;
        $Password = password_hash($Password, PASSWORD_BCRYPT);
        $Email = $Email;
        $CreationTime = time();

        $_Username = $App->DB->find('account', ['Username' => $Username])->toArray();

        if (!empty($_Username))
            JSON(["Status" => "Failed", "Message" => Lang("REGISTER_ALREADY_EXIST_USERNAME")]);

        $_Email = $App->DB->find('account', ['Email' => $Email])->toArray();

        if (!empty($_Email))
            JSON(["Status" => "Failed", "Message" => Lang("REGISTER_ALREADY_EMAIL")]);

        $App->DB->Insert('account', ['Username' => $Username, 'Password' => $Password, 'Email' => $Email, 'CreationTime' => $CreationTime, 'LastOnlineTime' => $CreationTime]);

        // Send Email
        $User = $App->DB->Find('account', ['Username' => $Username])->toArray();
        $Email = $User[0]->Email;
        $Sub = "Account Register";
        $Msg = " Hello Dear ..... Thank you for ur choice"; // @TODO: fix message
        _Mail($Email, $Sub, $Msg);

        // Create Log
        $Account = $App->DB->find('account', ['Username' => $Username])->toArray();
        $UserID = $Account[0]->_id->__toString();
        $App->SetLog->Create('Register', ['UserID' => $UserID]);

        JSON(["Status" => "Success", "Message" => Lang("GEN_SUCCESS")]);
    }

    function SignOut($App)
    {
        $Token = $_SERVER['HTTP_TOKEN'];
        $Decode = $App->Auth->Decode($Token);
        $ID = $Decode->ID;

        $App->DB->Update('account', ['_id' => new MongoDB\BSON\ObjectID($ID)], ['$pull' => ['Session' => ["Token" => $Token]]]);

        // Send Email
        $User = $App->DB->Find('account', ['_id' => new MongoDB\BSON\ObjectID($ID)])->toArray();
        $Email = $User[0]->Email;
        $Sub = "Account Logout";
        $Msg = " Hello Dear ..... Thank you for ur choice"; // @TODO: fix message
        _Mail($Email, $Sub, $Msg);

        // Create Log
        $App->SetLog->Create('Logout',['UserID' => $ID]);

        JSON(["Status" => "Success", "Message" => Lang("GEN_SUCCESS")]);
    }
?>
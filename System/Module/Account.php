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

        Logger($App, 'SignIn', ['UserID' => $ID]);

        JSON(["Status" => "Success", "Message" => Lang("GEN_SUCCESS"), "Token" => $Token]);
    }

    function SignUp($App)
    {
        $Username = strtolower($_POST["Username"]);
        $Password = $_POST["Password"];
        $Email    = strtolower($_POST["Email"]);

        if (!isset($Username) || empty($Username))
            JSON(["Status" => "Failed", "Message" => Lang("SIGNUP_USERNAME_EMPTY")]);

        if (!isset($Password) || empty($Password))
            JSON(["Status" => "Failed", "Message" => Lang("SIGNUP_PASSWORD_EMPTY")]);

        if (!isset($Email) || empty($Email))
            JSON(["Status" => "Failed", "Message" => Lang("SIGNUP_EMAIL_EMPTY")]);

        if (!filter_var($Email, FILTER_VALIDATE_EMAIL))
            JSON(["Status" => "Failed", "Message" => Lang("SIGNUP_EMAIL_INVALID")]);

        if (strlen($Username) <= 2)
            JSON(["Status" => "Failed", "Message" => Lang("SIGNUP_USERNAME_SHORT")]);

        if (strlen($Username) >= 33)
            JSON(["Status" => "Failed", "Message" => Lang("SIGNUP_USERNAME_LONG")]);

        if (strlen($Password) <= 4)
            JSON(["Status" => "Failed", "Message" => Lang("SIGNUP_PASSWORD_SHORT")]);

        if (strlen($Password) >= 33)
            JSON(["Status" => "Failed", "Message" => Lang("SIGNUP_PASSWORD_LONG")]);

        if (strlen($Email) >= 65)
            JSON(["Status" => "Failed", "Message" => Lang("SIGNUP_EMAIL_LONG")]);

        if (!preg_match("/^(?![^A-Za-z])(?!.*\.\.)[A-Za-z0-9_.]+(?<![^A-Za-z])$/", $Username))
            JSON(["Status" => "Failed", "Message" => Lang("SIGNUP_USERNAME_INVALID")]);

        $_Username = $App->DB->find('account', ['Username' => $Username])->toArray();

        if (!empty($_Username))
            JSON(["Status" => "Failed", "Message" => Lang("SIGNUP_USERNAME_EXIST")]);

        $_Email = $App->DB->find('account', ['Email' => $Email])->toArray();

        if (!empty($_Email))
            JSON(["Status" => "Failed", "Message" => Lang("SIGNUP_")]);

        $Password = password_hash($Password, PASSWORD_BCRYPT);

        $App->DB->Insert('account', ['Username' => $Username, 'Password' => $Password, 'Email' => $Email, 'CreatedTime' => time()]);

        Logger($App, 'SignUp', ['Username' => $Username]);

        JSON(["Status" => "Success", "Message" => Lang("GEN_SUCCESS")]);
    }

    function SignOut($App)
    {
        $Token = $_SERVER['HTTP_TOKEN'];

        if (!isset($Token) || empty($Token))
            JSON(["Status" => "Failed", "Message" => Lang("SIGNOUT_TOKEN_EMPTY")]);

        $Decode = $App->Auth->Decode($Token);
        $ID = $Decode->ID;

        $App->DB->Update('account', ['_id' => new MongoDB\BSON\ObjectID($ID)], ['$pull' => ['Session' => ["Token" => $Token]]]);

        Logger($App, 'SignOut',['UserID' => $ID]);

        JSON(["Status" => "Success", "Message" => Lang("GEN_SUCCESS")]);
    }
?>
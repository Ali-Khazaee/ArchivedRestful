<?php
    if (!defined("ROOT")) { exit(); }

    function UsernameIsFree($App)
    {
        $Username = strtolower($_POST["Username"]);

        if (!isset($Username) || empty($Username))
            JSON(["Status" => "Failed", "Message" => Lang("USERNAMEISFREE_USERNAME_EMPTY")]);

        if (strlen($Username) <= 2)
            JSON(["Status" => "Failed", "Message" => Lang("USERNAMEISFREE_USERNAME_SHORT")]);

        if (strlen($Username) >= 33)
            JSON(["Status" => "Failed", "Message" => Lang("USERNAMEISFREE_USERNAME_LONG")]);

        if (!preg_match("/^(?![^A-Za-z])(?!.*\.\.)[A-Za-z0-9_.]+(?<![^A-Za-z])$/", $Username))
            JSON(["Status" => "Failed", "Message" => Lang("USERNAMEISFREE_USERNAME_INVALID")]);

        if (empty($App->DB->Find('account', ['Username' => $Username])->toArray()))
            JSON(["Status" => "Success", "Message" => Lang("SUCCESS")]);

        JSON(["Status" => "Failed", "Message" => Lang("FAILED")]);
    }

    function SignUp($App)
    {
        $Username = strtolower($_POST["Username"]);
        $Password = $_POST["Password"];
        $Email    = strtolower($_POST["Email"]);
        $Session  = strtolower($_POST["Session"]);

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

        $App->RateLimit->Call('SignUpQuery.1.5000');

        if (!empty($App->DB->Find('account', ['Username' => $Username])->toArray()))
            JSON(["Status" => "Failed", "Message" => Lang("SIGNUP_USERNAME_EXIST")]);

        if (!empty($App->DB->Find('account', ['Email' => $Email])->toArray()))
            JSON(["Status" => "Failed", "Message" => Lang("SIGNUP_EMAIL_EXIST")]);

        if (!isset($Session) || empty($Session))
            $Session = "Unknown - " . $_SERVER['REMOTE_ADDR'];
        else
            $Session .= " - " . $_SERVER['REMOTE_ADDR'];

        $App->RateLimit->Call('SignUpCreated.1.60000');

        $ID = $App->DB->Insert('account', ['Username' => $Username, 'Password' => password_hash($Password, PASSWORD_BCRYPT), 'Email' => $Email, 'CreatedTime' => time()])->__toString();

        $Token = $App->Auth->CreateToken(["ID" => $ID]);

        $App->DB->Update('account', ['_id' => new MongoDB\BSON\ObjectID($ID)], ['$push' => ['Session' => ['Name' => $Session, 'Token' => $Token, 'CreatedTime' => time()]]]);

        JSON(["Status" => "Success", "Message" => Lang("SUCCESS"), "Token" => $Token, "AccountID" => $ID]);
    }

    function SignIn($App)
    {
        $Username = strtolower($_POST["Username"]);
        $Password = $_POST["Password"];
        $Session  = $_POST["Session"];

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

        $Account = $App->DB->Find('account', ['Username' => $Username])->toArray();

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

        JSON(["Status" => "Success", "Message" => Lang("SUCCESS"), "Token" => $Token, "AccountID" => $ID]);
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
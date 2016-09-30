<?php
    // Don't Allow Access Directly
    if (!defined("ROOT")) { exit(); }

    // Openssl pubil and private keys
    define("SSL_PRIVATE_KEY", openssl_pkey_get_private( file_get_contents(ROOT.'Storage/private_key.pem') ));
    define("SSL_PUBLIC_KEY", openssl_pkey_get_public( file_get_contents(ROOT.'Storage/public_key.pem') ));



    /**
     *  Following lines are executed only once, to create
     *  public_key and private_key to use for openssl cryptography.
     */

//    $config = array(
//        "digest_alg" => "SHA256",
//        "private_key_bits" => 2048,
//        "private_key_type" => OPENSSL_KEYTYPE_RSA,
//    );
//    $new_key = openssl_pkey_new($config);
//    openssl_pkey_export($new_key, $private_key_pem);
//
//    $details = openssl_pkey_get_details($new_key);
//    $public_key_pem = $details['key'];
//
////    save keys in separate files for later use.
//    file_put_contents(ROOT.'Storage/private_key.pem', $private_key_pem);
//    file_put_contents(ROOT.'Storage/public_key.pem', $public_key_pem);

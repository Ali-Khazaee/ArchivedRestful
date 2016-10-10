<?php
    // Don't Allow Access Directly
    if (!defined("ROOT")) { exit(); }

    // Private Key
    define("SSL_PRIVATE_KEY", openssl_pkey_get_private(file_get_contents(ROOT . 'Storage/PrivateKey.pem')), true);

    // Pubilc Key
    define("SSL_PUBLIC_KEY", openssl_pkey_get_public(file_get_contents(ROOT . 'Storage/PublicKey.pem')), true);

    /* Uncomment Me For Generate
    $Config = array("config" => ROOT . "Storage/openssl.cnf", "digest_alg" => "SHA256", "private_key_bits" => 2048, "private_key_type" => OPENSSL_KEYTYPE_RSA);
    $NewKey = openssl_pkey_new($Config);

    // Public Key
    $Details = openssl_pkey_get_details($NewKey);

    // Private Key
    openssl_pkey_export($NewKey, $PrivateKey, NULL, $Config);

    // Save Them
    file_put_contents(ROOT . 'Storage/PrivateKey.pem', $PrivateKey);
    file_put_contents(ROOT . 'Storage/PublicKey.pem', $Details['key']);
    */
?>
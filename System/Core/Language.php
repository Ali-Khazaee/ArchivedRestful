<?php
    // Don't Allow Access Directly
    if (!defined("ROOT")) { exit(); }

    function Lang($Name)
    {
        switch ($Name)
        {
            // General Lang
            case "GEN_SUCCESS":          return 1000;
            case "GEN_LONG_USERNAME":    return 800;
            case "GEN_LONG_PASSWORD":    return 801;
            case "GEN_SHORT_USERNAME":   return 802;
            case "GEN_SHORT_PASSWORD":   return 803;
            case "GEN_EMPTY_USERNAME":   return 804;
            case "GEN_EMPTY_PASSWORD":   return 805;
            case "GEN_INVALID_USERNAME": return 806;

            // DataBase
            case "DATABASE_CONNECTION": return 1;
            // DB Reserve 2
            // DB Reserve 3
            // DB Reserve 4
            // DB Reserve 5
            // DB Reserve 6
            // DB Reserve 7
            // DB Reserve 8
            // DB Reserve 9
            // DB Reserve 10

            // Authentication
            case "AUTH_EMPTY_TOKEN":           return 11;
            case "AUTH_EMPTY_DATA":            return 12;
            case "AUTH_EXPIRED_TOKEN":         return 13;
            case "AUTH_CANNOT_SIGN":           return 14;
            case "AUTH_WRONG_SEGMENT_COUNT":   return 15;
            case "AUTH_VERIFY_FAILED":         return 16;
            case "AUTH_OPENSSL_VERIFY_FAILED": return 17;
            // Auth Reserve 18
            // Auth Reserve 19
            // Auth Reserve 20
            // Auth Reserve 21
            // Auth Reserve 22
            // Auth Reserve 23
            // Auth Reserve 24
            // Auth Reserve 25
            // Auth Reserve 26
            // Auth Reserve 27
            // Auth Reserve 28
            // Auth Reserve 29
            // Auth Reserve 30

            // Register
            case "REGISTER_EMPTY_EMAIL":            return 31;
            case "REGISTER_INVALID_EMAIL":          return 32;
            case "REGISTER_LONG_EMAIL":             return 33;
            case "REGISTER_ALREADY_EXIST_USERNAME": return 34;
            case "REGISTER_ALREADY_EMAIL":          return 35;
            // Register Reserve 36
            // Register Reserve 37
            // Register Reserve 38
            // Register Reserve 38
            // Register Reserve 39
            // Register Reserve 40

            // Login
            case "LOGIN_EMPTY_SESSION":           return 41;
            case "LOGIN_NOT_EXIST_USERNAME":      return 42;
            case "LOGIN_WRONG_USERNAME_PASSWORD": return 43;
            // Login Reserve 44
            // Login Reserve 45
            // Login Reserve 46
            // Login Reserve 47
            // Login Reserve 48
            // Login Reserve 49

            // Upload
            case "UPLOAD_EMPTY_FILE":           return 51;
            // Login Reserve 44
            // Login Reserve 45
            // Login Reserve 46
            // Login Reserve 47
            // Login Reserve 48
            // Login Reserve 49

        }
    }
?>
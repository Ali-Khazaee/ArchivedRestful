<?php
    // Don't Allow Access Directly
    if (!defined("ROOT")) { exit(); }

    function Lang($Name)
    {
        switch ($Name)
        {
            // General Lang
            case "GEN_SUCCESS":          return 1000; break;
            case "GEN_LONG_USERNAME":    return 800;  break;
            case "GEN_LONG_PASSWORD":    return 801;  break;
            case "GEN_SHORT_USERNAME":   return 802;  break;
            case "GEN_SHORT_PASSWORD":   return 803;  break;
            case "GEN_EMPTY_USERNAME":   return 804;  break;
            case "GEN_EMPTY_PASSWORD":   return 805;  break;
            case "GEN_INVALID_USERNAME": return 806;  break;

            // DataBase
            case "DATABASE_CONNECTION": return 1; break;
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
            case "AUTH_EMPTY_TOKEN":           return 11; break;
            case "AUTH_EMPTY_DATA":            return 12; break;
            case "AUTH_EXPIRED_TOKEN":         return 13; break;
            case "AUTH_CANNOT_SIGN":           return 14; break;
            case "AUTH_WRONG_SEGMENT_COUNT":   return 15; break;
            case "AUTH_VERIFY_FAILED":         return 16; break;
            case "AUTH_OPENSSL_VERIFY_FAILED": return 17; break;
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
            case "REGISTER_EMPTY_EMAIL":            return 31; break;
            case "REGISTER_INVALID_EMAIL":          return 32; break;
            case "REGISTER_LONG_EMAIL":             return 33; break;
            case "REGISTER_ALREADY_EXIST_USERNAME": return 34; break;
            case "REGISTER_ALREADY_EMAIL":          return 35; break;
            // Register Reserve 36
            // Register Reserve 37
            // Register Reserve 38
            // Register Reserve 38
            // Register Reserve 39
            // Register Reserve 40

            // Login
            case "LOGIN_EMPTY_SESSION":           return 41; break;
            case "LOGIN_NOT_EXIST_USERNAME":      return 42; break;
            case "LOGIN_WRONG_USERNAME_PASSWORD": return 43; break;
            // Login Reserve 44
            // Login Reserve 45
            // Login Reserve 46
            // Login Reserve 47
            // Login Reserve 48
            // Login Reserve 49
        }
    }
?>
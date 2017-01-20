<?php
    if (!defined("ROOT")) { exit(); }

    function Lang($Name)
    {
        switch ($Name)
        {
            // General
            case "FAILED":  return 999;
            case "SUCCESS": return 1000;

            // Authentication
            case "AUTH_EMPTY_TOKEN":             return 1001;
            case "AUTH_EMPTY_DATA":              return 1002;
            case "AUTH_EXPIRED_TOKEN":           return 1003;
            case "AUTH_CANNOT_SIGN":             return 1004;
            case "AUTH_WRONG_SEGMENT_COUNT":     return 1005;
            case "AUTH_VERIFY_FAILED":           return 1006;
            // Auth Reserve 1007
            // Auth Reserve 1008
            // Auth Reserve 1009
            // Auth Reserve 1010
            // Auth Reserve 1011
            // Auth Reserve 1012
            // Auth Reserve 1013
            // Auth Reserve 1014
            // Auth Reserve 1015
            // Auth Reserve 1016
            // Auth Reserve 1017
            // Auth Reserve 1018
            // Auth Reserve 1019

            // Database
            case "DATABASE_CONNECTION":          return 1020;
            // DB Reserve 1021
            // DB Reserve 1022
            // DB Reserve 1022
            // DB Reserve 1023
            // DB Reserve 1024
            // DB Reserve 1025
            // DB Reserve 1026
            // DB Reserve 1027
            // DB Reserve 1028
            // DB Reserve 1029

            // Upload
            case "UPLOAD_EMPTY_FILE":            return 1030;
            case "UPLOAD_SUCCESSFUL":            return 1031;
            case "UPLOAD_NOT_ALLOWED_FORMAT":    return 1032;
            case "UPLOAD_MAX_SIZE_LIMIT":        return 1033;
            // Upload Reserve 1034
            // Upload Reserve 1035
            // Upload Reserve 1036
            // Upload Reserve 1037
            // Upload Reserve 1038
            // Upload Reserve 1039

            // RateLimit
            case "RATELIMIT_MAX_REQUEST_EXCEED": return 1040;
            // Upload Reserve 1041
            // Upload Reserve 1042
            // Upload Reserve 1043
            // Upload Reserve 1044
            // Upload Reserve 1045
            // Upload Reserve 1046
            // Upload Reserve 1047
            // Upload Reserve 1048
            // Upload Reserve 1049

            // Username Is Free
            case "USERNAMEISFREE_USERNAME_EMPTY":   return 1;
            case "USERNAMEISFREE_USERNAME_SHORT":   return 2;
            case "USERNAMEISFREE_USERNAME_LONG":    return 3;
            case "USERNAMEISFREE_USERNAME_INVALID": return 4;

            // Sign Up
            case "SIGNUP_USERNAME_EMPTY":   return 1;
            case "SIGNUP_PASSWORD_EMPTY":   return 2;
            case "SIGNUP_EMAIL_EMPTY":      return 3;
            case "SIGNUP_EMAIL_INVALID":    return 4;
            case "SIGNUP_USERNAME_SHORT":   return 5;
            case "SIGNUP_USERNAME_LONG":    return 6;
            case "SIGNUP_PASSWORD_SHORT":   return 7;
            case "SIGNUP_PASSWORD_LONG":    return 8;
            case "SIGNUP_EMAIL_LONG":       return 9;
            case "SIGNUP_USERNAME_INVALID": return 10;
            case "SIGNUP_USERNAME_EXIST":   return 11;
            case "SIGNUP_EMAIL_EXIST":      return 12;

            // Sign In
            case "SIGNIN_USERNAME_EMPTY":     return 1;
            case "SIGNIN_PASSWORD_EMPTY":     return 2;
            case "SIGNIN_USERNAME_SHORT":     return 3;
            case "SIGNIN_USERNAME_LONG":      return 4;
            case "SIGNIN_PASSWORD_SHORT":     return 5;
            case "SIGNIN_PASSWORD_LONG":      return 6;
            case "SIGNIN_USERNAME_INVALID":   return 7;
            case "SIGNIN_USERNAME_NOT_EXIST": return 8;
            case "SIGNIN_DATA_WRONG":         return 9;

            // Category Save
            case "CATEGORYSAVE_EMPTY":      return 1;
            case "CATEGORYSAVE_NOT_ENOUGH": return 2;
            case "CATEGORYSAVE_ALREADY":    return 3;

            // Profile Set
            case "PROFILESET_USERNAME_SHORT": return 1;
            case "PROFILESET_USERNAME_LONG":  return 2;
        }
    }
?>
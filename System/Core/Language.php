<?php
    if (!defined("ROOT")) { exit(); }

    function Lang($Name)
    {
        switch ($Name)
        {
            // General
            case "SUCCESS":                      return 1000;

            // Authentication
            case "AUTH_EMPTY_TOKEN":             return 1001;
            case "AUTH_EMPTY_DATA":              return 1002;
            case "AUTH_EXPIRED_TOKEN":           return 1003;
            case "AUTH_CANNOT_SIGN":             return 1004;
            case "AUTH_WRONG_SEGMENT_COUNT":     return 1005;
            case "AUTH_VERIFY_FAILED":           return 1006;
            case "AUTH_OPENSSL_VERIFY_FAILED":   return 1007;
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
        }
    }
?>
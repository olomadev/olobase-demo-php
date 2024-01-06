<?php

return [
    // Error Upload
    // 
    'File is not provided' => 'File is not received',
    'Uploaded file has expired or file does not exists' => 'Uploaded file has expired or file does not exists',
    'Please make sure the column headings are spelled correctly' => 'We have not verified the column headers of the uploaded file for this list. Please make sure the column headings are spelled correctly',
    'This file format is not allowed' => 'This file format is not allowed',
    'The uploaded file exceeds the upload_max_filesize directive in php.ini' => 'The uploaded file exceeds the upload max file size limit',
    'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form' => 'The uploaded file exceeds the upload max file size limit',
    'The uploaded file was only partially uploaded' => 'The uploaded file was only partially uploaded',
    'No file was uploaded' => 'No file was uploaded',
    'Missing a temporary folder' => 'Missing a temporary folder',
    'Failed to write file to disk' => 'Failed to write file to disk',
    'File upload stopped by extension' => 'File upload stopped by extension',
    'Unknown upload error' => 'Unknown upload error',
    'Only .xls and .xlsx file formats are supported' => 'Only .xls and .xlsx file formats are supported',
    'Max allowed upload size exceed' => 'Max allowed upload size exceed',

    // Validation errors
    // 
    'Empty file id' => 'Empty file id',
    'Empty file content' => 'Empty file content',
    'Empty "allowed_extensions" option' => 'Empty "allowed_extensions" option',
    'Empty "max_allowed_upload" option' => 'Empty "maximum_allowed_upload" option',
    'Empty file "mime_types" option' => 'Empty "mime_types" option',
    'Invalid file content' => 'Invalid file content',
    'Invalid file mime type' => 'Invalid mime type',
    'Excel file not approved' => 'Excel file not approved',

    // Reset code validations
    // 
    'Your password reset code is incorrect or expired' => 'Your password reset code is incorrect or expired',
    // Resurce ownerships
    // 
    'You are not authorized to modify a record that is not yours' => 'You are not authorized to modify a record that is not yours',
    // Password validations
    // 
    'Current password is not correct' => 'Current password is not correct',
    'Old password is not correct' => 'Current password is not correct',

    // Sheet import component
    // 
    'This file has expired, please try uploading it again' => 'This file has expired, please try uploading it again',
    'No such company is defined in the database' => 'No such company is defined in the database',

    // Employee list
    // 
    'Please first choose at least one employee list' => 'Please first choose at least one employee list',
    
    // General errors
    // 
    'Invalid token' => 'Invalid token',
    'Token value cannot be sent empty' => 'Token value cannot be sent empty',
    'Token not expired to refresh' => 'Token not expired to refresh',
    'Username or password is incorrect' => 'Username or password is incorrect',
    'Username and password fields must be given' => 'Username and password fields must be given',
    'This account is inactive or awaiting approval' => 'This account is inactive or awaiting approval',
    'There is no role defined for this user' => 'There is no role defined for this user',
    'Authentication required. Please sign in to your account' => 'Authentication required. Please sign in to your account',
    'Ip validation failed and you are logged out' => 'Your IP address could not be verified and has been logged out for security reasons',
    'Browser validation failed and you are logged out' => 'Your browser could not be verified and has been logged out for security reasons',

    // Restricted Mode Middleware
    // 
    'Demo mode can only handle read operations. To perform writes, install the demo application in your environment and remove the RestrictedModeMiddleware class from config/pipeline.php' => 'Demo mode can only handle read operations. To perform writes, install the demo application in your environment and remove the RestrictedModeMiddleware class from config/pipeline.php',

    // Failed Logins
    // 
    'BLOCK_30_SECONDS' => 'Too many incorrect login attempts.Login operations has been suspended for 30 seconds for security reasons.',
    'BLOCK_60_SECONDS' => 'Too many incorrect login attempts.Login operations has been suspended for 60 seconds for security reasons.',
    'BLOCK_300_SECONDS' => 'Too many incorrect login attempts.Login operations has been suspended for 5 minute for security reasons.Try to reset your password.',
    'BLOCK_1800_SECONDS' => 'Too many incorrect login attempts.Login operations has been suspended for 30 minute for security reasons.Try to reset your password.',
    'BLOCK_86400_SECONDS' => 'Too many incorrect login attempts.Login operations has been suspended for 1 day for security reasons.Try to reset your password.',

    // Php Json parse errors
    //
    JSON_ERROR_DEPTH => 'Client json error: The maximum stack depth has been exceeded',
    JSON_ERROR_STATE_MISMATCH => 'Client json error: Client sent and invalid or malformed JSON',
    JSON_ERROR_CTRL_CHAR => 'Client json error: Client sent Control character error, possibly incorrectly encoded',
    JSON_ERROR_SYNTAX => 'Client json error: Syntax error',
    JSON_ERROR_UTF8 => 'Client json error: Malformed UTF-8 characters, possibly incorrectly encoded',
    JSON_ERROR_RECURSION => 'Client json error: One or more recursive references in the value to be encoded',
    JSON_ERROR_INF_OR_NAN => 'Client json error: One or more NAN or INF values in the value to be encoded',
    JSON_ERROR_UNSUPPORTED_TYPE => 'Client json error: A value of a type that cannot be encoded was given',
    JSON_ERROR_INVALID_PROPERTY_NAME => 'Client json error: A property name that cannot be encoded was given',
    JSON_ERROR_UTF16 => 'Client json error: Malformed UTF-16 characters, possibly incorrectly encoded',

];
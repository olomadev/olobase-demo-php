<?php

return [
    // Error Upload
    // 
    'File is not provided' => 'Dosya alınamadı',
    'Uploaded file has expired or file does not exists' => 'Yüklenen dosyanın süresi sona erdi ya da bu dosya mevcut değil',
    'Please make sure the column headings are spelled correctly' => 'Bu liste için yüklenen dosyanın sütun başlıklarını doğrulayamadık. Lütfen sütun başlıklarının doğru yazıldığından emin olunuz.',
    'This file format is not allowed' => 'Bu dosya formatına izin verilmiyor',
    'The uploaded file exceeds the upload_max_filesize directive in php.ini' => 'Yüklenen dosya, maksimum dosya boyutu sınırını aşıyor',
    'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form' => 'Yüklenen dosya, maksimum dosya boyutu sınırını aşıyor',
    'The uploaded file was only partially uploaded' => 'Yüklenen dosya yalnızca kısmen yüklendi',
    'No file was uploaded' => 'Dosya yüklenmedi',
    'Missing a temporary folder' => 'Geçici klasör eksik',
    'Failed to write file to disk' => 'Dosya diske yazılamadı',
    'File upload stopped by extension' => 'Dosya yükleme, uzantı tarafından durduruldu',
    'Unknown upload error' => 'Bilinmeyen yükleme hatası',
    'Only .xls and .xlsx file formats are supported' => 'Sadece .xls ve .xlsx dosya formatları destekleniyor',
    'Max allowed upload size exceed' => 'İzin verilen dosya yükleme sınırı aşıldı',

    // Validation errors
    //
    'Empty file id' => 'Boş dosya id',
    'Empty file content' => 'Boş dosya içeriği',
    'Empty "allowed_extensions" option' => '"allowed_extensions" seçeneği boş gözüküyor',
    'Empty "max_allowed_upload" option' => '"maximum_allowed_upload" seçeneği boş gözüküyor',
    'Empty file "mime_types" option' => 'mime_types" seçeneği boş gözüküyor',
    'Invalid file content' => 'Geçersiz dosya içeriği',
    'Invalid file mime type' => 'Geçersiz dosya türü',
    'Excel file not approved' => 'Excel dosyası onaylı değil',

    // Reset code validations
    // 
    'Your password reset code is incorrect or expired' => 'Şifre sıfırlama kodunuz yanlış veya süresi dolmuş',
    // Resurce ownerships
    // 
    'You are not authorized to modify a record that is not yours' => 'Size ait olmayan bir kaydı değiştirme yetkiniz yoktur',
    // Password validations
    // 
    'Current password is not correct' => 'Geçerli şifre yanlış girildi',
    'Old password is not correct' => 'Geçerli şifre yanlış girildi',

    // Employee list
    // 
    'Please first choose at least one employee list' => 'Lütfen önce en az bir çalışan listesi seçin',
    
    // Sheet import component
    // 
    'This file has expired, please try uploading it again' => 'Bu dosyanın süresi geçti, lütfen yeniden yüklemeyi deneyin',
    'No such company is defined in the database' => 'Veritabanında böyle bir şirket tanımlı değil',

    // General errors
    //
    'Invalid token' => 'Geçersiz jeton',
    'Token value cannot be sent empty' => 'Jeton değeri boş gönderilemez',
    'Token not expired to refresh' => 'Jetonun yenileme için süresi dolmadı',
    'Username or password is incorrect' => 'Kullanıcı adı veya şifre hatalı girildi',
    'Username and password fields must be given' => 'Kullanıcı ve şifre alanları gönderilmeli',
    'This account is inactive or awaiting approval' => 'Bu hesap pasif veya onay bekliyor',
    'There is no role defined for this user' => 'Bu kullanıcı herhangi bir rol tanımlı değil',
    'Authentication required. Please sign in to your account' => 'Kimlik doğrulama gerekli. Lütfen hesabınıza giriş yapın',
    'Ip validation failed and you are logged out' => 'IP adresiniz doğrulanamadı ve güvenlik nedeniyle çıkış yapıldı',
    'Browser validation failed and you are logged out' => 'Tarayıcınız doğrulanamadı ve güvenlik nedeniyle çıkış yapıldı',

    // Restricted Mode Middleware
    // 
    'Demo mode can only handle read operations. To perform writes, install the demo application in your environment and remove the RestrictedModeMiddleware class from config/pipeline.php' => 'Demo modu yalnızca okuma işlemlerini yürütebilir. Yazma işlemlerini gerçekleştirebilmek için demo uygulamasını çalışma ortamınızda kurun ve RestrictedModeMiddleware sınıfını config/pipeline.php dosyasından kaldırın',

    // Failed Logins
    // 
    'BLOCK_30_SECONDS' => 'Çok fazla yanlış giriş denemesi yapıldı. Güvenlik nedeniyle giriş işlemleri 30 saniye süreyle durduruldu.',
    'BLOCK_60_SECONDS' => 'Çok fazla yanlış giriş denemesi yapıldı. Güvenlik nedeniyle giriş işlemleri 60 saniye süreyle durduruldu.',
    'BLOCK_300_SECONDS' => 'Çok fazla hatalı giriş denemesi yapıldı. Güvenlik nedeniyle giriş işlemleri 5 dakika süreyle durduruldu.Lütfen şifrenizi sıfırlamayı deneyin.',
    'BLOCK_1800_SECONDS' => 'Çok fazla yanlış giriş denemesi yapıldı. Güvenlik nedeniyle giriş işlemleri 30 dakika süreyle durduruldu.Lütfen şifrenizi sıfırlamayı deneyin.',
    'BLOCK_86400_SECONDS' => 'Çok fazla hatalı giriş denemesi yapıldı. Güvenlik nedeniyle giriş işlemleri 1 gün süreyle durduruldu.Lütfen şifrenizi sıfırlamayı deneyin.',
    
    // Php Json parse errors
    //
    JSON_ERROR_DEPTH => 'İstemci json hatası: Maksimum yığın derinliği aşıldı',
    JSON_ERROR_STATE_MISMATCH => 'İstemci json hatası: İstemci tarafından gönderilen ve geçersiz veya hatalı biçimlendirilmiş JSON',
    JSON_ERROR_CTRL_CHAR => 'İstemci json hatası: Muhtemelen yanlış kodlanmış kontrol karakteri',
    JSON_ERROR_SYNTAX => 'İstemci json hatası: Sözdizimi hatası',
    JSON_ERROR_UTF8 => 'İstemci json hatası: Hatalı biçimlendirilmiş UTF-8 karakterleri, muhtemelen yanlış kodlanmış',
    JSON_ERROR_RECURSION => 'İstemci json hatası: Kodlanacak değerde bir veya daha fazla özyinelemeli başvuru',
    JSON_ERROR_INF_OR_NAN => 'İstemci json hatası: Kodlanacak değerde bir veya daha fazla NAN veya INF değeri',
    JSON_ERROR_UNSUPPORTED_TYPE => 'İstemci json hatası: Kodlanamayan bir tür değeri verildi',
    JSON_ERROR_INVALID_PROPERTY_NAME => 'İstemci json hatası: Kodlanamayan bir özellik adı verildi',
    JSON_ERROR_UTF16 => 'İstemci json hatası: Hatalı biçimlendirilmiş UTF-16 karakterleri, muhtemelen yanlış kodlanmış',    
];
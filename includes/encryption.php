<?php


function encrypt_id($id)
{
     $key = "your-secret-key-123";  
     $iv = substr(hash('sha256', $key), 0, 16);
     $encrypted = openssl_encrypt($id, "AES-128-CBC", $key, 0, $iv);
     return base64_encode($encrypted);
}

function decrypt_id($encrypted_id)
{
     $key = "your-secret-key-123";  
     $iv = substr(hash('sha256', $key), 0, 16);
     $decrypted = openssl_decrypt(base64_decode($encrypted_id), "AES-128-CBC", $key, 0, $iv);
     if (is_numeric($decrypted)) {
          return (int) $decrypted;
     }
     return false;
}
?>
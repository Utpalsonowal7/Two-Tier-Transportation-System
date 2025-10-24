<?php
session_start();

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");


// echo '<pre>';
// print_r($_SESSION);
// echo '</pre>';
// exit();

unset($_SESSION['login']);
unset($_SESSION['adminid']);

session_destroy();

if (ini_get('session.use_cookies')) {
     $params = session_get_cookie_params();
     setcookie(session_name(), '', time() - 18000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
}

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Expires: Sat, 26 Jul 1997 05:00:00 GMT'); 
header('Pragma: no-cache');

header('Location: ../index.html');
exit();
?>
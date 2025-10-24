<?php

session_start();

$_SESSION = array(); 

if (isset($_SESSION['adminid']) || isset($_SESSION['login'])) {
     unset($_SESSION['adminid']);
     unset($_SESSION['login']);
     // $_SESSION = []; 
     session_destroy();
}


if(ini_get('session.use_cookies')) {
     $params = session_get_cookie_params();
     setcookie(session_name(), '', time() - 18000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
}

header("Cache-Control: no-cache, no-store, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

header("Location: ../index.html");
exit();
?>
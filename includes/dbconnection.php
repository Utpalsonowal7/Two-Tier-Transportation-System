<?php

$fsms_conn = pg_connect("host = localhost  dbname = fsms_auth user = postgres password = 1234");

$master_conn = pg_connect("host = localhost  dbname = master_db user = postgres password = 1234");



if (!$fsms_conn || !$master_conn ) {
  die("Database connection failed: " . pg_last_error());
}


?> 

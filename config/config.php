<?php
 
/* ---------- DB constants ---------- */
define("DB_HOST", "localhost");
define("DB_USER", "root");
define("DB_PASS", "");
define("DB_NAME", "CRAVERUSH_DB");
 
 
/* ---------- Session configuration ---------- */
if (session_status() === PHP_SESSION_NONE)
{
    session_start();
}
 
 
/* ---------- Connect (MySQLi, procedural) ---------- */
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
 
if (!$conn)
{
    die("Connection failed: " . mysqli_connect_error());
}
 

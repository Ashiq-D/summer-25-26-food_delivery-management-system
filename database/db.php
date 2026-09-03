<?php
$dbHost = "localhost";
$dbUser = "root";
$dbPass = "";
$dbName = "CRAVERUSH_DB";

/* ---------- Connect (MySQLi, procedural) ---------- */
$conn = mysqli_connect($dbHost, $dbUser, $dbPass, $dbName);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

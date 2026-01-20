<?php
$host = "localhost";
$user = "root"; 
$password = "";
$database = "jskreditmotor";

// Create connection
$konek = mysqli_connect($host, $user, $password, $database);

// Check connection
if (!$konek) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// Set charset to utf8
mysqli_set_charset($konek, "utf8");

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
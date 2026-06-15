<?php

$host = "db";
$user = "root";
$password = "root";
$database = "grocery_db";

$conn = mysqli_connect($host, $user, $password, $database);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
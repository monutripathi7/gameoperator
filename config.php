<?php

//Note: This file should be included first in every php page.
error_reporting(E_ALL);
ini_set('display_errors', 'On'); 

//ini_set('display_errors', 'Off');
//error_reporting(E_ERROR | E_PARSE);

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gamedb";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

$homepath = getcwd();
$currentdomain = 'http://localhost/';


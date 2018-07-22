<?php
  $dsn = 'mysql:host=localhost;dbname=Paulware';
  $username = 'root';
  $password = 'pi';
  $options = array (
      PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
  );
  try {
     $connection = mysqli_connect ('localhost', 'root', 'pi', 'Paulware', 80);
     if (mysqli_connect_errno()) {
        echo ("Connect failed ");
        echo (mysqli_connect_error());
        exit();
     } 
  } catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
    exit();
  }
  if (!$connection) {
     echo ("Could not connect" );
     die ('Die already: ' . mysql_error());
  } 
  // echo "<br>Successfully connected to database<br>";  
?>
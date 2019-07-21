<?php
/**
 * Database Connection
 */

 class Config
 {
     private $serverName = 'localhost';
     private $dbName     = 'jwt_api';
     private $userName   = 'root';
     private $password   = 'arb6@Son';
     public $db;

     public function connect()
     {
        try{
            $conn = new PDO('mysql:host=' .$this->serverName. ';dbname=' . $this->dbName, $this->userName, $this->password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $this->db = $conn;
        }
        catch(PDOException $e){
            echo 'Database error: ' . $e->getMessage();
        }
     }
 }

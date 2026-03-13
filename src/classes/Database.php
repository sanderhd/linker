<?php

class Database {
    private $host = "57.129.44.247:3306";
    private $db = "s28_linker";
    private $user = "u28_Er7tNIyIDl";
    private $pass = "^aI.hA=XxEh+iXjq4MQjWa=@";
    private $conn;

    // functie om te connecten met de database met de credentials van hierboven
    public function connect() 
    {
        if ($this->conn == null) {
            $this->conn = new PDO(
                "mysql:host=".$this->host.";dbname=".$this->db,
                $this->user,
                $this->pass
            );

            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }

        return $this->conn;
    }
}

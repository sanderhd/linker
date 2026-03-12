<?php

class Link {

    private $conn;
    private $table = "links";

    public function __construct($db) 
    {
        $this->conn = $db;
    }

    // alle links pakken
    public function getAll() 
    {
        $stmt = $this->conn->prepare("SELECT * FROM ".$this->table);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // link info pakken met id
    public function getById($id) 
    {
        $stmt = $this->conn->prepare("SELECT * FROM ".$this->table." WHERE id=?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // link pakken bij short id
    public function getByShortId($shortId) 
    {
        $stmt = $this->conn->prepare("SELECT * FROM ".$this->table." WHERE short_id = ?");
        $stmt->execute([$shortId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // link maken
    public function create($title, $url, $owner_id, $shortId) 
    {
        $stmt = $this->conn->prepare(
            "INSERT INTO ".$this->table." (title,url,owner_id,short_id)
             VALUES (?,?,?,?)"
        );

        return $stmt->execute([$title,$url,$owner_id,$shortId]);
    }

    // link updaten
    public function update($id, $title, $url, $owner_id) 
    {
        $stmt = $this->conn->prepare(
            "UPDATE ".$this->table."
             SET title=?, url=?, owner_id=?
             WHERE id=?"
        );

        return $stmt->execute([$title,$url,$owner_id,$id]);
    }

    // link deleten
    public function delete($id) 
    {
        $stmt = $this->conn->prepare(
            "DELETE FROM ".$this->table." WHERE id=?"
        );

        return $stmt->execute([$id]);
    }

    // short id van 6 char's maken
    public function generateShortId($length = 6) 
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

        do {
            $shortId = '';
            for ($i = 0; $i < $length; $i++) {
                $shortId .= $characters[rand(0, strlen($characters) - 1)];
            }

            $stmt = $this->conn->prepare(
                "SELECT id FROM ".$this->table." WHERE short_id=?"
            );
            $stmt->execute([$shortId]);

            $exists = $stmt->fetch();

        } while ($exists);

        return $shortId;
    }
}
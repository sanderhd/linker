<?php

class Click {
    private $conn;
    private $table = 'clicks';

    public function __construct($db) 
    {
        $this->conn = $db;
    }

    // clicks in de database loggen
    public function recordClick($linkId, $location, $device, $operatingSystem) 
    {
        $stmt = $this->conn->prepare("INSERT INTO {$this->table} (link_id, location, device, operating_system) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$linkId, $location, $device, $operatingSystem]);
    }

    // link bij id ophalen
    public function getByLinkId($linkId) 
    {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE link_id = ? ORDER BY created_at DESC");
        $stmt->execute([$linkId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

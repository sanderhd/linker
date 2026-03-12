<?php

class Analytics
{
    private $conn;
    private $clickTable = 'clicks';
    private $linksTable = 'links';

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // links bij een user ophalen
    public function getUserLinks($userId)
    {
        if (!$userId) return [];
        $stmt = $this->conn->prepare("SELECT id, title FROM {$this->linksTable} WHERE owner_id = ? ORDER BY created_at DESC");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    // specifieke link ophalen
    public function getLink($id, $userId)
    {
        if (!$id || !$userId) return null;
        $stmt = $this->conn->prepare("SELECT * FROM {$this->linksTable} WHERE id = ? AND owner_id = ?");
        $stmt->execute([$id, $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    // clicks van een link pakken
    public function getClicks($linkId, $rangeStart = null)
    {
        if (!$linkId) return [];
        $sql = "SELECT * FROM {$this->clickTable} WHERE link_id = ?";
        $params = [$linkId];
        if ($rangeStart) {
            $sql .= " AND date >= ?";
            $params[] = $rangeStart->format('Y-m-d 00:00:00');
        }
        $sql .= " ORDER BY date ASC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    // clicks groep per dag
    public function getClickCountsPerDay(array $clicks, $rangeStart = null)
    {
        $clickCounts = [];
        foreach ($clicks as $click) {
            $clickedAt = $click['date'] ?? $click['clicked_at'] ?? null;
            if (!$clickedAt) continue;
            $bucket = date('Y-m-d', strtotime($clickedAt));
            $clickCounts[$bucket] = ($clickCounts[$bucket] ?? 0) + 1;
        }

        if (empty($clickCounts) && !$rangeStart) return [];

        if (!empty($clickCounts)) {
            ksort($clickCounts);
            $dates = array_keys($clickCounts);
            $start = $rangeStart ? clone $rangeStart : new DateTime(reset($dates));
        } else {
            $start = clone $rangeStart;
        }

        $end = new DateTime('today');
        if ($end < $start) {
            $end = new DateTime(end($dates ?? [date('Y-m-d')]));
        }
        $end->modify('+1 day');

        $period = new DatePeriod($start, new DateInterval('P1D'), $end);
        $filled = [];
        foreach ($period as $date) {
            $label = $date->format('Y-m-d');
            $filled[$label] = $clickCounts[$label] ?? 0;
        }

        return $filled;
    }

    // top locaties, devices en os
    public function getTop($linkId, $rangeStart = null, $column = 'operating_system', $limit = 3)
    {
        if (!$linkId) return [];
        $rangeSql = $rangeStart ? " AND date >= ?" : "";
        $sql = "SELECT {$column} AS label, COUNT(*) AS total FROM {$this->clickTable} WHERE link_id = ?" . $rangeSql . " AND {$column} IS NOT NULL AND {$column} <> '' GROUP BY {$column} ORDER BY total DESC LIMIT " . (int)$limit;
        $params = [$linkId];
        if ($rangeStart) $params[] = $rangeStart->format('Y-m-d 00:00:00');

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    // trending links berekenen
    public function getTrend($linkId)
    {
        if (!$linkId) return [null, null];

        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM {$this->clickTable} WHERE link_id = ? AND date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)");
        $stmt->execute([$linkId]);
        $last7 = (int) $stmt->fetchColumn();

        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM {$this->clickTable} WHERE link_id = ? AND date >= DATE_SUB(CURDATE(), INTERVAL 14 DAY) AND date < DATE_SUB(CURDATE(), INTERVAL 7 DAY)");
        $stmt->execute([$linkId]);
        $prev7 = (int) $stmt->fetchColumn();

        $trendPercent = null;
        $trendDirection = null;
        if ($prev7 > 0) {
            $trendPercent = round((($last7 - $prev7) / $prev7) * 100);
            $trendDirection = $trendPercent >= 0 ? 'up' : 'down';
        } elseif ($last7 > 0) {
            $trendPercent = 100;
            $trendDirection = 'up';
        }

        return [$trendPercent, $trendDirection];
    }
}

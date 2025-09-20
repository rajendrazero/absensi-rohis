<?php
class Database {
    public $conn;
    public function __construct() {
        $host = "maglev.proxy.rlwy.net";
        $port = "35682"; // ganti sesuai port Railway DB-mu
        $db = "absensi_rohis";
        $user = "root";
        $pass = "RQDnCUbLBvpLHlbgfDDGuORKVjFOznhB";

        try {
            $this->conn = new PDO(
                "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4",
                $user,
                $pass
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            die("Koneksi gagal: " . $e->getMessage());
        }
    }
}

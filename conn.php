    <?php
class Database {
    public $conn;
    public function __construct() {
        $host = "localhost";
        $db = "absensi_rohis";
        $user = "root";
        $pass = "";
        try {
            $this->conn = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            die("Koneksi gagal: " . $e->getMessage());
        }
    }
}


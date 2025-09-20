    <?php
  require_once "conn.php";

  date_default_timezone_set('Asia/Jakarta');
  
class AbsensiController {

    private $db;
    // public $lokasiX = 3.6002186; // Latitude Perintis
    public $lokasiX = 3.591537; 
    public $lokasiY = 98.623725; 
    // public $lokasiY = 98.6900058; // Longitude Perintis
    public $radius = 50;// meter

    public function __construct() {
        $this->db = (new Database())->conn;
    }

    // haversine formula
    private function haversine($lat1, $lon1, $lat2, $lon2) {
        $R = 6371000; // radius bumi (meter)
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat/2) * sin($dLat/2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon/2) * sin($dLon/2);
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        return $R * $c;
    }

    public function hitungJarak($lat, $lon) {
        return $this->haversine(floatval($lat), floatval($lon), $this->lokasiX, $this->lokasiY);
    }

    public function store($data) {
        $lat = floatval($data['lat']);
        $lon = floatval($data['lon']);

        // cek radius lokasi
        $distance = $this->haversine($lat, $lon, $this->lokasiX, $this->lokasiY);
        if ($distance > $this->radius) {
            return false;
        }

        $sql = "INSERT INTO absensi (nama, acara, kelas, jurusan, lat, lon, waktu)
                VALUES (:nama, :acara, :kelas, :jurusan, :lat, :lon, :waktu)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            "nama" => $data['nama'],
            "acara" => $data['acara'],
            "kelas" => $data['kelas'],
            "jurusan" => $data['jurusan'],
            "lat" => $lat,
            "lon" => $lon,
            "waktu" => $data['waktu'],
        ]);
    }

    public function all($filter = []) {
    $sql = "SELECT * FROM absensi WHERE 1=1";
    $params = [];

    if (!empty($filter['acara'])) {
        $sql .= " AND acara = :acara";
        $params['acara'] = $filter['acara'];
    }
    if (!empty($filter['start']) && !empty($filter['end'])) {
        $sql .= " AND DATE(waktu) BETWEEN :start AND :end";
        $params['start'] = $filter['start'];
        $params['end'] = $filter['end'];
    }

    // urutkan nama → kelas → jurusan
    $sql .= " ORDER BY nama ASC, kelas ASC, jurusan ASC";

    $stmt = $this->db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


    public function countByAcaraThisYear() {
        $year = date("Y");
        $sql = "SELECT acara, COUNT(*) as total 
                FROM absensi 
                WHERE YEAR(waktu) = :year 
                GROUP BY acara";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(["year" => $year]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}


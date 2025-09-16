<?php
require 'conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama      = $_POST['nama'];
    $kelas     = $_POST['kelas'];
    $event     = $_POST['event'];
    $latitude  = $_POST['latitude'];
    $longitude = $_POST['longitude'];

    // Lokasi SMKN 9 Medan
    $lokasi_resmi_lat = 3.589452;
    $lokasi_resmi_lng = 98.674796;

    // Hitung jarak menggunakan Haversine Formula
    function hitungJarak($lat1, $lon1, $lat2, $lon2) {
        $earthRadius = 6371; // KM

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distance = $earthRadius * $c;

        return $distance; // dalam kilometer
    }

    $jarak = hitungJarak($latitude, $longitude, $lokasi_resmi_lat, $lokasi_resmi_lng);

    // Batas jarak maksimal (dalam KM)
    $batas_jarak_km = 0.1; // 100 meter

    if ($jarak <= $batas_jarak_km) {
        $stmt = $conn->prepare("INSERT INTO absensi (nama, kelas, tanggal, latitude, longtitude, event) VALUES (?, ?, NOW(), ?, ?, ?)");
        $stmt->bind_param("sssss", $nama, $kelas, $latitude, $longitude, $event);
        
        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "message" => "Absensi berhasil!"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Gagal menyimpan data."]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Lokasi di luar area SMKN 9 Medan."]);
    }
}
?>

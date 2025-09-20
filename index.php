  <?php
  include_once "controller.php";
  $controller = new AbsensiController();

  $msg = "";
  $type = "";

  // simpan data absensi
  if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['submit_absen'])) {
      $data = [
          "nama" => trim($_POST['nama']),
          "acara" => trim($_POST['acara']),
          "kelas" => trim($_POST['kelas']),
          "jurusan" => trim($_POST['jurusan']),
          "lat" => $_POST['lat'],
          "lon" => $_POST['lon'],
          "waktu" => date("Y-m-d H:i:s"),
      ];

      if (empty($data['nama']) || empty($data['acara']) || empty($data['kelas']) || empty($data['jurusan']) || empty($data['lat']) || empty($data['lon'])) {
          $msg = "Semua field wajib diisi dan lokasi harus diaktifkan!";
          $type = "error";
      } else {
          $sukses = $controller->store($data);
          if ($sukses) {
              $msg = "Absensi berhasil!";
              $type = "success";
          } else {
              $msg = "Gagal: Anda di luar radius lokasi!";
              $type = "error";
          }
      }
  }

  $filter = [];
  if (!empty($_GET['acara'])) $filter['acara'] = $_GET['acara'];
  if (!empty($_GET['start']) && !empty($_GET['end'])) {
      $filter['start'] = $_GET['start'];
      $filter['end'] = $_GET['end'];
  }

  $dataAbsensi = $controller->all($filter);
  $rekap = $controller->countByAcaraThisYear();
  ?>
  <!DOCTYPE html>
  <html lang="id">
  <head>
      <meta charset="UTF-8">
      <title>Absensi</title>

          <!-- SEO Dasar -->
    <title>Absensi Rohis SMKN 9 Medan | Sistem Absensi Online</title>
    <meta name="description" content="Sistem Absensi Rohis SMKN 9 Medan untuk memudahkan presensi peserta di setiap acara. Deteksi lokasi otomatis dan laporan absensi real-time.">
    <meta name="keywords" content="absensi, rohis, SMKN 9 Medan, presensi, lokasi, laporan, sistem absensi online">
    <meta name="author" content="Rohis SMKN 9 Medan">
    <meta name="robots" content="index, follow">

    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="32x32" href="rohis.jpg">
<link rel="icon" type="image/png" sizes="16x16" href="rohis.jpg">




      <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
      <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
      <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
      <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
      <style>
        #map { height: 400px; width: 100%; }
      </style>

    <meta name="google-site-verification" content="odF4v2EKkyFcAMxCR03lfbFLiICsMPBUGsYtpCKvATQ" />
  </head>
  <body class="bg-light">
  <div class="container py-4">

      <h2 class="mb-4 text-center">📋 Sistem Absensi (Rohis Smkn9 Medan)</h2>

      <div class="alert alert-warning">
          <b>Lokasi Panitia:</b> <?= $controller->lokasiX ?>, <?= $controller->lokasiY ?> (Radius: <?= $controller->radius ?> m)<br>
          <b>Lokasi Anda:</b> <span id="lokasiKita">Belum terdeteksi...</span><br>
          <b>Status:</b> <span id="statusLokasi" class="fw-bold text-danger">Belum dicek</span>
      </div>

      <!-- MAP LEAFLET -->
      <div id="map"></div>

      <?php if (!empty($msg)): ?>
      <script>
      Swal.fire({
          icon: "<?= $type ?>",
          title: "<?= $msg ?>",
          timer: 3000,
          showConfirmButton: false
      });
      </script>
      <?php endif; ?>

      <!-- Rekap -->
      <div class="row mb-4 mt-3">
          <?php foreach($rekap as $r): ?>
          <div class="col-md-3">
              <div class="card text-bg-primary mb-3">
                  <div class="card-body">
                      <h5 class="card-title"><?= htmlspecialchars($r['acara']) ?></h5>
                      <p class="card-text fs-4 fw-bold"><?= $r['total'] ?> hadir</p>
                      <small>Tahun <?= date("Y") ?></small>
                  </div>
              </div>
          </div>
          <?php endforeach; ?>
      </div>

      <!-- Form Absensi -->
      <div class="card mb-4">
          <div class="card-header">Form Absensi</div>
          <div class="card-body">
              <button type="button" id="btnLokasi" class="btn btn-warning mb-3">📍 Izinkan Lokasi</button>
              <form method="post" id="formAbsen">
                  <div class="row g-3">
                      <div class="col-md-3">
                          <input type="text" name="nama" class="form-control" placeholder="Nama Lengkap" required>
                      </div>
                      <div class="col-md-3">
                          <select name="acara" class="form-control" required>
                              <option value="">-- Pilih Acara --</option>
                              <option value="Maulid Nabi">Maulid Nabi</option>
                              <option value="Isra Mi'raj">Isra Mi'raj</option>
                              <option value="1 Muharam">1 Muharam</option>
                              <option value="Pesantren Kilat">Pesantren Kilat</option>
                          </select>
                      </div>
                      <div class="col-md-2">
                          <select name="kelas" class="form-control" required>
                              <option value="">-- Pilih Kelas --</option>
                              <option value="X">X</option>
                              <option value="XI">XI</option>
                              <option value="XII">XII</option>
                          </select>
                      </div>
                      <div class="col-md-2">
                        <select name="jurusan" class="form-control" required>
                          <option value="">-- Pilih Jurusan --</option>

                          <?php 
                          $jurusanList = ["RPL", "PEKSOS", "PSPT", "TKJ", "AN", "DKV"];
                          foreach($jurusanList as $jur) {
                              echo "<optgroup label='$jur'>";
                              for($i = 1; $i <= 5; $i++) { // misal tiap jurusan ada 5 kelas
                                  $val = $jur . "-" . $i;
                                  $selected = (isset($_GET['jurusan']) && $_GET['jurusan'] == $val) ? "selected" : "";
                                  echo "<option value='$val' $selected>$val</option>";
                              }
                              echo "</optgroup>";
                          }
                          ?>
                      </select>
                    </div>
                      <input type="hidden" name="lat" id="lat">
                      <input type="hidden" name="lon" id="lon">
                      <div class="col-md-2">
                          <button type="submit" name="submit_absen" class="btn btn-success w-100">Absen</button>
                      </div>
                  </div>
              </form>
          </div>
      </div>

      <!-- Filter -->
  <form method="get" class="row g-3 mb-4">
      <div class="col-md-3">
          <select name="acara" class="form-control" required>
              <option value="">-- Pilih Acara --</option>
              <option value="Maulid Nabi" <?= (isset($_GET['acara']) && $_GET['acara']=="Maulid Nabi") ? "selected" : "" ?>>Maulid Nabi</option>
              <option value="Isra Mi'raj" <?= (isset($_GET['acara']) && $_GET['acara']=="Isra Mi'raj") ? "selected" : "" ?>>Isra Mi'raj</option>
              <option value="1 Muharam" <?= (isset($_GET['acara']) && $_GET['acara']=="1 Muharam") ? "selected" : "" ?>>1 Muharam</option>
              <option value="Pesantren Kilat" <?= (isset($_GET['acara']) && $_GET['acara']=="Pesantren Kilat") ? "selected" : "" ?>>Pesantren Kilat</option>
          </select>
      </div>
      <div class="col-md-3">
          <input type="date" name="start" class="form-control" 
                value="<?= isset($_GET['start']) ? htmlspecialchars($_GET['start']) : "" ?>">
      </div>
      <div class="col-md-3">
          <input type="date" name="end" class="form-control" 
                value="<?= isset($_GET['end']) ? htmlspecialchars($_GET['end']) : "" ?>">
      </div>
      <div class="col-md-3 d-flex">
          <button type="submit" class="btn btn-primary me-2">Filter</button>
          <a href="<?= strtok($_SERVER["REQUEST_URI"], '?') ?>" class="btn btn-secondary">Reset</a>
      </div>
  </form>


      <!-- Tabel -->
      <div class="card">
          <div class="card-header">Data Absensi</div>
          <div class="card-body">
              <table id="absensiTable" class="table table-bordered table-striped">
                  <thead>
                      <tr>
                          <th>Nama</th>
                          <th>Acara</th>
                          <th>Kelas</th>
                          <th>Jurusan</th>
                          <th>Latitude</th>
                          <th>Longitude</th>
                          <th>Jarak (m)</th>
                          <th>Waktu</th>
                      </tr>
                  </thead>
                  <tbody>
                      <?php foreach($dataAbsensi as $d): ?>
                      <tr>
                          <td><?= htmlspecialchars($d['nama']) ?></td>
                          <td><?= htmlspecialchars($d['acara']) ?></td>
                          <td><?= htmlspecialchars($d['kelas']) ?></td>
                          <td><?= htmlspecialchars($d['jurusan']) ?></td>
                          <td><?= $d['lat'] ?></td>
                          <td><?= $d['lon'] ?></td>
                          <td><?= round($controller->hitungJarak($d['lat'], $d['lon']), 2) ?></td>
                          <td><?= $d['waktu'] ?></td>
                      </tr>
                      <?php endforeach; ?>
                  </tbody>
              </table>
              <button id="downloadPDF" class="btn btn-danger mt-3">Download PDF</button>
              <button id="downloadExcel" class="btn btn-success mt-3">Download Excel</button>
          </div>
      </div>
  </div>

  <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
  <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

  <script>
  let map, userMarker, circle;

  function initMap() {
      let panitia = [<?= $controller->lokasiX ?>, <?= $controller->lokasiY ?>];

      map = L.map('map').setView(panitia, 15);

      // basemap
      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
          attribution: '© OpenStreetMap contributors'
      }).addTo(map);

      // marker panitia
      L.marker(panitia).addTo(map).bindPopup("Lokasi Panitia").openPopup();

      // lingkaran radius
      circle = L.circle(panitia, {
          color: 'green',
          fillColor: '#0f0',
          fillOpacity: 0.2,
          radius: <?= $controller->radius ?>
      }).addTo(map);
  }

  function getLocation() {
      if (navigator.geolocation) {
          navigator.geolocation.getCurrentPosition(function(pos) {
              let userLat = pos.coords.latitude;
              let userLon = pos.coords.longitude;

              document.getElementById("lat").value = userLat;
              document.getElementById("lon").value = userLon;
              document.getElementById("lokasiKita").innerText = userLat + ", " + userLon;

              // hapus marker lama
              if (userMarker) map.removeLayer(userMarker);

              // tampilkan marker biru
              userMarker = L.marker([userLat, userLon], {icon: L.icon({
                  iconUrl: "http://maps.google.com/mapfiles/ms/icons/blue-dot.png",
                  iconSize: [32, 32],
                  iconAnchor: [16, 32]
              })}).addTo(map).bindPopup("Lokasi Anda").openPopup();

              // hitung jarak
              let panitiaLat = <?= $controller->lokasiX ?>;
              let panitiaLon = <?= $controller->lokasiY ?>;
              let radius = <?= $controller->radius ?>;

              let R = 6371000;
              let dLat = (panitiaLat - userLat) * Math.PI / 180;
              let dLon = (panitiaLon - userLon) * Math.PI / 180;
              let a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                      Math.cos(userLat * Math.PI/180) * Math.cos(panitiaLat * Math.PI/180) *
                      Math.sin(dLon/2) * Math.sin(dLon/2);
              let c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
              let distance = R * c;

              let status = document.getElementById("statusLokasi");
              if (distance <= radius) {
                  status.innerText = "✅ Dalam radius (" + Math.round(distance) + " m)";
                  status.classList.remove("text-danger");
                  status.classList.add("text-success");
              } else {
                  status.innerText = "❌ Di luar radius (" + Math.round(distance) + " m)";
                  status.classList.remove("text-success");
                  status.classList.add("text-danger");
              }

              // ubah tombol
              let btn = document.getElementById("btnLokasi");
              btn.classList.remove("btn-warning");
              btn.classList.add("btn-success");
              btn.innerText = "✅ Lokasi Terdeteksi";

              // pindah map ke lokasi user
              map.setView([userLat, userLon], 16);
          });
      } else {
          Swal.fire("Error", "Geolocation tidak didukung browser ini!", "error");
      }
  }

  document.getElementById("btnLokasi").addEventListener("click", getLocation);

  // DataTables
  $(document).ready(function() {
      $('#absensiTable').DataTable();
  });

  document.getElementById("downloadPDF").addEventListener("click", () => {
      const { jsPDF } = window.jspdf;
      const doc = new jsPDF("l", "mm", "a4"); // landscape biar muat tabel

      // judul
      doc.setFontSize(16);
      doc.text("📋 Laporan Data Absensi", 14, 15);

      // info filter
      let filterInfo = "Filter: ";
      let acara = "<?= isset($_GET['acara']) ? $_GET['acara'] : 'Semua' ?>";
      let start = "<?= isset($_GET['start']) ? $_GET['start'] : '-' ?>";
      let end   = "<?= isset($_GET['end']) ? $_GET['end'] : '-' ?>";
      filterInfo += `Acara = ${acara}, Periode = ${start} s/d ${end}`;

      doc.setFontSize(11);
      doc.text(filterInfo, 14, 25);
      doc.text("Tanggal Export: " + new Date().toLocaleString(), 14, 32);

      // tabel
      doc.autoTable({
          html: "#absensiTable",
          startY: 38,
          styles: { fontSize: 9, halign: "center" },
          headStyles: { fillColor: [41, 128, 185] },
          theme: "grid"
      });

      doc.save("laporan_absensi.pdf");
  });

  document.getElementById("downloadExcel").addEventListener("click", () => {
      let table = document.getElementById("absensiTable");

      // ambil workbook
      let wb = XLSX.utils.book_new();

      // tambahkan sheet
      let ws = XLSX.utils.table_to_sheet(table);

      // info filter di atas
      XLSX.utils.sheet_add_aoa(ws, [
          ["📋 Laporan Data Absensi"],
          ["Filter: Acara = <?= isset($_GET['acara']) ? $_GET['acara'] : 'Semua' ?>, Periode = <?= isset($_GET['start']) ? $_GET['start'] : '-' ?> s/d <?= isset($_GET['end']) ? $_GET['end'] : '-' ?>"],
          ["Tanggal Export: " + new Date().toLocaleString()],
          []
      ], { origin: "A1" });

      XLSX.utils.book_append_sheet(wb, ws, "Absensi");
      XLSX.writeFile(wb, "laporan_absensi.xlsx");
  });

  // init map saat load
  window.onload = initMap;
  </script>
  </body>
  </html>


<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Absensi Siswa SMKN 9 Medan</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 40px;
            background: #f0f0f0;
        }
        form {
            background: white;
            padding: 20px;
            border-radius: 8px;
            max-width: 400px;
            margin: auto;
        }
        input, select, button {
            width: 100%;
            margin: 8px 0;
            padding: 10px;
        }
    </style>
</head>
<body>

<h2 style="text-align:center;">Form Absensi Siswa</h2>

<form id="absenForm">
    <input type="text" name="nama" placeholder="Nama Lengkap" required>
    <input type="text" name="kelas" placeholder="Kelas" required>
    <input type="text" name="event" placeholder="Kegiatan/Event" required>
    <input type="hidden" name="latitude">
    <input type="hidden" name="longitude">
    <button type="submit">Absen Sekarang</button>
</form>

<p id="status" style="text-align:center;"></p>

<script>
document.addEventListener("DOMContentLoaded", () => {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            (position) => {
                document.querySelector('input[name="latitude"]').value = position.coords.latitude;
                document.querySelector('input[name="longitude"]').value = position.coords.longitude;
            },
            (error) => {
                alert("Gagal mendapatkan lokasi. Aktifkan GPS dan izinkan akses lokasi.");
            }
        );
    } else {
        alert("Browser tidak mendukung Geolocation.");
    }

    document.getElementById("absenForm").addEventListener("submit", function (e) {
        e.preventDefault();
        const formData = new FormData(this);

        fetch("controller.php", {
            method: "POST",
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            document.getElementById("status").innerText = data.message;
            if (data.status === "success") {
                this.reset();
            }
        })
        .catch(err => {
            document.getElementById("status").innerText = "Terjadi kesalahan saat mengirim data.";
        });
    });
});
</script>

</body>
</html>

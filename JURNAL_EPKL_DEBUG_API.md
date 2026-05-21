# JURNAL E-PKL - DEBUGGING PROJECT KYPAY
**Periode: 10-11 Mei 2026**

---

## Kendala dan Solusi

Pada pengembangan project KyPay, terjadi kendala koneksi antara Flutter app dan backend Laravel yang menyebabkan request timeout. Awalnya diduga backend belum berjalan, namun setelah investigasi ditemukan bahwa backend berusaha berjalan di IP address `192.168.1.75:8000` yang ternyata tidak valid untuk PC backend itu sendiri. IP address tersebut merupakan IP dari Android device (emulator/physical device) yang akan menjalankan Flutter app.

Solusi dimulai dengan mengidentifikasi IP address yang benar menggunakan perintah `ipconfig`, dan ditemukan bahwa PC backend memiliki IP `192.168.1.74`. Backend kemudian dijalankan dengan perintah `php artisan serve --host 192.168.1.74 --port 8000`, dan server berhasil berjalan. Verifikasi dilakukan dengan melakukan test request ke endpoint `/api/register` menggunakan PowerShell Invoke-WebRequest, dan API merespons dengan HTTP 201 Created dalam waktu 1-8 detik. Konfigurasi CORS di file `config/cors.php` telah diverifikasi dan sudah tepat, dengan `allowed_origins: ['*']`, `allowed_methods: ['*']`, dan `allowed_headers: ['*']` yang memungkinkan request dari berbagai origin dan header.

Setelah backend siap, langkah berikutnya adalah memastikan Flutter app menggunakan IP backend yang benar. Flutter app perlu dikonfigurasi untuk mengakses `http://192.168.1.74:8000/api` bukan IP device-nya sendiri. Kedua device (PC backend dan Android device) berada dalam network segment yang sama (192.168.1.x) dengan gateway 192.168.1.1 dan subnet mask 255.255.255.0, sehingga komunikasi network seharusnya berjalan lancar. Testing dilakukan dengan curl dari Android device untuk memastikan konektivitas sebelum menjalankan Flutter app di device tersebut.

---

## Pembelajaran dan Best Practices

Dari debugging ini, diperoleh pembelajaran bahwa IP address harus diidentifikasi dengan akurat sesuai dengan interface network yang actual, tidak bisa hanya ditebak atau diasumsikan. Saat melakukan development dengan multiple devices, selalu penting untuk menverifikasi bahwa kedua device berada dalam network segment yang sama dan dapat saling berkomunikasi. Testing dilakukan secara bertahap: pertama test dari machine yang sama (backend machine), kemudian test cross-device setelah dipastikan backend sudah responsif. Tools yang digunakan selama debugging antara lain `ipconfig` untuk identifikasi network, `php artisan serve` untuk menjalankan development server, PowerShell Invoke-WebRequest untuk API testing, dan monitoring server logs untuk verifikasi bahwa request diterima dan diproses dengan benar.

---

**Jurnal disusun oleh:** [Nama Peserta PKL]  
**Pembimbing:** [Nama Pembimbing]  
**Tanggal Selesai:** 11 Mei 2026  
**Status:** ✅ Debugging Selesai - Backend Ready untuk Testing

---

**Jurnal disusun oleh:** [Nama Peserta PKL]  
**Pembimbing:** [Nama Pembimbing]  
**Tanggal Selesai:** 11 Mei 2026  
**Status:** ✅ Debugging Selesai - Backend Ready untuk Testing

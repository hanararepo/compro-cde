<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## Zona waktu

Tanggal pesan kontak, lowongan, lamaran, dan waktu pengiriman di admin serta email ditampilkan dalam WIB melalui `APP_DISPLAY_TIMEZONE=Asia/Jakarta`. Timestamp database tetap UTC agar data lama dan baru konsisten; tampilan otomatis mengonversi tanggal dan jam, termasuk saat melewati tengah malam. Tidak perlu mengubah timestamp data lama. Setelah mengganti konfigurasi, jalankan `php artisan config:clear`.

## Pengiriman email lamaran

Satu email hanya dapat memiliki satu lamaran pada lowongan yang sama, termasuk jika email pengirimannya gagal. Email yang sama tetap dapat melamar lowongan lain. Email dinormalisasi ke huruf kecil dan spasi di awal/akhir dihapus. Validasi formulir, pemeriksaan ulang dalam transaksi, dan unique index `(job_posting_id, email)` mencegah submit berulang maupun request yang bersamaan. Jika data lama berisi duplikat, migrasi berhenti tanpa menghapus data tersebut.

Lowongan memiliki dua pengaturan terpisah: `is_active` untuk Published/Unpublished, dan `is_closed` untuk Open/Closed. Pada Create/Edit Job Posting, pilih **Recruitment status â†’ Closed** untuk menutup lamaran sambil mempertahankan centang Publish. Lowongan tetap terlihat di publik dengan badge Closed, tetapi formulir dihilangkan dan backend menolak submit, termasuk formulir yang dibuka sebelum lowongan ditutup. Status Open dapat dipilih kembali untuk menerima lamaran. Lamaran yang sudah masuk tetap tersimpan.

Lamaran baru disimpan ke database, sedangkan CV disimpan sementara pada disk privat `local` di `cv-temporary`. Sistem langsung mengirim data pelamar dan lampiran CV ke alamat `CAREERS_MAIL_TO`. Setelah layanan email menerima pengiriman, status menjadi `sent`, file dihapus, dan `cv_path` dikosongkan. Nama asli lampiran tetap tersedia sebagai metadata.

Konfigurasi `.env` dengan `CAREERS_MAIL_TO` (email rekrutmen), `CAREERS_MAILER=smtp`, serta kredensial `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_SCHEME`, dan `MAIL_FROM_ADDRESS` sesuai penyedia email. Mailer `log`, `array`, dan mailer gabungan tidak diterima untuk lamaran agar simulasi email tidak menyebabkan penghapusan CV atau menulis isinya ke log.

Jalankan `php artisan migrate` dan `php artisan config:clear` setelah memperbarui konfigurasi. Pengiriman dan retry dilakukan langsung di backend dalam request submit yang sama, tanpa job, queue worker, atau scheduler. Timeout operasi SMTP dikonfigurasi melalui `MAIL_TIMEOUT` (default 10 detik); sesuaikan timeout PHP/web server untuk mengakomodasi pengiriman lampiran dan seluruh percobaan.

Jika layanan email gagal, backend langsung mencoba lagi, maksimal 3 percobaan total (1 pengiriman awal + 2 retry), lalu menyelesaikan request. Konfigurasi tidak valid atau CV hilang langsung menghentikan pengiriman karena retry dalam request yang sama tidak dapat memperbaikinya. Jika seluruh percobaan gagal, data pelamar dan CV tetap tersimpan, status menjadi `failed`, dan tidak ada retry otomatis setelah request selesai. Tidak ada tombol kirim ulang admin maupun command retry terjadwal.

Setelah email berhasil, penghapusan CV juga dicoba langsung maksimal 3 kali. Email yang sudah berhasil tidak dikirim ulang saat penghapusan gagal. Jika file tetap tidak bisa dihapus, path dipertahankan dan kegagalan dicatat di admin; tidak ada penghapusan terjadwal. Setiap lamaran memiliki nama file UUID dan lock per ID yang dipertahankan selama semua percobaan serta penghapusan.

Halaman Applications menampilkan status, jumlah percobaan, penerima, waktu pengiriman, dan pesan kegagalan tanpa kredensial SMTP. CV yang sudah terkirim tidak dapat diunduh dari admin. Lamaran sebelum migrasi ditandai `legacy` dan tidak dikirim atau dihapus otomatis. Kolom lama `email_next_attempt_at` dipertahankan untuk kompatibilitas database dan dikosongkan pada pengiriman baru; kolom itu tidak digunakan untuk menjadwalkan retry.

Email lamaran menggunakan warna hijau website CDE, dengan judul isi `Lamaran [APP_NAME]` dan subject `[APP_NAME] - [Posisi]`. Nama perusahaan mengikuti konfigurasi `app.name`. Isi memuat data pelamar, posisi, referensi lamaran, serta informasi CV terlampir. Reply-To diarahkan ke email pelamar; tersedia juga versi teks biasa.

Status `sent` berarti layanan email menerima pesan, bukan jaminan pesan sudah masuk inbox. Jika proses berhenti tepat setelah layanan menerima pesan tetapi sebelum status tersimpan, retry dapat menghasilkan duplikat; nomor lamaran dicantumkan di isi email untuk identifikasi.

Admin dapat mencari/filter pelamar, membuka detail, dan memilih beberapa lamaran untuk dihapus melalui modal konfirmasi. Checkbox pilih semua hanya memilih lamaran pada halaman pagination saat ini. Hapus massal memerlukan izin `career-applications.delete`, memvalidasi seluruh ID terhadap lowongan yang dibuka, dan menggunakan lock yang sama dengan pengiriman email. File CV dihapus sebelum record lamaran; kegagalan penghapusan mempertahankan record terkait dan menampilkan hasil parsial. Setiap penghapusan berhasil dicatat pada activity log.

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

In addition, [Laracasts](https://laracasts.com) contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

You can also watch bite-sized lessons with real-world projects on [Laravel Learn](https://laravel.com/learn), where you will be guided through building a Laravel application from scratch while learning PHP fundamentals.

## Agentic Development

Laravel's predictable structure and conventions make it ideal for AI coding agents like Claude Code, Cursor, and GitHub Copilot. Install [Laravel Boost](https://laravel.com/docs/ai) to supercharge your AI workflow:

```bash
composer require laravel/boost --dev

php artisan boost:install
```

Boost provides your agent 15+ tools and skills that help agents build Laravel applications while following best practices.

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

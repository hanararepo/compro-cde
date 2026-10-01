Lamaran {{ $companyName }}

Lamaran baru untuk posisi {{ $position }}.

Nama pelamar: {{ $application->name }}
Email: {{ $application->email }}
Telepon: {{ $application->phone }}
Tanggal melamar: {{ \App\Support\LocalTime::format($application->created_at, 'd M Y, H:i T') }}
Referensi lamaran: #{{ $application->id }}

CV terlampir: {{ $application->cv_original_name }}
Silakan tinjau CV terlampir untuk melanjutkan proses rekrutmen.

Balas email ini untuk menghubungi pelamar secara langsung.

{{ $companyName }}
Notifikasi rekrutmen dari website perusahaan.

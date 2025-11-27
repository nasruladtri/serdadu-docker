@extends('layouts.dukcapil', ['title' => 'Syarat & Ketentuan'])

@push('styles')
    <style>
        .terms-container {
            max-width: 900px;
            margin: 0 auto;
        }
        
        .terms-header {
            border-bottom: 3px solid #009B4D;
            padding-bottom: 1rem;
            margin-bottom: 2rem;
        }
        
        .terms-section {
            margin-bottom: 2.5rem;
        }
        
        .terms-section-title {
            color: #009B4D;
            font-weight: 700;
            font-size: 1.25rem;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #e5e7eb;
        }
        
        .terms-subsection {
            margin-bottom: 1.5rem;
        }
        
        .terms-subsection-title {
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.75rem;
        }
        
        .terms-list {
            list-style-type: decimal;
            padding-left: 1.5rem;
            margin-top: 0.5rem;
        }
        
        .terms-list li {
            margin-bottom: 0.5rem;
            line-height: 1.7;
        }
        
        .terms-list-nested {
            list-style-type: lower-alpha;
            padding-left: 1.5rem;
            margin-top: 0.5rem;
        }
        
        .terms-list-nested li {
            margin-bottom: 0.5rem;
        }
        
        .terms-intro {
            background-color: #f3f4f6;
            border-left: 4px solid #009B4D;
            padding: 1rem 1.5rem;
            margin-bottom: 2rem;
            border-radius: 0.375rem;
        }
        
        .terms-highlight {
            background-color: #fef3c7;
            padding: 0.125rem 0.375rem;
            border-radius: 0.25rem;
            font-weight: 500;
        }

        /* Dark mode adjustments */
        .dark .terms-header {
            border-color: #1e293b;
        }
        .dark .terms-section-title {
            color: #93c5fd;
            border-color: #1f2937;
        }
        .dark .terms-subsection-title {
            color: #e2e8f0;
        }
        .dark .terms-intro {
            background-color: #111827;
            border-left-color: #2563eb;
        }
        .dark .terms-highlight {
            background-color: #1f2937;
            color: #e2e8f0;
        }
        .dark .terms-list li,
        .dark .terms-list-nested li,
        .dark .terms-section p,
        .dark .terms-section strong,
        .dark .terms-intro p {
            color: #e2e8f0;
        }
        .dark .terms-header h1,
        .dark .terms-header h2,
        .dark .terms-header p {
            color: #e2e8f0;
        }
        .dark .dk-card {
            background-color: #0f172a;
            border-color: #1e293b;
        }
        .dark .terms-container .border-gray-200 {
            border-color: #1e293b;
        }
    </style>
@endpush

@section('content')
    <div class="dk-card animate-fade-in-up">
        <div class="p-6 md:p-8 terms-container">
            {{-- Header --}}
            <div class="terms-header text-center mb-6">
                <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-2">
                    SYARAT DAN KETENTUAN PENGGUNAAN
                </h1>
                <h2 class="text-xl md:text-2xl font-semibold text-gray-700 mb-1">
                    SISTEM REKAP DATA TERPADU KABUPATEN MADIUN
                </h2>
                <p class="text-lg text-gray-600">
                    DINAS KEPENDUDUKAN DAN PENCATATAN SIPIL KABUPATEN MADIUN
                </p>
            </div>

            {{-- Introduction --}}
            <div class="terms-intro">
                <p class="text-gray-700 leading-relaxed">
                    Syarat dan Ketentuan ini berlaku bagi setiap Pengguna yang mengakses dan/atau menggunakan Sistem Rekap Data Terpadu Kabupaten Madiun (selanjutnya disebut <span class="terms-highlight">"Layanan"</span>). Dengan mengakses Layanan, Pengguna menyatakan telah membaca, memahami, dan menyetujui seluruh isi Syarat dan Ketentuan ini. Apabila Pengguna tidak menyetujui sebagian atau seluruh ketentuan, Pengguna wajib menghentikan penggunaan Layanan.
                </p>
            </div>

            {{-- Section I: Definisi --}}
            <div class="terms-section animate-fade-in-up delay-100">
                <h2 class="terms-section-title">I. DEFINISI</h2>
                <div class="space-y-3 text-gray-700 leading-relaxed">
                    <p>
                        <strong class="text-gray-900">Layanan</strong> adalah aplikasi berbasis web yang menyediakan akses terhadap data agregat kependudukan Kabupaten Madiun.
                    </p>
                    <p>
                        <strong class="text-gray-900">Dinas Kependudukan dan Pencatatan Sipil Kabupaten Madiun</strong> (selanjutnya disebut <span class="terms-highlight">"Dinas"</span>) adalah instansi pemerintah daerah penyelenggara urusan administrasi kependudukan.
                    </p>
                    <p>
                        <strong class="text-gray-900">Pengguna</strong> adalah setiap pihak yang mengakses atau memanfaatkan Layanan, baik untuk melihat informasi maupun mengunduh data agregat.
                    </p>
                    <p>
                        <strong class="text-gray-900">Data Agregat Kependudukan</strong> adalah data kependudukan yang telah diringkas, dianalisis, atau diklasifikasikan sehingga tidak memuat identitas individu.
                    </p>
                    <p>
                        <strong class="text-gray-900">Konten</strong> mencakup seluruh informasi, tampilan, data, tabel, grafik, teks, gambar, fitur aplikasi, dan materi lain yang tersedia melalui Layanan.
                    </p>
                </div>
            </div>

            {{-- Section II: Akses dan Penggunaan Layanan --}}
            <div class="terms-section animate-fade-in-up delay-200">
                <h2 class="terms-section-title">II. AKSES DAN PENGGUNAAN LAYANAN</h2>
                <div class="space-y-4 text-gray-700 leading-relaxed">
                    <p>
                        Layanan disediakan untuk mendukung kebutuhan data publik dalam lingkup penelitian, perencanaan, penyusunan kebijakan, serta kepentingan non-komersial lainnya sesuai ketentuan peraturan perundang-undangan.
                    </p>
                    <p>
                        Pengguna diizinkan mengunduh Data Agregat Kependudukan melalui fitur yang tersedia pada Layanan.
                    </p>
                    <div class="terms-subsection">
                        <p class="terms-subsection-title">Sebelum mengunduh data, Pengguna wajib mengisi data identitas secara benar, yaitu:</p>
                        <ul class="terms-list">
                            <li>Nama Lengkap</li>
                            <li>Alamat</li>
                            <li>Pekerjaan</li>
                            <li>Instansi/Organisasi</li>
                            <li>Nomor Kontak</li>
                            <li>Tujuan Penggunaan Data</li>
                        </ul>
                    </div>
                    <p>
                        Pengguna bertanggung jawab atas keakuratan data yang diberikan. Pemberian informasi palsu, manipulatif, atau menyesatkan dapat dikenai konsekuensi hukum sesuai ketentuan perdata dan/atau pidana, termasuk Undang-Undang Nomor 11 Tahun 2008 tentang Informasi dan Transaksi Elektronik beserta perubahannya.
                    </p>
                    <div class="terms-subsection">
                        <p class="terms-subsection-title">Pengguna dilarang melakukan:</p>
                        <ul class="terms-list">
                            <li>penggunaan skrip otomatis, bot, atau perangkat serupa untuk mengunduh data;</li>
                            <li>perambanan (crawling/scraping) tanpa izin;</li>
                            <li>upaya merusak, mengubah, atau mengakses sistem di luar kewenangan;</li>
                            <li>segala bentuk tindakan yang dapat mengganggu integritas, keamanan, atau operasional Layanan.</li>
                        </ul>
                    </div>
                </div>
            </div>

            {{-- Section III: Pengelolaan Data Pribadi Pengguna --}}
            <div class="terms-section animate-fade-in-up delay-300">
                <h2 class="terms-section-title">III. PENGELOLAAN DATA PRIBADI PENGGUNA</h2>
                <div class="space-y-3 text-gray-700 leading-relaxed">
                    <p>
                        Dinas menjaga kerahasiaan data identitas Pengguna sesuai ketentuan peraturan perundang-undangan.
                    </p>
                    <p>
                        Data identitas Pengguna digunakan untuk kepentingan verifikasi, statistik internal, evaluasi layanan, dan dokumentasi administratif.
                    </p>
                    <p>
                        Dinas tidak memberikan data identitas Pengguna kepada pihak lain kecuali berdasarkan ketentuan hukum atau permintaan resmi lembaga berwenang.
                    </p>
                </div>
            </div>

            {{-- Section IV: Data Log dan Keamanan Sistem --}}
            <div class="terms-section animate-fade-in-up delay-400">
                <h2 class="terms-section-title">IV. DATA LOG DAN KEAMANAN SISTEM</h2>
                <div class="space-y-3 text-gray-700 leading-relaxed">
                    <p>
                        Sistem dapat merekam informasi akses Pengguna, termasuk alamat IP, waktu kunjungan, jenis perangkat, dan aktivitas penggunaan.
                    </p>
                    <p>
                        Data log digunakan untuk pemeliharaan, peningkatan mutu layanan, dan keamanan sistem.
                    </p>
                    <p>
                        Dinas tidak bertanggung jawab atas peretasan, penyalahgunaan akun, atau akses yang terjadi akibat kelalaian Pengguna dalam menjaga perangkat atau kerahasiaan aksesnya.
                    </p>
                </div>
            </div>

            {{-- Section V: Keadaan Kahar (Force Majeure) --}}
            <div class="terms-section animate-fade-in-up delay-500">
                <h2 class="terms-section-title">V. KEADAAN KAHAR (FORCE MAJEURE)</h2>
                <div class="space-y-3 text-gray-700 leading-relaxed">
                    <p>
                        Dalam hal terjadi keadaan di luar kendali Dinas, termasuk namun tidak terbatas pada:
                    </p>
                    <ul class="terms-list">
                        <li>bencana alam,</li>
                        <li>gangguan jaringan atau infrastruktur teknologi,</li>
                        <li>serangan siber,</li>
                        <li>kebijakan pemerintah,</li>
                        <li>atau keadaan lain yang menyebabkan terganggunya layanan,</li>
                    </ul>
                    <p>
                        Dinas tidak bertanggung jawab atas terhentinya, terhambatnya, atau berubahnya layanan. Pengguna dengan ini membebaskan Dinas dari tuntutan, klaim, atau bentuk tanggung jawab lainnya yang timbul akibat keadaan tersebut.
                    </p>
                </div>
            </div>

            {{-- Section VI: Perubahan Syarat dan Ketentuan --}}
            <div class="terms-section animate-fade-in-up delay-600">
                <h2 class="terms-section-title">VI. PERUBAHAN SYARAT DAN KETENTUAN</h2>
                <div class="text-gray-700 leading-relaxed">
                    <p>
                        Dinas berhak mengubah, memperbarui, atau menyesuaikan Syarat dan Ketentuan ini sewaktu-waktu. Pengguna diimbau untuk memeriksa pembaruan secara berkala. Penggunaan berkelanjutan atas Layanan dianggap sebagai persetujuan Pengguna terhadap perubahan tersebut.
                    </p>
                </div>
            </div>

            {{-- Section VII: Penutup --}}
            <div class="terms-section animate-fade-in-up delay-700">
                <h2 class="terms-section-title">VII. PENUTUP</h2>
                <div class="text-gray-700 leading-relaxed">
                    <p>
                        Dengan menggunakan Layanan, Pengguna menyatakan memahami dan menyetujui seluruh isi Syarat dan Ketentuan ini secara sah dan tanpa paksaan.
                    </p>
                </div>
            </div>

            {{-- Footer Note --}}
            <div class="mt-8 pt-6 border-t border-gray-200 text-center">
                <p class="text-sm text-gray-500">
                    Dokumen ini berlaku sejak {{ config('app.terms_effective_date', '23 November 2025') }}
                </p>
                <p class="text-sm text-gray-500 mt-2">
                    Dinas Kependudukan dan Pencatatan Sipil Kabupaten Madiun
                </p>
            </div>
        </div>
    </div>
@endsection


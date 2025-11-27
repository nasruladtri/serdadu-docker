@push('styles')
    <style>
        .admin-landing-panel {
            border: 1px solid #e2e8f0;
            box-shadow: 0 20px 45px rgba(15, 118, 110, 0.05);
        }

        .admin-pill {
            border: 1px solid #e2e8f0;
            background: linear-gradient(180deg, #f9fafb 0%, #f3f4f6 100%);
        }

        .year-input::-webkit-inner-spin-button,
        .year-input::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        .year-input {
            -moz-appearance: textfield;
        }
    </style>
@endpush

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-start justify-between gap-6">
            <div class="space-y-3">
                <p class="text-xs font-semibold uppercase tracking-[0.35em] text-[#009B4D]">Administrasi Dataset</p>
                <div>
                    <h2 class="text-2xl sm:text-3xl font-semibold text-slate-900">
                        Impor Data Dukcapil (Satu File .xlsx)
                    </h2>
                    <p class="text-sm text-slate-500 max-w-2xl">
                        Gunakan berkas rekap resmi dengan banyak sheet. Sistem akan melakukan validasi struktur dan menyiapkan data publik otomatis.
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-3 rounded-2xl admin-pill px-4 py-3">
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white text-[#009B4D] border border-gray-200">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v8m4-4H8"/>
                    </svg>
                </div>
                <div>
                    <p class="text-[0.65rem] uppercase tracking-[0.3em] text-gray-400">Status</p>
                    <p class="text-sm font-semibold text-slate-900">
                        {{ session('status') ? 'Sinkronisasi Diperbarui' : 'Menunggu unggahan' }}
                    </p>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <section class="bg-white rounded-2xl admin-landing-panel p-6 sm:p-8 space-y-6">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <p class="text-lg font-semibold text-slate-900">Impor Dataset Terbaru</p>
                        <p class="text-sm text-slate-500">Pastikan periode yang dipilih sesuai dengan data pada dashboard publik Serdadu.</p>
                    </div>
                    <form method="POST" action="{{ route('import.reset') }}"
                          onsubmit="return confirm('Semua tabel hasil impor dan file unggahan akan dihapus. Lanjutkan?');">
                        @csrf
                        <button type="submit"
                                class="inline-flex items-center gap-2 rounded-full border border-red-200 bg-white px-4 py-2 text-sm font-semibold text-red-600 transition hover:bg-red-50">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6h18M9 6v12m6-12v12M4 6l1 14h14l1-14"/>
                            </svg>
                            Bersihkan Data Impor
                        </button>
                    </form>
                </div>

                @if (session('status'))
                    <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900 flex items-start gap-3">
                        <svg class="h-5 w-5 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span>{{ session('status') }}</span>
                    </div>
                @endif

                <form method="POST" action="{{ route('import.store') }}" enctype="multipart/form-data" class="space-y-6">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Tahun --}}
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-slate-700">Tahun</label>
                            <input
                                type="number"
                                name="year"
                                min="2000"
                                max="2100"
                                inputmode="numeric"
                                pattern="\d{4}"
                                required
                                value="{{ old('year') }}"
                                class="year-input block w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-slate-900 focus:border-[#009B4D] focus:ring-[#009B4D]"
                            >
                            <p class="text-xs text-slate-500">Masukkan tahun rilis dataset (4 digit).</p>
                            @error('year') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
                        </div>

                        {{-- Semester --}}
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-slate-700">Semester</label>
                            <select
                                name="semester"
                                required
                                class="block w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-slate-900 focus:border-[#009B4D] focus:ring-[#009B4D]"
                            >
                                <option value="">-- pilih --</option>
                                <option value="1" {{ old('semester')=='1'?'selected':'' }}>1 (Januari–Juni)</option>
                                <option value="2" {{ old('semester')=='2'?'selected':'' }}>2 (Juli–Desember)</option>
                            </select>
                            @error('semester') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    {{-- File --}}
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-slate-700">File Excel (.xlsx)</label>
                        <div class="rounded-2xl border border-dashed border-slate-300 bg-gray-50 px-4 py-6">
                            <input
                                type="file"
                                name="file"
                                accept=".xlsx"
                                required
                                class="block w-full text-sm text-slate-600 file:mr-4 file:rounded-full file:border-0 file:bg-emerald-100 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-emerald-700 hover:file:bg-emerald-200"
                            />
                            <p class="text-xs text-slate-500 mt-2">
                                Gunakan file rekap tunggal (berisi banyak sheet). Format nama sheet mengikuti template resmi Dukcapil.
                            </p>
                            @error('file') <div class="text-red-600 text-sm mt-2">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <p class="text-xs text-slate-500">Setelah proses selesai, statistik publik akan diperbarui otomatis.</p>
                        <button type="submit"
                                class="inline-flex items-center gap-2 rounded-full bg-[#009B4D] px-6 py-3 text-sm font-semibold text-white shadow-sm hover:bg-[#007a3d] transition">
                            <img src="{{ asset('img/upload.png') }}" alt="Upload" class="h-4 w-4 object-contain">
                            Unggah &amp; Proses
                        </button>
                    </div>
                </form>
            </section>

            <section class="grid gap-6 lg:grid-cols-2">
                <div class="bg-white rounded-2xl admin-landing-panel p-6 space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="h-10 w-10 rounded-2xl bg-emerald-50 text-emerald-700 flex items-center justify-center font-semibold">01</div>
                        <div>
                            <p class="text-sm font-semibold text-slate-900">Panduan Impor Teknis</p>
                            <p class="text-xs text-slate-500">Checklist wajib sebelum menekan tombol unggah.</p>
                        </div>
                    </div>
                    <ul class="space-y-3 text-sm text-slate-600 list-disc ms-5">
                        <li>Pastikan workbook memuat sheet wajib sesuai template (mis. <code>GENDER</code>, <code>AGE_GROUP</code>, <code>RELIGION</code>) dengan header baris pertama yang identik huruf-per-huruf.</li>
                        <li>Setiap kolom numerik harus bertipe angka (General/Number) tanpa pemisah ribuan maupun simbol lain untuk menghindari parsing error.</li>
                        <li>Tombol <strong>Bersihkan Data Impor</strong> akan mengosongkan tabel staging (<code>import_batches</code>, <code>import_rows</code>, dll.) — jalankan sebelum upload baru.</li>
                        <li>Gunakan penamaan file <code>dukcapil-TAHUN-SMT.xlsx</code> dan simpan backup asli di storage internal.</li>
                    </ul>
                </div>

                <div class="bg-white rounded-2xl admin-landing-panel p-6 space-y-4">
                    <div class="flex items-center gap-3">
                        <svg class="h-10 w-10 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div>
                            <p class="text-sm font-semibold text-amber-900">Catatan Keamanan</p>
                            <p class="text-xs text-amber-700">Unggah hanya dari jaringan internal Dukcapil.</p>
                        </div>
                    </div>
                    <p class="text-sm text-amber-700">
                        Proses impor menggantikan dataset publik. Jangan menutup browser sebelum notifikasi selesai. Jika koneksi terputus, ulangi prosedur dari awal untuk menghindari data parsial.
                    </p>
                </div>
            </section>
        </div>
    </div>
</x-app-layout>

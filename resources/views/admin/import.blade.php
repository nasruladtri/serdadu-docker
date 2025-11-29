@extends('layouts.admin', ['title' => 'Import Data'])

@push('styles')
    <style>
        .admin-landing-panel {
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
            transition: all 0.3s ease;
        }
        
        .admin-landing-panel:hover {
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -2px rgba(0, 0, 0, 0.025);
        }

        .upload-zone {
            background-image: url("data:image/svg+xml,%3csvg width='100%25' height='100%25' xmlns='http://www.w3.org/2000/svg'%3e%3crect width='100%25' height='100%25' fill='none' rx='16' ry='16' stroke='%23CBD5E1FF' stroke-width='2' stroke-dasharray='12%2c 12' stroke-dashoffset='0' stroke-linecap='square'/%3e%3c/svg%3e");
            transition: all 0.3s ease;
        }

        .dark .upload-zone {
            background-image: url("data:image/svg+xml,%3csvg width='100%25' height='100%25' xmlns='http://www.w3.org/2000/svg'%3e%3crect width='100%25' height='100%25' fill='none' rx='16' ry='16' stroke='%23475569FF' stroke-width='2' stroke-dasharray='12%2c 12' stroke-dashoffset='0' stroke-linecap='square'/%3e%3c/svg%3e");
        }

        .upload-zone:hover, .upload-zone.dragover {
            background-image: url("data:image/svg+xml,%3csvg width='100%25' height='100%25' xmlns='http://www.w3.org/2000/svg'%3e%3crect width='100%25' height='100%25' fill='none' rx='16' ry='16' stroke='%23009B4DFF' stroke-width='2' stroke-dasharray='12%2c 12' stroke-dashoffset='0' stroke-linecap='square'/%3e%3c/svg%3e");
            background-color: #F0FDF4;
        }

        .dark .upload-zone:hover, .dark .upload-zone.dragover {
            background-color: rgba(6, 78, 59, 0.3);
        }

        .year-input::-webkit-inner-spin-button,
        .year-input::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        @keyframes progress-indeterminate {
            0% { width: 0%; margin-left: 0%; }
            50% { width: 30%; margin-left: 35%; }
            100% { width: 0%; margin-left: 100%; }
        }
        .animate-progress {
            animation: progress-indeterminate 2s infinite ease-in-out;
        }

        /* Excel Sheet Tabs */
        .sheet-tab {
            position: relative;
            padding: 8px 16px;
            background: #f1f5f9;
            border: 1px solid #cbd5e1;
            border-bottom: none;
            border-radius: 6px 6px 0 0;
            cursor: pointer;
            transition: all 0.2s;
        }
        .dark .sheet-tab {
            background: #1e293b;
            border-color: #475569;
        }
        .sheet-tab.active {
            background: white;
            border-color: #cbd5e1;
            border-bottom-color: white;
            z-index: 2;
        }
        .dark .sheet-tab.active {
            background: #0f172a;
            border-bottom-color: #0f172a;
        }
        .sheet-content {
            display: none;
        }
        .sheet-content.active {
            display: block;
        }
    </style>
@endpush

@section('content')
    <div class="space-y-8 animate-fade-in-up">
        <!-- Header Section -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="space-y-2">
                <div class="flex items-center gap-2">
                    <span class="px-3 py-1 rounded-full bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 text-xs font-bold tracking-wider uppercase">
                        Administrasi Dataset
                    </span>
                </div>


            </div>


        </div>

        @if(isset($summary) && !empty($summary))
            <!-- Import Result Section -->
            <div class="space-y-6 animate-fade-in-up">
                <div class="bg-white dark:bg-slate-800 rounded-3xl p-8 border border-slate-200 dark:border-slate-700 shadow-lg">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-xl font-bold text-slate-900 dark:text-white flex items-center gap-2">
                            <svg class="w-6 h-6 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Ringkasan Impor: {{ $filename ?? 'File Terunggah' }}
                        </h2>
                        <a href="{{ route('admin.import') }}" class="text-sm text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300 font-medium flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            Upload File Lain
                        </a>
                    </div>

                    <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-700">
                        <table class="min-w-full text-sm divide-y divide-slate-200 dark:divide-slate-700">
                            <thead class="bg-slate-50 dark:bg-slate-900/50">
                                <tr>
                                    <th class="text-left px-6 py-3 font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Sheet</th>
                                    <th class="text-left px-6 py-3 font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Status</th>
                                    <th class="text-left px-6 py-3 font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Rows OK</th>
                                    <th class="text-left px-6 py-3 font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Rows Gagal</th>
                                    <th class="text-left px-6 py-3 font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Catatan</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-slate-800 divide-y divide-slate-200 dark:divide-slate-700">
                            @foreach($summary as [$sheet,$status,$ok,$fail,$errs])
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                                    <td class="px-6 py-4 font-medium text-slate-900 dark:text-slate-200">{{ $sheet }}</td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wide
                                          {{ $status==='success' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400' : ($status==='partial' ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400' : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400') }}">
                            {{ $status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-slate-500 dark:text-slate-400 font-mono">{{ $ok }}</td>
                                    <td class="px-6 py-4 text-slate-500 dark:text-slate-400 font-mono">{{ $fail }}</td>
                                    <td class="px-6 py-4 text-slate-500 dark:text-slate-400">
                                        @if($errs)
                                            <details class="group">
                                                <summary class="cursor-pointer text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 font-medium list-none flex items-center gap-1">
                                                    <span>Lihat Error</span>
                                                    <svg class="h-4 w-4 transition-transform group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                                    </svg>
                                                </summary>
                                                <ul class="mt-2 list-disc list-inside text-xs text-red-600 dark:text-red-400 space-y-1 bg-red-50 dark:bg-red-900/20 p-3 rounded-lg">
                                                    @foreach($errs as $e)<li>{{ $e }}</li>@endforeach
                                                </ul>
                                            </details>
                                        @else
                                            <span class="text-slate-300 dark:text-slate-600">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @else
            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <!-- Left Column: Upload Form -->
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white dark:bg-slate-800 rounded-3xl p-8 border border-slate-200 dark:border-slate-700 shadow-lg relative overflow-hidden">
                        <!-- Decorative Background -->
                        <div class="absolute top-0 right-0 -mt-16 -mr-16 w-64 h-64 bg-emerald-50 dark:bg-emerald-900/20 rounded-full blur-3xl opacity-50 pointer-events-none"></div>

                        <div class="relative">
                            <div class="flex items-center justify-between mb-8">
                                <h2 class="text-xl font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                    <svg class="w-6 h-6 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                    </svg>
                                    Upload Dataset Baru
                                </h2>
                                
                                <form method="POST" action="{{ route('import.reset') }}" onsubmit="return confirm('Tindakan ini akan menghapus semua data sementara. Lanjutkan?');">
                                    @csrf
                                    <button type="submit" class="text-sm text-red-500 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 font-medium flex items-center gap-1 transition-colors px-3 py-1.5 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                        Reset Data
                                    </button>
                                </form>
                            </div>

                            @if (session('status'))
                                <div class="mb-6 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 p-4 flex items-start gap-3 animate-fade-in">
                                    <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    <div>
                                        <h4 class="text-sm font-bold text-emerald-800 dark:text-emerald-300">Sukses!</h4>
                                        <p class="text-sm text-emerald-700 dark:text-emerald-400 mt-1">{{ session('status') }}</p>
                                    </div>
                                </div>
                            @endif

                            <!-- Progress Bar (Hidden by default) -->
                            <div id="progress-container" class="hidden mb-8 space-y-3">
                                <div class="flex justify-between text-sm font-medium text-slate-700 dark:text-slate-300">
                                    <span>Memproses Data...</span>
                                    <span id="progress-percentage">0%</span>
                                </div>
                                <div class="w-full bg-slate-100 dark:bg-slate-700 rounded-full h-4 overflow-hidden">
                                    <div id="progress-bar" class="bg-emerald-500 h-4 rounded-full transition-all duration-300 animate-progress" style="width: 0%"></div>
                                </div>
                                <p class="text-xs text-slate-500 dark:text-slate-400 text-center animate-pulse">Mohon tunggu, jangan tutup halaman ini.</p>
                            </div>

                            <form id="import-form" method="POST" action="{{ route('import.store') }}" enctype="multipart/form-data" class="space-y-8">
                                @csrf

                                <!-- Period Selection -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="space-y-2">
                                        <label class="text-sm font-semibold text-slate-700 dark:text-slate-300 ml-1">Tahun Data</label>
                                        <div class="relative">
                                            <input type="number" name="year" min="2000" max="2100" required value="{{ old('year') }}" 
                                                class="year-input block w-full rounded-xl border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-900 px-4 py-3 text-slate-900 dark:text-white focus:border-emerald-500 focus:ring-emerald-500 focus:bg-white dark:focus:bg-slate-800 transition-all font-medium"
                                                placeholder="Contoh: 2024">
                                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-slate-400">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                </svg>
                                            </div>
                                        </div>
                                        @error('year') <p class="text-red-500 text-xs mt-1 ml-1">{{ $message }}</p> @enderror
                                    </div>

                                    <div class="space-y-2">
                                        <label class="text-sm font-semibold text-slate-700 dark:text-slate-300 ml-1">Semester</label>
                                        <div class="relative">
                                            <select name="semester" required class="block w-full rounded-xl border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-900 px-4 py-3 text-slate-900 dark:text-white focus:border-emerald-500 focus:ring-emerald-500 focus:bg-white dark:focus:bg-slate-800 transition-all font-medium appearance-none">
                                                <option value="">Pilih Semester</option>
                                                <option value="1" {{ old('semester')=='1'?'selected':'' }}>Semester 1 (Jan - Jun)</option>
                                                <option value="2" {{ old('semester')=='2'?'selected':'' }}>Semester 2 (Jul - Des)</option>
                                            </select>
                                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-slate-400">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                                </svg>
                                            </div>
                                        </div>
                                        @error('semester') <p class="text-red-500 text-xs mt-1 ml-1">{{ $message }}</p> @enderror
                                    </div>
                                </div>

                                <!-- Drag & Drop Zone -->
                                <div class="space-y-2">
                                    <label class="text-sm font-semibold text-slate-700 dark:text-slate-300 ml-1">File Excel (.xlsx)</label>
                                    <div class="upload-zone rounded-3xl p-8 text-center cursor-pointer group relative" id="drop-zone">
                                        <input type="file" name="file" accept=".xlsx" required class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" id="file-input">
                                        
                                        <div class="space-y-4 pointer-events-none">
                                            <div class="w-16 h-16 mx-auto bg-emerald-50 dark:bg-emerald-900/20 rounded-2xl flex items-center justify-center text-emerald-600 dark:text-emerald-400 group-hover:scale-110 transition-transform duration-300">
                                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                                </svg>
                                            </div>
                                            <div>
                                                <p class="text-slate-900 dark:text-white font-medium text-lg">Klik atau seret file ke sini</p>
                                                <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">Format wajib .xlsx (Excel Workbook)</p>
                                            </div>
                                            <div id="file-name" class="hidden inline-flex items-center gap-2 px-4 py-2 bg-emerald-100 dark:bg-emerald-900/40 text-emerald-800 dark:text-emerald-300 rounded-lg text-sm font-medium">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                                <span class="truncate max-w-[200px]">filename.xlsx</span>
                                            </div>
                                        </div>
                                    </div>
                                    @error('file') <p class="text-red-500 text-xs mt-1 ml-1">{{ $message }}</p> @enderror
                                </div>

                                <!-- Submit Button -->
                                <div class="pt-4">
                                    <button type="submit" class="w-full bg-[#009B4D] hover:bg-[#007a3d] text-white font-bold py-4 px-6 rounded-2xl shadow-lg shadow-emerald-200 hover:shadow-emerald-300 transform hover:-translate-y-0.5 transition-all duration-200 flex items-center justify-center gap-3">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                        </svg>
                                        Mulai Proses Impor
                                    </button>
                                    <p class="text-center text-slate-400 dark:text-slate-500 text-xs mt-4">
                                        Pastikan data sudah benar sebelum memproses. Tindakan ini tidak dapat dibatalkan.
                                    </p>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Excel-like Format Example Section (Full Width Below) -->
            <div class="mt-8 bg-white dark:bg-slate-800 rounded-3xl border border-slate-200 dark:border-slate-700 shadow-lg overflow-hidden">
                <div class="p-6 border-b border-slate-200 dark:border-slate-700">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-slate-900 dark:text-white">Contoh Format File Excel</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">File harus memiliki 8 sheet dengan header berikut</p>
                        </div>
                    </div>
                </div>

                <!-- Excel Content Area -->
                <div class="bg-slate-50 dark:bg-slate-900/50">
                    <!-- Sheet Content -->
                    <div class="overflow-x-auto">
                        <!-- Sheet1 -->
                        <div id="sheet1-content" class="sheet-content-excel active min-h-[300px] p-4">
                            <table class="min-w-full text-xs border-collapse">
                                <thead>
                                    <tr class="bg-emerald-50 dark:bg-emerald-900/20">
                                        <th class="border border-slate-300 dark:border-slate-600 px-3 py-2 text-left font-semibold">IDEM</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-3 py-2 text-left font-semibold">KODE</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-3 py-2 text-left font-semibold">WILAYAH</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-3 py-2 text-left font-semibold">0-4 L</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-3 py-2 text-left font-semibold">0-4 P</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-3 py-2 text-left font-semibold">0-4</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-3 py-2 text-left font-semibold">5-9 L</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-3 py-2 text-left font-semibold">5-9 P</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-3 py-2 text-left font-semibold">5-9</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-3 py-2 text-left font-semibold">10-14 L</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-3 py-2 text-left font-semibold">10-14 P</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-3 py-2 text-left font-semibold">10-14</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-3 py-2 text-left font-semibold">...</th>
                                    </tr>
                                </thead>
                            </table>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-2 px-3">Total 120 kolom kelompok umur (0-4, 5-9, 10-14, ... hingga 75+), masing-masing dengan L (Laki-laki), P (Perempuan), dan Total</p>
                        </div>

                        <!-- Sheet1_UMUR -->
                        <div id="sheet1_umur-content" class="sheet-content-excel min-h-[300px] p-4 hidden">
                            <table class="min-w-full text-xs border-collapse">
                                <thead>
                                    <tr class="bg-emerald-50 dark:bg-emerald-900/20">
                                        <th class="border border-slate-300 dark:border-slate-600 px-3 py-2 text-left font-semibold">IDEM</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-3 py-2 text-left font-semibold">KODE</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-3 py-2 text-left font-semibold">WILAYAH</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-3 py-2 text-left font-semibold">JML 0</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-3 py-2 text-left font-semibold">JML 1</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-3 py-2 text-left font-semibold">JML 2</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-3 py-2 text-left font-semibold">JML 3</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-3 py-2 text-left font-semibold">JML 4</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-3 py-2 text-left font-semibold">JML 5</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-3 py-2 text-left font-semibold">...</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-3 py-2 text-left font-semibold">JML 120</th>
                                    </tr>
                                </thead>
                            </table>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-2 px-3">Umur tunggal dari 0 hingga 120 tahun</p>
                        </div>

                        <!-- Sheet3 (Pendidikan) -->
                        <div id="sheet3-content" class="sheet-content-excel min-h-[300px] p-4 hidden">
                            <table class="min-w-full text-xs border-collapse">
                                <thead>
                                    <tr class="bg-emerald-50 dark:bg-emerald-900/20">
                                        <th class="border border-slate-300 dark:border-slate-600 px-3 py-2 text-left font-semibold">IDEM</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-3 py-2 text-left font-semibold">KODE</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-3 py-2 text-left font-semibold">WILAYAH</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-3 py-2 text-left font-semibold">TIDAK/BELUM SEKOLAH_L</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-3 py-2 text-left font-semibold">TIDAK/BELUM SEKOLAH_P</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-3 py-2 text-left font-semibold">TIDAK/BELUM SEKOLAH</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-3 py-2 text-left font-semibold">BELUM TAMAT SD/SEDERAJAT_L</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-3 py-2 text-left font-semibold">BELUM TAMAT SD/SEDERAJAT_P</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-3 py-2 text-left font-semibold">BELUM TAMAT SD/SEDERAJAT</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-3 py-2 text-left font-semibold">TAMAT SD/SEDERAJAT_L</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-3 py-2 text-left font-semibold">TAMAT SD/SEDERAJAT_P</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-3 py-2 text-left font-semibold">...</th>
                                    </tr>
                                </thead>
                            </table>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-2 px-3">Kolom: TIDAK/BELUM SEKOLAH, BELUM TAMAT SD, TAMAT SD, SLTP, SLTA, DIPLOMA I/II, DIPLOMA III, DIPLOMA IV/STRATA I, STRATA II, STRATA III (masing-masing _L, _P, dan Total)</p>
                        </div>

                        <!-- Sheet4 (Pekerjaan) -->
                        <div id="sheet4-content" class="sheet-content-excel min-h-[300px] p-4 hidden">
                            <table class="min-w-full text-xs border-collapse">
                                <thead>
                                    <tr class="bg-emerald-50 dark:bg-emerald-900/20">
                                        <th class="border border-slate-300 dark:border-slate-600 px-3 py-2 text-left font-semibold">NO_PROP</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-3 py-2 text-left font-semibold">NAMA_PROP</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-3 py-2 text-left font-semibold">NO_KAB</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-3 py-2 text-left font-semibold">NAMA_KAB</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-3 py-2 text-left font-semibold">NO_KEC</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-3 py-2 text-left font-semibold">NAMA_KEC</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-3 py-2 text-left font-semibold">NO_KEL</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-3 py-2 text-left font-semibold">NAMA_KEL</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-3 py-2 text-left font-semibold">BELUM/TIDAK BEKERJA_L</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-3 py-2 text-left font-semibold">BELUM/TIDAK BEKERJA_P</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-3 py-2 text-left font-semibold">MENGURUS RUMAH TANGGA_L</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-3 py-2 text-left font-semibold">...</th>
                                    </tr>
                                </thead>
                            </table>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-2 px-3">Banyak kolom pekerjaan: BELUM/TIDAK BEKERJA, MENGURUS RUMAH TANGGA, PELAJAR/MAHASISWA, PENSIUNAN, PNS, TNI, POLRI, PERDAGANGAN, PETANI/PEKEBUN, PETERNAK, NELAYAN, INDUSTRI, KONSTRUKSI, TRANSPORTASI, KARYAWAN SWASTA/BUMN/BUMD/HONORER, BURUH, TUKANG (CUKUR/LISTRIK/BATU/KAYU/SOL SEPATU/LAS/JAHIT/GIGI), PENATA (RIAS/BUSANA/RAMBUT), MEKANIK, SENIMAN, TABIB, PARAJI, PERANCANG BUSANA, PENTERJEMAH, IMAM MASJID, PENDETA, PASTOR, WARTAWAN, USTADZ, JURU MASAK, PROMOTOR ACARA, ANGGOTA DPR/DPD/BPK, PRESIDEN, WAKIL PRESIDEN, ANGGOTA MAHKAMAH KONSTITUSI, ANGGOTA KABINET, DUTA BESAR, GUBERNUR, WAKIL GUBERNUR, BUPATI, WAKIL BUPATI, WALIKOTA, WAKIL WALIKOTA, ANGGOTA DPRD, DOSEN, GURU, PILOT, PENGACARA, NOTARIS, ARSITEK, AKUNTAN, KONSULTAN, DOKTER, BIDAN, PERAWAT, APOTEKER, PSIKIATER/PSIKOLOG, PENYIAR TV/RADIO, PELAUT, PENELITI, SOPIR, PIALANG, PARANORMAL, PEDAGANG, PERANGKAT DESA, KEPALA DESA, BIARAWAN/BIARAWATI, WIRASWASTA, ARTIS, ATLIT, CHEFF, MANAJER, TENAGA TATA USAHA, OPERATOR, PEKERJA PENGOLAHAN KERAJINAN, TEKNISI, ASISTEN AHLI, PEKERJAAN LAINNYA (masing-masing _L dan _P)</p>
                        </div>

                        <!-- Sheet10 (Perkawinan) -->
                        <div id="sheet10-content" class="sheet-content-excel min-h-[300px] p-4 hidden">
                            <table class="min-w-full text-xs border-collapse">
                                <thead>
                                    <tr class="bg-emerald-50 dark:bg-emerald-900/20">
                                        <th class="border border-slate-300 dark:border-slate-600 px-3 py-2 text-left font-semibold">NO_PROP</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-3 py-2 text-left font-semibold">NAMA_PROP</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-3 py-2 text-left font-semibold">NO_KAB</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-3 py-2 text-left font-semibold">NAMA_KAB</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-3 py-2 text-left font-semibold">NO_KEC</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-3 py-2 text-left font-semibold">NAMA_KEC</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-3 py-2 text-left font-semibold">NO_KEL</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-3 py-2 text-left font-semibold">NAMA_KEL</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-3 py-2 text-left font-semibold">LK_BLM_KAWIN</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-3 py-2 text-left font-semibold">PR_BLM_KWN</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-3 py-2 text-left font-semibold">LK_KAWIN</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-3 py-2 text-left font-semibold">PR_KWN</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-3 py-2 text-left font-semibold">LK_CERAI_HIDUP</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-3 py-2 text-left font-semibold">PR_CERAI_HIDUP</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-3 py-2 text-left font-semibold">LK_CERAI_MATI</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-3 py-2 text-left font-semibold">PR_CERAI_MATI</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>

                        <!-- Sheet5 (Kepala Keluarga berdasarkan Status Perkawinan) -->
                        <div id="sheet5-content" class="sheet-content-excel min-h-[300px] p-4 hidden">
                            <table class="min-w-full text-xs border-collapse">
                                <thead>
                                    <tr class="bg-emerald-50 dark:bg-emerald-900/20">
                                        <th class="border border-slate-300 dark:border-slate-600 px-3 py-2 text-left font-semibold">NO_PROP</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-3 py-2 text-left font-semibold">NAMA_PROP</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-3 py-2 text-left font-semibold">NO_KAB</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-3 py-2 text-left font-semibold">NAMA_KAB</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-3 py-2 text-left font-semibold">NO_KEC</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-3 py-2 text-left font-semibold">NAMA_KEC</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-3 py-2 text-left font-semibold">NO_KEL</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-3 py-2 text-left font-semibold">NAMA_KEL</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-3 py-2 text-left font-semibold">'BELUM KAWIN'_L</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-3 py-2 text-left font-semibold">'BELUM KAWIN'_P</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-3 py-2 text-left font-semibold">'BELUM KAWIN'_JML</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-3 py-2 text-left font-semibold">'KAWIN'_L</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-3 py-2 text-left font-semibold">'KAWIN'_P</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-3 py-2 text-left font-semibold">'KAWIN'_JML</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-3 py-2 text-left font-semibold">'CERAI HIDUP'_L</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-3 py-2 text-left font-semibold">'CERAI HIDUP'_P</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-3 py-2 text-left font-semibold">'CERAI HIDUP'_JML</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-3 py-2 text-left font-semibold">'CERAI MATI'_L</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-3 py-2 text-left font-semibold">'CERAI MATI'_P</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-3 py-2 text-left font-semibold">'CERAI MATI'_JML</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>

                        <!-- Sheet7 (Agama) -->
                        <div id="sheet7-content" class="sheet-content-excel min-h-[300px] p-4 hidden">
                            <table class="min-w-full text-xs border-collapse">
                                <thead>
                                    <tr class="bg-emerald-50 dark:bg-emerald-900/20">
                                        <th class="border border-slate-300 dark:border-slate-600 px-3 py-2 text-left font-semibold">KODE</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-3 py-2 text-left font-semibold">WILAYAH</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-3 py-2 text-left font-semibold">ISLAM L</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-3 py-2 text-left font-semibold">ISLAM P</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-3 py-2 text-left font-semibold">KRISTEN L</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-3 py-2 text-left font-semibold">KRISTEN P</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-3 py-2 text-left font-semibold">KATHOLIK L</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-3 py-2 text-left font-semibold">KATHOLIK P</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-3 py-2 text-left font-semibold">HINDU L</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-3 py-2 text-left font-semibold">HINDU P</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-3 py-2 text-left font-semibold">BUDHA L</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-3 py-2 text-left font-semibold">BUDHA P</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-3 py-2 text-left font-semibold">KHONGHUCU L</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-3 py-2 text-left font-semibold">KHONGHUCU P</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-3 py-2 text-left font-semibold">KEPERCAYAAN L</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-3 py-2 text-left font-semibold">KEPERCAYAAN P</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>

                        <!-- Sheet6(2) (Wajib KTP) -->
                        <div id="sheet6_2-content" class="sheet-content-excel min-h-[300px] p-4 hidden">
                            <table class="min-w-full text-xs border-collapse">
                                <thead>
                                    <tr class="bg-emerald-50 dark:bg-emerald-900/20">
                                        <th class="border border-slate-300 dark:border-slate-600 px-3 py-2 text-left font-semibold">NO_PROP</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-3 py-2 text-left font-semibold">NAMA_PROP</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-3 py-2 text-left font-semibold">NO_KAB</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-3 py-2 text-left font-semibold">NAMA_KAB</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-3 py-2 text-left font-semibold">NO_KEC</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-3 py-2 text-left font-semibold">NAMA_KEC</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-3 py-2 text-left font-semibold">NO_KEL</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-3 py-2 text-left font-semibold">NAMA_KEL</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-3 py-2 text-left font-semibold">WKTP_LK</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-3 py-2 text-left font-semibold">WKTP_PR</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-3 py-2 text-left font-semibold">WKTP</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-3 py-2 text-left font-semibold">REKAM_LK</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-3 py-2 text-left font-semibold">REKAM_PR</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-3 py-2 text-left font-semibold">REKAM</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-3 py-2 text-left font-semibold">KTP_LK</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-3 py-2 text-left font-semibold">KTP_PR</th>
                                        <th class="border border-slate-300 dark:border-slate-600 px-3 py-2 text-left font-semibold">KTP</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>

                    <!-- Excel Sheet Tabs (Bottom) -->
                    <div class="border-t border-slate-300 dark:border-slate-600 bg-slate-100 dark:bg-slate-800 px-2 py-1">
                        <div class="flex gap-1 overflow-x-auto">
                            <button onclick="switchExcelSheet('sheet1')" class="excel-sheet-tab active px-4 py-2 text-xs font-medium bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-t hover:bg-slate-50 dark:hover:bg-slate-600 transition-colors">
                                Sheet1
                            </button>
                            <button onclick="switchExcelSheet('sheet1_umur')" class="excel-sheet-tab px-4 py-2 text-xs font-medium bg-slate-200 dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-t hover:bg-slate-50 dark:hover:bg-slate-600 transition-colors">
                                Sheet1_UMUR
                            </button>
                            <button onclick="switchExcelSheet('sheet3')" class="excel-sheet-tab px-4 py-2 text-xs font-medium bg-slate-200 dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-t hover:bg-slate-50 dark:hover:bg-slate-600 transition-colors">
                                Sheet3
                            </button>
                            <button onclick="switchExcelSheet('sheet4')" class="excel-sheet-tab px-4 py-2 text-xs font-medium bg-slate-200 dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-t hover:bg-slate-50 dark:hover:bg-slate-600 transition-colors">
                                Sheet4
                            </button>
                            <button onclick="switchExcelSheet('sheet10')" class="excel-sheet-tab px-4 py-2 text-xs font-medium bg-slate-200 dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-t hover:bg-slate-50 dark:hover:bg-slate-600 transition-colors">
                                Sheet10
                            </button>
                            <button onclick="switchExcelSheet('sheet5')" class="excel-sheet-tab px-4 py-2 text-xs font-medium bg-slate-200 dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-t hover:bg-slate-50 dark:hover:bg-slate-600 transition-colors">
                                Sheet5
                            </button>
                            <button onclick="switchExcelSheet('sheet7')" class="excel-sheet-tab px-4 py-2 text-xs font-medium bg-slate-200 dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-t hover:bg-slate-50 dark:hover:bg-slate-600 transition-colors">
                                Sheet7
                            </button>
                            <button onclick="switchExcelSheet('sheet6_2')" class="excel-sheet-tab px-4 py-2 text-xs font-medium bg-slate-200 dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-t hover:bg-slate-50 dark:hover:bg-slate-600 transition-colors">
                                Sheet6(2)
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Note -->
                <div class="p-4 bg-blue-50 dark:bg-blue-900/20 border-t border-slate-200 dark:border-slate-700">
                    <p class="text-xs text-blue-800 dark:text-blue-300">
                        <strong>Catatan:</strong> Nama sheet dan header harus persis seperti di atas (8 sheet wajib). Pastikan format dan ejaan sama dengan contoh.
                    </p>
                </div>
            </div>
        @endif
    </div>

    <script>
        function switchExcelSheet(sheetId) {
            // Hide all sheet contents
            document.querySelectorAll('.sheet-content-excel').forEach(content => {
                content.classList.add('hidden');
                content.classList.remove('active');
            });
            
            // Remove active class from all tabs
            document.querySelectorAll('.excel-sheet-tab').forEach(tab => {
                tab.classList.remove('active', 'bg-white', 'dark:bg-slate-700');
                tab.classList.add('bg-slate-200', 'dark:bg-slate-900');
            });
            
            // Show selected sheet content
            const selectedContent = document.getElementById(sheetId + '-content');
            if (selectedContent) {
                selectedContent.classList.remove('hidden');
                selectedContent.classList.add('active');
            }
            
            // Activate clicked tab
            event.target.classList.add('active', 'bg-white', 'dark:bg-slate-700');
            event.target.classList.remove('bg-slate-200', 'dark:bg-slate-900');
        }

        function switchSheet(event, sheetId) {
            // Remove active class from all tabs and contents
            document.querySelectorAll('.sheet-tab').forEach(tab => tab.classList.remove('active'));
            document.querySelectorAll('.sheet-content').forEach(content => content.classList.remove('active'));
            
            // Add active class to clicked tab and corresponding content
            event.target.classList.add('active');
            document.getElementById(sheetId).classList.add('active');
        }

        document.addEventListener('DOMContentLoaded', function() {
            const dropZone = document.getElementById('drop-zone');
            const fileInput = document.getElementById('file-input');
            const fileNameDisplay = document.getElementById('file-name');
            const fileNameText = fileNameDisplay.querySelector('span');
            const importForm = document.getElementById('import-form');
            const progressContainer = document.getElementById('progress-container');
            const progressBar = document.getElementById('progress-bar');
            const progressPercentage = document.getElementById('progress-percentage');

            if (dropZone) {
                // Drag & Drop effects
                ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                    dropZone.addEventListener(eventName, preventDefaults, false);
                });

                function preventDefaults(e) {
                    e.preventDefault();
                    e.stopPropagation();
                }

                ['dragenter', 'dragover'].forEach(eventName => {
                    dropZone.addEventListener(eventName, highlight, false);
                });

                ['dragleave', 'drop'].forEach(eventName => {
                    dropZone.addEventListener(eventName, unhighlight, false);
                });

                function highlight(e) {
                    dropZone.classList.add('dragover');
                }

                function unhighlight(e) {
                    dropZone.classList.remove('dragover');
                }

                // Handle file selection
                fileInput.addEventListener('change', handleFiles);
                dropZone.addEventListener('drop', handleDrop, false);

                function handleDrop(e) {
                    const dt = e.dataTransfer;
                    const files = dt.files;
                    fileInput.files = files;
                    handleFiles();
                }

                function handleFiles() {
                    if (fileInput.files.length > 0) {
                        const file = fileInput.files[0];
                        fileNameText.textContent = file.name;
                        fileNameDisplay.classList.remove('hidden');
                    }
                }
            }

            // Progress Bar Logic
            if (importForm) {
                importForm.addEventListener('submit', function() {
                    // Hide form, show progress
                    importForm.classList.add('opacity-50', 'pointer-events-none');
                    progressContainer.classList.remove('hidden');
                    
                    // Simulate progress since we can't track real server-side progress easily without websockets/polling
                    let width = 0;
                    const interval = setInterval(function() {
                        if (width >= 90) {
                            clearInterval(interval);
                        } else {
                            width += Math.random() * 10;
                            if (width > 90) width = 90;
                            progressBar.style.width = width + '%';
                            progressPercentage.textContent = Math.round(width) + '%';
                        }
                    }, 500);
                });
            }
        });
    </script>
@endsection

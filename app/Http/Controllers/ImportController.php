<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Services\DukcapilImportService;

class ImportController extends Controller
{
    public function form()
    {
        return view('import.form');
    }

    public function store(Request $request, DukcapilImportService $service)
    {
        // Validasi dasar
        $data = $request->validate([
            'year'     => ['required', 'integer', 'between:2000,2100'],
            'semester' => ['required'],
            'file'     => ['required', 'file', 'mimes:xlsx', 'max:51200'], // 50 MB
        ]);

        // Impor bisa memakan waktu lama, jadi longgarkan batas waktu eksekusi PHP.
        // if (function_exists('set_time_limit')) {
        //     @set_time_limit(0);
        // }
        if (function_exists('ini_set')) {
            @ini_set('max_execution_time', '0');
        }

        $year = (int) $data['year'];
        $semester = $this->normalizeSemester($data['semester']);
        if (!in_array($semester, [1, 2], true)) {
            return back()->withErrors(['semester' => 'Semester harus 1 atau 2.'])->withInput();
        }

        // Pastikan file dipilih
        $uploaded = $request->file('file');
        if (!$uploaded) {
            return back()->withErrors(['file' => 'Tidak ada file yang dipilih.'])->withInput();
        }

        // Cek status upload dari PHP
        if (!$uploaded->isValid()) {
            $errCode = $uploaded->getError(); // UPLOAD_ERR_*
            $map = [
                UPLOAD_ERR_INI_SIZE   => 'Ukuran file melebihi upload_max_filesize di php.ini.',
                UPLOAD_ERR_FORM_SIZE  => 'Ukuran file melebihi batas MAX_FILE_SIZE di form.',
                UPLOAD_ERR_PARTIAL    => 'File hanya terunggah sebagian.',
                UPLOAD_ERR_NO_FILE    => 'Tidak ada file yang diunggah.',
                UPLOAD_ERR_NO_TMP_DIR => 'Folder sementara (upload_tmp_dir) tidak ditemukan.',
                UPLOAD_ERR_CANT_WRITE => 'Gagal menulis file ke disk.',
                UPLOAD_ERR_EXTENSION  => 'Upload dihentikan oleh ekstensi PHP.',
            ];
            $msg = $map[$errCode] ?? ('Upload error code: '.$errCode);
            return back()->withErrors(['file' => $msg])->withInput();
        }

        try {
            // Pastikan direktori ada
            if (!Storage::disk('local')->exists('imports')) {
                Storage::disk('local')->makeDirectory('imports');
            }

            // Simpan file
            $filename = uniqid('im_') . '.xlsx';
            $path     = $uploaded->storeAs('imports', $filename, 'local');
            if ($path === false) {
                return back()->withErrors(['file' => 'Gagal menyimpan file upload.'])->withInput();
            }

            // Verifikasi benar-benar tersimpan (disesuaikan dengan root disk "local")
            if (!Storage::disk('local')->exists($path)) {
                return back()->withErrors(['file' => 'Gagal menyimpan file upload.'])->withInput();
            }

            $fullPath = Storage::disk('local')->path($path);

            // Jalankan impor dengan tahun & semester dari form
            $result = $service->import($fullPath, $year, $semester);

            return view('admin.import', [
                'filename'   => $filename,
                'summary'    => $result['summary'] ?? [],
                'highlights' => $result['highlights'] ?? [],
                'filters'    => $result['filters'] ?? [],
            ]);
        } catch (\Throwable $e) {
            return back()->withErrors([
                'file' => 'Upload error: '.$e->getMessage()
            ])->withInput();
        }
    }

    public function reset(Request $request)
    {
        $this->authorizeAction($request);

        $tables = [
            'pop_age_group',
            'pop_single_age',
            'pop_education',
            'pop_occupation',
            'pop_marital_status',
            'pop_religion',
            'pop_head_of_household',
            'pop_gender',
            'pop_wajib_ktp',
            'pop_kk',
            'import_logs',
        ];

        Schema::withoutForeignKeyConstraints(function () use ($tables) {
            foreach ($tables as $table) {
                if (Schema::hasTable($table)) {
                    DB::table($table)->truncate();
                }
            }
        });

        if (Storage::disk('local')->exists('imports')) {
            $files = Storage::disk('local')->files('imports');
            foreach ($files as $file) {
                Storage::disk('local')->delete($file);
            }
        }

        return back()->with('status', 'Data impor berhasil dibersihkan. Silakan unggah dataset baru.');
    }

    private function normalizeSemester($val): ?int
    {
        if ($val === null || $val === '') return null;
        $s = strtolower(trim((string)$val));
        $s = preg_replace('/[^0-9iv]/', '', $s);
        if (is_numeric($s)) {
            $n = (int) $s;
            return in_array($n, [1, 2], true) ? $n : null;
        }
        if (in_array($s, ['i', 'ii'], true)) return $s === 'i' ? 1 : 2;
        return null;
    }

    private function authorizeAction(Request $request): void
    {
        // Tempatkan hook otorisasi lebih lanjut jika diperlukan
        if (!$request->user() || !$request->user()->is_admin) {
            abort(403, 'Tindakan tidak diizinkan.');
        }
    }
}

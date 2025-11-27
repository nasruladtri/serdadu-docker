<?php // 2025_01_01_000010_create_population_tables.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  private function dims(Blueprint $t): array {
    $t->unsignedSmallInteger('year');
    $t->unsignedTinyInteger('semester'); // 1|2
    $t->foreignId('district_id')->constrained()->cascadeOnDelete();
    $t->foreignId('village_id')->nullable()->constrained()->nullOnDelete();
    $t->index(['year','semester']);
    $t->index(['district_id','village_id']);
    return ['year','semester','district_id','village_id'];
  }

  public function up(): void {

    // 1) Jenis kelamin (ringkasan)
    Schema::create('pop_gender', function (Blueprint $t) {
      $t->id();
      $dim = $this->dims($t);
      $t->unsignedInteger('male')->default(0);
      $t->unsignedInteger('female')->default(0);
      $t->unsignedInteger('total')->default(0);
      $t->timestamps();
      $t->unique($dim, 'pop_gender_unique_dim');
    });

    // 2) Kepala keluarga
    Schema::create('pop_head_of_household', function (Blueprint $t) {
      $t->id();
      $dim = $this->dims($t);
      foreach (['belum_kawin','kawin','cerai_hidup','cerai_mati'] as $status) {
        $t->unsignedInteger("{$status}_m")->default(0);
        $t->unsignedInteger("{$status}_f")->default(0);
      }
      $t->unsignedInteger('total')->default(0);
      $t->timestamps();
      $t->unique($dim, 'pop_hoh_unique_dim');
    });

    // 3) Agama
    Schema::create('pop_religion', function (Blueprint $t) {
      $t->id();
      $dim = $this->dims($t);
      foreach ([
        'islam','kristen','katolik','hindu','buddha','konghucu','aliran_kepercayaan'
      ] as $r) {
        $t->unsignedInteger("{$r}_m")->default(0);
        $t->unsignedInteger("{$r}_f")->default(0);
      }
      $t->unsignedInteger('total')->default(0);
      $t->timestamps();
      $t->unique($dim, 'pop_religion_unique_dim');
    });

    // 4) Status Kawin
    Schema::create('pop_marital_status', function (Blueprint $t) {
      $t->id();
      $dim = $this->dims($t);
      foreach (['belum_kawin','kawin','cerai_hidup','cerai_mati'] as $c) {
        $t->unsignedInteger("{$c}_m")->default(0);
        $t->unsignedInteger("{$c}_f")->default(0);
      }
      $t->unsignedInteger('total')->default(0);
      $t->timestamps();
      $t->unique($dim, 'pop_marital_unique_dim');
    });

    // 5) Pendidikan
    Schema::create('pop_education', function (Blueprint $t) {
      $t->id();
      $dim = $this->dims($t);
      foreach ([
        'belum_sekolah','belum_tamat_sd','tamat_sd','tamat_sltp','tamat_slta',
        'd1d2','d3','s1','s2','s3'
      ] as $p) {
        $t->unsignedInteger("{$p}_m")->default(0);
        $t->unsignedInteger("{$p}_f")->default(0);
      }
      $t->unsignedInteger('total')->default(0);
      $t->timestamps();
      $t->unique($dim, 'pop_education_unique_dim');
    });

    // 6) Pekerjaan (set inti — bisa ditambah lewat migration berikutnya)
    Schema::create('pop_occupation', function (Blueprint $t) {
      $t->id();
      $dim = $this->dims($t);
      foreach ([
        'belum_tidak_bekerja',
        'mengurus_rumah_tangga',
        'pelajar_mahasiswa',
        'pensiunan',
        'pegawai_negeri_sipil_pns',
        'tentara_nasional_indonesia_tni',
        'kepolisian_ri_polri',
        'perdagangan',
        'petani_pekebun',
        'peternak',
        'nelayan_perikanan',
        'industri',
        'konstruksi',
        'transportasi',
        'karyawan_swasta',
        'karyawan_bumn',
        'karyawan_bumd',
        'karyawan_honorer',
        'buruh_harian_lepas',
        'buruh_tani_perkebunan',
        'buruh_nelayan_perikanan',
        'buruh_peternakan',
        'pembantu_rumah_tangga',
        'tukang_cukur',
        'tukang_listrik',
        'tukang_batu',
        'tukang_kayu',
        'tukang_sol_sepatu',
        'tukang_las_pandai_besi',
        'tukang_jahit',
        'tukang_gigi',
        'penata_rias',
        'penata_busana',
        'penata_rambut',
        'mekanik',
        'seniman',
        'tabib',
        'paraji',
        'perancang_busana',
        'penterjemah',
        'imam_masjid',
        'pendeta',
        'pastor',
        'wartawan',
        'ustadz_mubaligh',
        'juru_masak',
        'promotor_acara',
        'anggota_dpr_ri',
        'anggota_dpd_ri',
        'anggota_bpk',
        'presiden',
        'wakil_presiden',
        'anggota_mahkamah_konstitusi',
        'anggota_kabinet_kementrian',
        'duta_besar',
        'gubernur',
        'wakil_gubernur',
        'bupati',
        'wakil_bupati',
        'walikota',
        'wakil_walikota',
        'anggota_dprd_prop',
        'anggota_dprd_kab_kota',
        'dosen',
        'guru',
        'pilot',
        'pengacara',
        'notaris',
        'arsitek',
        'akuntan',
        'konsultan',
        'dokter',
        'bidan',
        'perawat',
        'apoteker',
        'psikiater_psikolog',
        'penyiar_televisi',
        'penyiar_radio',
        'pelaut',
        'peneliti',
        'sopir',
        'pialang',
        'paranormal',
        'pedagang',
        'perangkat_desa',
        'kepala_desa',
        'biarawan_biarawati',
        'wiraswasta',
        'anggota_lembaga_tinggi_lainnya',
        'artis',
        'atlit',
        'cheff',
        'manajer',
        'tenaga_tata_usaha',
        'operator',
        'pekerja_pengolahan_kerajinan',
        'teknisi',
        'asisten_ahli',
        'pekerjaan_lainnya',
      ] as $o) {
        $t->unsignedInteger("{$o}_m")->default(0);
        $t->unsignedInteger("{$o}_f")->default(0);
      }
      $t->unsignedInteger('total')->default(0);
      $t->timestamps();
      $t->unique($dim, 'pop_occupation_unique_dim');
    });

    // 7) Umur Tunggal
    Schema::create('pop_single_age', function (Blueprint $t) {
      $t->id();
      $dim = $this->dims($t);
      $t->unsignedTinyInteger('age'); // 0..100
      $t->unsignedInteger('male')->default(0);
      $t->unsignedInteger('female')->default(0);
      $t->unsignedInteger('total')->default(0);
      $t->timestamps();
      $t->unique(array_merge($dim, ['age']), 'pop_single_age_unique_dim');
    });

    // 8) Kelompok Umur
    Schema::create('pop_age_group', function (Blueprint $t) {
      $t->id();
      $dim = $this->dims($t);
      $t->string('age_group'); // "0-4", "5-9", ... ">75"
      $t->unsignedInteger('male')->default(0);
      $t->unsignedInteger('female')->default(0);
      $t->unsignedInteger('total')->default(0);
      $t->timestamps();
      $t->unique(array_merge($dim, ['age_group']), 'pop_age_group_unique_dim');
    });

    // 9) Wajib KTP
    Schema::create('pop_wajib_ktp', function (Blueprint $t) {
      $t->id();
      $dim = $this->dims($t);
      $t->unsignedInteger('male')->default(0);
      $t->unsignedInteger('female')->default(0);
      $t->unsignedInteger('total')->default(0);
      $t->timestamps();
      $t->unique($dim, 'pop_wajib_ktp_unique_dim');
    });

    // 10) Log impor
    Schema::create('import_logs', function (Blueprint $t) {
      $t->id();
      $t->string('filename');
      $t->string('sheet');
      $t->enum('status', ['success','partial','skipped','failed'])->default('success');
      $t->unsignedInteger('rows_ok')->default(0);
      $t->unsignedInteger('rows_fail')->default(0);
      $t->json('errors')->nullable();
      $t->timestamps();
      $t->index(['filename','sheet']);
      $t->index('created_at');
    });
  }

  public function down(): void {
    Schema::dropIfExists('import_logs');
    Schema::dropIfExists('pop_wajib_ktp');
    Schema::dropIfExists('pop_age_group');
    Schema::dropIfExists('pop_single_age');
    Schema::dropIfExists('pop_occupation');
    Schema::dropIfExists('pop_education');
    Schema::dropIfExists('pop_marital_status');
    Schema::dropIfExists('pop_religion');
    Schema::dropIfExists('pop_head_of_household');
    Schema::dropIfExists('pop_gender');
  }
};

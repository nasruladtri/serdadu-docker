<?php
return [

  // Normalisasi header: lower, spasi->underscore, hilangkan tanda baca
  'normalize_headers' => true,

  // Master referensi (wajib ada minimal code+name)
  'master' => [
    'district_code' => 'districts.code',
    'district_name' => 'districts.name',
    'village_code'  => 'villages.code',
    'village_name'  => 'villages.name',
  ],

  // Pemetaan sheet -> tabel & kolom
  // Sesuaikan nama persis sheet di file Anda (boleh multi-variasi).
  'sheets' => [

    // 1) Kelompok Umur
    'kelompok umur' => [
      'table' => 'pop_age_group',
      'keys'  => ['year','semester','district_id','village_id','age_group'],
      'cols'  => [
        'year','semester','district_code','district_name','village_code','village_name',
        'age_group','male','female','total'
      ],
      'calc_total' => ['male','female'],
    ],

    // 2) Umur Tunggal
    'umur tunggal' => [
      'table' => 'pop_single_age',
      'keys'  => ['year','semester','district_id','village_id','age'],
      'cols'  => [
        'year','semester','district_code','district_name','village_code','village_name',
        'age','male','female','total'
      ],
      'calc_total' => ['male','female'],
    ],

    // 3) Pendidikan
    'pendidikan' => [
      'table' => 'pop_education',
      'keys'  => ['year','semester','district_id','village_id'],
      'cols'  => [
        'year','semester','district_code','district_name','village_code','village_name',
        'belum_sekolah_m','belum_sekolah_f',
        'belum_tamat_sd_m','belum_tamat_sd_f',
        'tamat_sd_m','tamat_sd_f',
        'tamat_sltp_m','tamat_sltp_f',
        'tamat_slta_m','tamat_slta_f',
        'd1d2_m','d1d2_f','d3_m','d3_f','s1_m','s1_f','s2_m','s2_f','s3_m','s3_f',
        'total'
      ],
      'calc_total' => [
        'belum_sekolah_m','belum_sekolah_f','belum_tamat_sd_m','belum_tamat_sd_f',
        'tamat_sd_m','tamat_sd_f','tamat_sltp_m','tamat_sltp_f','tamat_slta_m','tamat_slta_f',
        'd1d2_m','d1d2_f','d3_m','d3_f','s1_m','s1_f','s2_m','s2_f','s3_m','s3_f'
      ],
    ],

    // 4) Pekerjaan
    'pekerjaan' => [
      'table' => 'pop_occupation',
      'keys'  => ['year','semester','district_id','village_id'],
      'cols'  => [
        'year','semester','district_code','district_name','village_code','village_name',
        'belum_tidak_bekerja_m','belum_tidak_bekerja_f',
        'mengurus_rumah_tangga_m','mengurus_rumah_tangga_f',
        'pelajar_mahasiswa_m','pelajar_mahasiswa_f',
        'pensiunan_m','pensiunan_f',
        'pegawai_negeri_sipil_pns_m','pegawai_negeri_sipil_pns_f',
        'tentara_nasional_indonesia_tni_m','tentara_nasional_indonesia_tni_f',
        'kepolisian_ri_polri_m','kepolisian_ri_polri_f',
        'perdagangan_m','perdagangan_f',
        'petani_pekebun_m','petani_pekebun_f',
        'peternak_m','peternak_f',
        'nelayan_perikanan_m','nelayan_perikanan_f',
        'industri_m','industri_f',
        'konstruksi_m','konstruksi_f',
        'transportasi_m','transportasi_f',
        'karyawan_swasta_m','karyawan_swasta_f',
        'karyawan_bumn_m','karyawan_bumn_f',
        'karyawan_bumd_m','karyawan_bumd_f',
        'karyawan_honorer_m','karyawan_honorer_f',
        'buruh_harian_lepas_m','buruh_harian_lepas_f',
        'buruh_tani_perkebunan_m','buruh_tani_perkebunan_f',
        'buruh_nelayan_perikanan_m','buruh_nelayan_perikanan_f',
        'buruh_peternakan_m','buruh_peternakan_f',
        'pembantu_rumah_tangga_m','pembantu_rumah_tangga_f',
        'tukang_cukur_m','tukang_cukur_f',
        'tukang_listrik_m','tukang_listrik_f',
        'tukang_batu_m','tukang_batu_f',
        'tukang_kayu_m','tukang_kayu_f',
        'tukang_sol_sepatu_m','tukang_sol_sepatu_f',
        'tukang_las_pandai_besi_m','tukang_las_pandai_besi_f',
        'tukang_jahit_m','tukang_jahit_f',
        'tukang_gigi_m','tukang_gigi_f',
        'penata_rias_m','penata_rias_f',
        'penata_busana_m','penata_busana_f',
        'penata_rambut_m','penata_rambut_f',
        'mekanik_m','mekanik_f',
        'seniman_m','seniman_f',
        'tabib_m','tabib_f',
        'paraji_m','paraji_f',
        'perancang_busana_m','perancang_busana_f',
        'penterjemah_m','penterjemah_f',
        'imam_masjid_m','imam_masjid_f',
        'pendeta_m','pendeta_f',
        'pastor_m','pastor_f',
        'wartawan_m','wartawan_f',
        'ustadz_mubaligh_m','ustadz_mubaligh_f',
        'juru_masak_m','juru_masak_f',
        'promotor_acara_m','promotor_acara_f',
        'anggota_dpr_ri_m','anggota_dpr_ri_f',
        'anggota_dpd_ri_m','anggota_dpd_ri_f',
        'anggota_bpk_m','anggota_bpk_f',
        'presiden_m','presiden_f',
        'wakil_presiden_m','wakil_presiden_f',
        'anggota_mahkamah_konstitusi_m','anggota_mahkamah_konstitusi_f',
        'anggota_kabinet_kementrian_m','anggota_kabinet_kementrian_f',
        'duta_besar_m','duta_besar_f',
        'gubernur_m','gubernur_f',
        'wakil_gubernur_m','wakil_gubernur_f',
        'bupati_m','bupati_f',
        'wakil_bupati_m','wakil_bupati_f',
        'walikota_m','walikota_f',
        'wakil_walikota_m','wakil_walikota_f',
        'anggota_dprd_prop_m','anggota_dprd_prop_f',
        'anggota_dprd_kab_kota_m','anggota_dprd_kab_kota_f',
        'dosen_m','dosen_f',
        'guru_m','guru_f',
        'pilot_m','pilot_f',
        'pengacara_m','pengacara_f',
        'notaris_m','notaris_f',
        'arsitek_m','arsitek_f',
        'akuntan_m','akuntan_f',
        'konsultan_m','konsultan_f',
        'dokter_m','dokter_f',
        'bidan_m','bidan_f',
        'perawat_m','perawat_f',
        'apoteker_m','apoteker_f',
        'psikiater_psikolog_m','psikiater_psikolog_f',
        'penyiar_televisi_m','penyiar_televisi_f',
        'penyiar_radio_m','penyiar_radio_f',
        'pelaut_m','pelaut_f',
        'peneliti_m','peneliti_f',
        'sopir_m','sopir_f',
        'pialang_m','pialang_f',
        'paranormal_m','paranormal_f',
        'pedagang_m','pedagang_f',
        'perangkat_desa_m','perangkat_desa_f',
        'kepala_desa_m','kepala_desa_f',
        'biarawan_biarawati_m','biarawan_biarawati_f',
        'wiraswasta_m','wiraswasta_f',
        'anggota_lembaga_tinggi_lainnya_m','anggota_lembaga_tinggi_lainnya_f',
        'artis_m','artis_f',
        'atlit_m','atlit_f',
        'cheff_m','cheff_f',
        'manajer_m','manajer_f',
        'tenaga_tata_usaha_m','tenaga_tata_usaha_f',
        'operator_m','operator_f',
        'pekerja_pengolahan_kerajinan_m','pekerja_pengolahan_kerajinan_f',
        'teknisi_m','teknisi_f',
        'asisten_ahli_m','asisten_ahli_f',
        'pekerjaan_lainnya_m','pekerjaan_lainnya_f',
        'total'
      ],
      'calc_total' => 'all_mf', // arti: jumlahkan semua *_m dan *_f
    ],

    // 5) Perkawinan (Status Kawin)
    'perkawinan' => [
      'table' => 'pop_marital_status',
      'keys'  => ['year','semester','district_id','village_id'],
      'cols'  => [
        'year','semester','district_code','district_name','village_code','village_name',
        'belum_kawin_m','belum_kawin_f',
        'kawin_m','kawin_f',
        'cerai_hidup_m','cerai_hidup_f',
        'cerai_mati_m','cerai_mati_f',
        'total'
      ],
      'calc_total' => [
        'belum_kawin_m','belum_kawin_f','kawin_m','kawin_f',
        'cerai_hidup_m','cerai_hidup_f','cerai_mati_m','cerai_mati_f'
      ],
    ],

    // 6) Kepala Keluarga Berdasarkan Status Perkawinan (kita simpan ke pop_head_of_household)
    'kepala keluarga berdasarkan status perkawinan' => [
      'table' => 'pop_head_of_household',
      'keys'  => ['year','semester','district_id','village_id'],
      'cols'  => [
        'year','semester','district_code','district_name','village_code','village_name',
        'belum_kawin_m','belum_kawin_f',
        'kawin_m','kawin_f',
        'cerai_hidup_m','cerai_hidup_f',
        'cerai_mati_m','cerai_mati_f',
        'total'
      ],
      'calc_total' => 'all_mf',
    ],

    // 7) Agama
    'agama' => [
      'table' => 'pop_religion',
      'keys'  => ['year','semester','district_id','village_id'],
      'cols'  => [
        'year','semester','district_code','district_name','village_code','village_name',
        'islam_m','islam_f','kristen_m','kristen_f','katolik_m','katolik_f',
        'hindu_m','hindu_f','buddha_m','buddha_f','konghucu_m','konghucu_f',
        'aliran_kepercayaan_m','aliran_kepercayaan_f','total'
      ],
      'calc_total' => [
        'islam_m','islam_f','kristen_m','kristen_f','katolik_m','katolik_f',
        'hindu_m','hindu_f','buddha_m','buddha_f','konghucu_m','konghucu_f',
        'aliran_kepercayaan_m','aliran_kepercayaan_f'
      ],
    ],

    // 8) Jenis Kelamin
    'jenis kelamin' => [
      'table' => 'pop_gender',
      'keys'  => ['year','semester','district_id','village_id'],
      'cols'  => [
        'year','semester','district_code','district_name','village_code','village_name',
        'male','female','total'
      ],
      'calc_total' => ['male','female'],
    ],

    // 9) Wajib KTP
    'wajib ktp' => [
      'table' => 'pop_wajib_ktp',
      'keys'  => ['year','semester','district_id','village_id'],
      'cols'  => [
        'year','semester','district_code','district_name','village_code','village_name',
        'male','female','total'
      ],
      'calc_total' => ['male','female'],
    ],

    // 10) Kartu Keluarga (KK)
    'kartu keluarga' => [
      'table' => 'pop_kk',
      'keys'  => ['year','semester','district_id','village_id'],
      'cols'  => [
        'year','semester','district_code','district_name','village_code','village_name',
        'male','female','total',
        'male_printed','female_printed','total_printed',
        'male_not_printed','female_not_printed','total_not_printed'
      ],
      'calc_total' => ['male','female'],
    ],

    // ========== MAPPING SHEET GENERIK (untuk import cepat tanpa ganti nama) ==========
    // Mapping untuk sheet dengan nama generik seperti Sheet1, Sheet3, dll

    'sheet1' => [
      'table' => 'pop_age_group',
      'keys'  => ['year','semester','district_id','village_id','age_group'],
      'cols'  => [
        'year','semester','district_code','district_name','village_code','village_name',
        'age_group','male','female','total'
      ],
      'calc_total' => ['male','female'],
    ],

    'sheet1_umur' => [
      'table' => 'pop_single_age',
      'keys'  => ['year','semester','district_id','village_id','age'],
      'cols'  => [
        'year','semester','district_code','district_name','village_code','village_name',
        'age','male','female','total'
      ],
      'calc_total' => ['male','female'],
    ],

    'sheet3' => [
      'table' => 'pop_education',
      'keys'  => ['year','semester','district_id','village_id'],
      'cols'  => [
        'year','semester','district_code','district_name','village_code','village_name',
        'belum_sekolah_m','belum_sekolah_f',
        'belum_tamat_sd_m','belum_tamat_sd_f',
        'tamat_sd_m','tamat_sd_f',
        'tamat_sltp_m','tamat_sltp_f',
        'tamat_slta_m','tamat_slta_f',
        'd1d2_m','d1d2_f','d3_m','d3_f','s1_m','s1_f','s2_m','s2_f','s3_m','s3_f',
        'total'
      ],
      'calc_total' => [
        'belum_sekolah_m','belum_sekolah_f','belum_tamat_sd_m','belum_tamat_sd_f',
        'tamat_sd_m','tamat_sd_f','tamat_sltp_m','tamat_sltp_f','tamat_slta_m','tamat_slta_f',
        'd1d2_m','d1d2_f','d3_m','d3_f','s1_m','s1_f','s2_m','s2_f','s3_m','s3_f'
      ],
    ],

    'sheet4' => [
      'table' => 'pop_occupation',
      'keys'  => ['year','semester','district_id','village_id'],
      'cols'  => [
        'year','semester','district_code','district_name','village_code','village_name',
        'belum_tidak_bekerja_m','belum_tidak_bekerja_f',
        'mengurus_rumah_tangga_m','mengurus_rumah_tangga_f',
        'pelajar_mahasiswa_m','pelajar_mahasiswa_f',
        'pensiunan_m','pensiunan_f',
        'pegawai_negeri_sipil_pns_m','pegawai_negeri_sipil_pns_f',
        'tentara_nasional_indonesia_tni_m','tentara_nasional_indonesia_tni_f',
        'kepolisian_ri_polri_m','kepolisian_ri_polri_f',
        'perdagangan_m','perdagangan_f',
        'petani_pekebun_m','petani_pekebun_f',
        'peternak_m','peternak_f',
        'nelayan_perikanan_m','nelayan_perikanan_f',
        'industri_m','industri_f',
        'konstruksi_m','konstruksi_f',
        'transportasi_m','transportasi_f',
        'karyawan_swasta_m','karyawan_swasta_f',
        'karyawan_bumn_m','karyawan_bumn_f',
        'karyawan_bumd_m','karyawan_bumd_f',
        'karyawan_honorer_m','karyawan_honorer_f',
        'buruh_harian_lepas_m','buruh_harian_lepas_f',
        'buruh_tani_perkebunan_m','buruh_tani_perkebunan_f',
        'buruh_nelayan_perikanan_m','buruh_nelayan_perikanan_f',
        'buruh_peternakan_m','buruh_peternakan_f',
        'pembantu_rumah_tangga_m','pembantu_rumah_tangga_f',
        'tukang_cukur_m','tukang_cukur_f',
        'tukang_listrik_m','tukang_listrik_f',
        'tukang_batu_m','tukang_batu_f',
        'tukang_kayu_m','tukang_kayu_f',
        'tukang_sol_sepatu_m','tukang_sol_sepatu_f',
        'tukang_las_pandai_besi_m','tukang_las_pandai_besi_f',
        'tukang_jahit_m','tukang_jahit_f',
        'tukang_gigi_m','tukang_gigi_f',
        'penata_rias_m','penata_rias_f',
        'penata_busana_m','penata_busana_f',
        'penata_rambut_m','penata_rambut_f',
        'mekanik_m','mekanik_f',
        'seniman_m','seniman_f',
        'tabib_m','tabib_f',
        'paraji_m','paraji_f',
        'perancang_busana_m','perancang_busana_f',
        'penterjemah_m','penterjemah_f',
        'imam_masjid_m','imam_masjid_f',
        'pendeta_m','pendeta_f',
        'pastor_m','pastor_f',
        'wartawan_m','wartawan_f',
        'ustadz_mubaligh_m','ustadz_mubaligh_f',
        'juru_masak_m','juru_masak_f',
        'promotor_acara_m','promotor_acara_f',
        'anggota_dpr_ri_m','anggota_dpr_ri_f',
        'anggota_dpd_ri_m','anggota_dpd_ri_f',
        'anggota_bpk_m','anggota_bpk_f',
        'presiden_m','presiden_f',
        'wakil_presiden_m','wakil_presiden_f',
        'anggota_mahkamah_konstitusi_m','anggota_mahkamah_konstitusi_f',
        'anggota_kabinet_kementrian_m','anggota_kabinet_kementrian_f',
        'duta_besar_m','duta_besar_f',
        'gubernur_m','gubernur_f',
        'wakil_gubernur_m','wakil_gubernur_f',
        'bupati_m','bupati_f',
        'wakil_bupati_m','wakil_bupati_f',
        'walikota_m','walikota_f',
        'wakil_walikota_m','wakil_walikota_f',
        'anggota_dprd_prop_m','anggota_dprd_prop_f',
        'anggota_dprd_kab_kota_m','anggota_dprd_kab_kota_f',
        'dosen_m','dosen_f',
        'guru_m','guru_f',
        'pilot_m','pilot_f',
        'pengacara_m','pengacara_f',
        'notaris_m','notaris_f',
        'arsitek_m','arsitek_f',
        'akuntan_m','akuntan_f',
        'konsultan_m','konsultan_f',
        'dokter_m','dokter_f',
        'bidan_m','bidan_f',
        'perawat_m','perawat_f',
        'apoteker_m','apoteker_f',
        'psikiater_psikolog_m','psikiater_psikolog_f',
        'penyiar_televisi_m','penyiar_televisi_f',
        'penyiar_radio_m','penyiar_radio_f',
        'pelaut_m','pelaut_f',
        'peneliti_m','peneliti_f',
        'sopir_m','sopir_f',
        'pialang_m','pialang_f',
        'paranormal_m','paranormal_f',
        'pedagang_m','pedagang_f',
        'perangkat_desa_m','perangkat_desa_f',
        'kepala_desa_m','kepala_desa_f',
        'biarawan_biarawati_m','biarawan_biarawati_f',
        'wiraswasta_m','wiraswasta_f',
        'anggota_lembaga_tinggi_lainnya_m','anggota_lembaga_tinggi_lainnya_f',
        'artis_m','artis_f',
        'atlit_m','atlit_f',
        'cheff_m','cheff_f',
        'manajer_m','manajer_f',
        'tenaga_tata_usaha_m','tenaga_tata_usaha_f',
        'operator_m','operator_f',
        'pekerja_pengolahan_kerajinan_m','pekerja_pengolahan_kerajinan_f',
        'teknisi_m','teknisi_f',
        'asisten_ahli_m','asisten_ahli_f',
        'pekerjaan_lainnya_m','pekerjaan_lainnya_f',
        'total'
      ],
      'calc_total' => 'all_mf',
    ],

    'sheet5' => [
      'table' => 'pop_head_of_household',
      'keys'  => ['year','semester','district_id','village_id'],
      'cols'  => [
        'year','semester','district_code','district_name','village_code','village_name',
        'belum_kawin_m','belum_kawin_f',
        'kawin_m','kawin_f',
        'cerai_hidup_m','cerai_hidup_f',
        'cerai_mati_m','cerai_mati_f',
        'total'
      ],
      'calc_total' => 'all_mf',
    ],

    'sheet6' => [
      'table' => 'pop_wajib_ktp',
      'keys'  => ['year','semester','district_id','village_id'],
      'cols'  => [
        'year','semester','district_code','district_name','village_code','village_name',
        'male','female','total'
      ],
      'calc_total' => ['male','female'],
    ],

    'sheet7' => [
      'table' => 'pop_religion',
      'keys'  => ['year','semester','district_id','village_id'],
      'cols'  => [
        'year','semester','district_code','district_name','village_code','village_name',
        'islam_m','islam_f','kristen_m','kristen_f','katolik_m','katolik_f',
        'hindu_m','hindu_f','buddha_m','buddha_f','konghucu_m','konghucu_f',
        'aliran_kepercayaan_m','aliran_kepercayaan_f','total'
      ],
      'calc_total' => [
        'islam_m','islam_f','kristen_m','kristen_f','katolik_m','katolik_f',
        'hindu_m','hindu_f','buddha_m','buddha_f','konghucu_m','konghucu_f',
        'aliran_kepercayaan_m','aliran_kepercayaan_f'
      ],
    ],

    'sheet9' => [
      'table' => 'pop_kk',
      'keys'  => ['year','semester','district_id','village_id'],
      'cols'  => [
        'year','semester','district_code','district_name','village_code','village_name',
        'male','female','total',
        'male_printed','female_printed','total_printed',
        'male_not_printed','female_not_printed','total_not_printed'
      ],
      'calc_total' => ['male','female'],
    ],

    'sheet10' => [
      'table' => 'pop_marital_status',
      'keys'  => ['year','semester','district_id','village_id'],
      'cols'  => [
        'year','semester','district_code','district_name','village_code','village_name',
        'belum_kawin_m','belum_kawin_f',
        'kawin_m','kawin_f',
        'cerai_hidup_m','cerai_hidup_f',
        'cerai_mati_m','cerai_mati_f',
        'total'
      ],
      'calc_total' => [
        'belum_kawin_m','belum_kawin_f','kawin_m','kawin_f',
        'cerai_hidup_m','cerai_hidup_f','cerai_mati_m','cerai_mati_f'
      ],
    ],

  ],
];

<?php // 2025_01_01_000011_create_pop_kk_table.php

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
    // Kartu Keluarga (KK)
    Schema::create('pop_kk', function (Blueprint $t) {
      $t->id();
      $dim = $this->dims($t);
      
      // Jumlah KK
      $t->unsignedInteger('male')->default(0);              // L_KK
      $t->unsignedInteger('female')->default(0);            // P_KK
      $t->unsignedInteger('total')->default(0);              // JML_KK
      
      // KK yang sudah dicetak
      $t->unsignedInteger('male_printed')->default(0);      // L_CETAK_KK
      $t->unsignedInteger('female_printed')->default(0);   // P_CETAK_KK
      $t->unsignedInteger('total_printed')->default(0);    // JML_CETAK_KK
      
      // KK yang belum dicetak
      $t->unsignedInteger('male_not_printed')->default(0);   // BLM_CETAK_KK_L
      $t->unsignedInteger('female_not_printed')->default(0); // BLM_CETAK_KK_P
      $t->unsignedInteger('total_not_printed')->default(0); // BLM_CETAK_KK_JML
      
      $t->timestamps();
      $t->unique($dim, 'pop_kk_unique_dim');
    });
  }

  public function down(): void {
    Schema::dropIfExists('pop_kk');
  }
};


<?php // 2025_01_01_000001_create_master_tables.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('districts', function (Blueprint $t) {
      $t->id();
      $t->string('code')->unique();
      $t->string('name');
      $t->longText('geojson')->nullable();
      $t->timestamps();
      $t->index('name');
    });

    Schema::create('villages', function (Blueprint $t) {
      $t->id();
      $t->foreignId('district_id')->constrained()->cascadeOnDelete();
      $t->string('code')->unique();
      $t->string('name');
      $t->timestamps();
      $t->index(['district_id','name']);
    });
  }
  public function down(): void {
    Schema::dropIfExists('villages');
    Schema::dropIfExists('districts');
  }
};

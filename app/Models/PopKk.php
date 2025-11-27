<?php // app/Models/PopKk.php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class PopKk extends Model {
  protected $table = 'pop_kk';
  protected $fillable = [
    'year',
    'semester',
    'district_id',
    'village_id',
    'male',
    'female',
    'total',
    'male_printed',
    'female_printed',
    'total_printed',
    'male_not_printed',
    'female_not_printed',
    'total_not_printed',
  ];

  public function district()
  {
    return $this->belongsTo(District::class);
  }

  public function village()
  {
    return $this->belongsTo(Village::class);
  }
}


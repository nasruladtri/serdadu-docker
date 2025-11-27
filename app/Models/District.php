<?php // app/Models/District.php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class District extends Model {
  protected $fillable = ['code','name','geojson'];
  public function villages(){ return $this->hasMany(Village::class); }
}

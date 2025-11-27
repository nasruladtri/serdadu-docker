<?php // app/Models/Village.php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Village extends Model {
  protected $fillable = ['district_id','code','name'];
  public function district(){ return $this->belongsTo(District::class); }
}

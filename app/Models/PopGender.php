<?php // app/Models/PopGender.php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class PopGender extends Model {
  protected $table='pop_gender';
  protected $fillable=[
    'year','semester','district_id','village_id','male','female','total'
  ];
}

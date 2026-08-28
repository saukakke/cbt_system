<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Question extends Model { protected $fillable=['exam_id','body','type','points','position']; public function exam(){return $this->belongsTo(Exam::class);} public function options(){return $this->hasMany(Option::class)->orderBy('position');} }

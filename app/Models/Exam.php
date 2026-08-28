<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Exam extends Model { protected $fillable=['title','description','duration_minutes','starts_at','ends_at','is_published','created_by']; protected $casts=['starts_at'=>'datetime','ends_at'=>'datetime','is_published'=>'boolean']; public function creator(){return $this->belongsTo(User::class,'created_by');} public function questions(){return $this->hasMany(Question::class)->orderBy('position');} public function attempts(){return $this->hasMany(Attempt::class);} public function scopePublished($q){return $q->where('is_published',true);} public function isOpen(){return $this->is_published && (!$this->starts_at||now()->gte($this->starts_at)) && (!$this->ends_at||now()->lte($this->ends_at));} }

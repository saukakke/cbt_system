<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Attempt extends Model
{
    use SoftDeletes;

    protected $fillable=['exam_id','user_id','started_at','submitted_at','status','score','total_points','duration_minutes'];
    protected $casts=['started_at'=>'datetime','submitted_at'=>'datetime','score'=>'decimal:2','total_points'=>'decimal:2','duration_minutes'=>'integer'];
    public function exam(){return $this->belongsTo(Exam::class);}
    public function user(){return $this->belongsTo(User::class);}
    public function answers(){return $this->hasMany(Answer::class);}
    public function isActive(){return $this->status==='in_progress';}
    public function deadline(){return $this->started_at->copy()->addMinutes($this->duration_minutes);}
    public function hasExpired(){return now()->gte($this->deadline());}
}

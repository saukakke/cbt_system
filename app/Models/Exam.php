<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Exam extends Model
{
    use SoftDeletes;

    protected $fillable=['title','description','duration_minutes','starts_at','ends_at','is_published','allow_retake','attempt_limit','created_by'];
    protected $casts=['starts_at'=>'datetime','ends_at'=>'datetime','is_published'=>'boolean','allow_retake'=>'boolean','attempt_limit'=>'integer'];
    public function creator(){return $this->belongsTo(User::class,'created_by');}
    public function questions(){return $this->hasMany(Question::class)->orderBy('position');}
    public function attempts(){return $this->hasMany(Attempt::class);}
    public function scopePublished($q){return $q->where('is_published',true);}
    public function isOpen(){return $this->is_published&&(!$this->starts_at||now()->gte($this->starts_at))&&(!$this->ends_at||now()->lte($this->ends_at));}
    public function canStudentStart(User $user): bool
    {
        if (!$this->isOpen() || !$this->questions()->exists()) return false;
        if ($this->attempts()->where('user_id',$user->id)->where('status','in_progress')->exists()) return true;
        $completed=$this->attempts()->where('user_id',$user->id)->where('status','submitted')->count();
        if ($completed===0) return true;
        return $this->allow_retake && ($this->attempt_limit===null || $completed<$this->attempt_limit);
    }
}

<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Answer extends Model { use SoftDeletes; protected $fillable=['attempt_id','question_id','option_id','is_correct','points_awarded']; protected $casts=['is_correct'=>'boolean','points_awarded'=>'decimal:2']; public function attempt(){return $this->belongsTo(Attempt::class);} public function question(){return $this->belongsTo(Question::class);} public function option(){return $this->belongsTo(Option::class);} }

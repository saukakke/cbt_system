<?php
namespace App\Models;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class User extends Authenticatable { use HasFactory,Notifiable; protected $fillable=['name','email','password','role']; protected $hidden=['password','remember_token']; protected function casts():array{return ['email_verified_at'=>'datetime','password'=>'hashed'];} public function exams(){return $this->hasMany(Exam::class,'created_by');} public function attempts(){return $this->hasMany(Attempt::class);} public function isAdmin(){return $this->role==='admin';} public function isTeacher(){return $this->role==='teacher';} public function isStudent(){return $this->role==='student';} }

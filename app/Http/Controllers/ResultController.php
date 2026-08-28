<?php
namespace App\Http\Controllers;
use App\Models\{Attempt,User};
class ResultController extends Controller { public function index(){ $u=auth()->user();$attempts=$u->isStudent()?Attempt::with('exam')->where('user_id',$u->id)->latest()->paginate(20):Attempt::with(['exam','user'])->latest()->paginate(30);return view('results.index',compact('attempts')); } public function show(Attempt $attempt){$u=auth()->user();abort_unless($u->isAdmin()||$attempt->user_id===$u->id||($u->isTeacher()&&$attempt->exam->created_by===$u->id),403);$attempt->load('exam.questions.options','answers.question','answers.option','user');return view('results.show',compact('attempt'));} }

<?php

namespace App\Http\Controllers;

use App\Models\{Exam,User};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(){return view('users.index',['users'=>User::latest()->paginate(30)]);}
    public function store(Request $r){$d=$r->validate(['name'=>'required|string|max:120','email'=>'required|email|unique:users','password'=>'required|string|min:10','role'=>'required|in:admin,teacher,student']);$d['password']=Hash::make($d['password']);User::create($d);return back()->with('success','User created.');}
    public function update(Request $r,User $user){$d=$r->validate(['name'=>'required|string|max:120','role'=>'required|in:admin,teacher,student']);if($user->role==='admin'&&$d['role']!=='admin'&&User::where('role','admin')->count()<=1){return back()->withInput()->withErrors(['role'=>'The last remaining admin cannot be removed. Promote another admin before changing this role.']);}$user->update($d);return back()->with('success','User updated.');}
    public function destroy(User $user){abort_if($user->id===auth()->id(),422,'You cannot delete your own account.');if(Exam::withTrashed()->where('created_by',$user->id)->whereHas('attempts',fn($q)=>$q->withTrashed()->whereIn('status',['submitted','in_progress']))->exists()){return back()->withErrors(['user'=>'This user cannot be deleted because they created an exam with recorded attempts. Preserve the account or remove the attempt history first.']);}$user->delete();return back()->with('success','User removed.');}
}

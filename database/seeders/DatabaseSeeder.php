<?php

namespace Database\Seeders;

use App\Models\{User,Exam,Question};
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::updateOrCreate(['email'=>'admin@cbt.local'],['name'=>'System Administrator','password'=>Hash::make('ChangeMe123!'),'role'=>'admin']);
        $teacher = User::updateOrCreate(['email'=>'teacher@cbt.local'],['name'=>'Demo Teacher','password'=>Hash::make('ChangeMe123!'),'role'=>'teacher']);
        User::updateOrCreate(['email'=>'student@cbt.local'],['name'=>'Demo Student','password'=>Hash::make('ChangeMe123!'),'role'=>'student']);

        $exam = Exam::updateOrCreate(['title'=>'CBT Demo Examination'],[
            'description'=>'Sample examination created by the seed data.','duration_minutes'=>30,
            'created_by'=>$teacher->id,'is_published'=>true,'allow_retake'=>false,'attempt_limit'=>null,
        ]);
        if (!$exam->questions()->exists()) {
            $question = $exam->questions()->create(['body'=>'Which HTTP status code indicates a successful request?','type'=>'single','points'=>10,'position'=>1]);
            $question->options()->createMany([
                ['label'=>'200 OK','is_correct'=>true,'position'=>1],
                ['label'=>'404 Not Found','is_correct'=>false,'position'=>2],
                ['label'=>'500 Server Error','is_correct'=>false,'position'=>3],
                ['label'=>'301 Moved Permanently','is_correct'=>false,'position'=>4],
            ]);
        }
    }
}

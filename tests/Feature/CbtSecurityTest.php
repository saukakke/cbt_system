<?php

namespace Tests\Feature;

use App\Models\{Attempt,Exam,Option,Question,User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CbtSecurityTest extends TestCase
{
    use RefreshDatabase;

    private function exam(User $teacher, array $extra=[]): Exam
    {
        $exam=Exam::create(array_merge(['created_by'=>$teacher->id,'title'=>'Test Exam','description'=>null,'duration_minutes'=>30,'is_published'=>true],$extra));
        $q=$exam->questions()->create(['body'=>'2 + 2?','type'=>'single','points'=>10,'position'=>1]);
        $q->options()->createMany([['label'=>'3','is_correct'=>false,'position'=>1],['label'=>'4','is_correct'=>true,'position'=>2]]);
        return $exam;
    }

    public function test_student_cannot_retake_by_default(): void
    {
        $teacher=User::factory()->create(['role'=>'teacher']); $student=User::factory()->create(['role'=>'student']); $exam=$this->exam($teacher);
        Attempt::create(['exam_id'=>$exam->id,'user_id'=>$student->id,'started_at'=>now()->subMinutes(10),'submitted_at'=>now(),'status'=>'submitted','total_points'=>10,'score'=>10]);
        $this->actingAs($student)->post(route('attempts.start',$exam))->assertForbidden();
    }

    public function test_admin_can_enable_retake_and_limit_attempts(): void
    {
        $admin=User::factory()->create(['role'=>'admin']); $teacher=User::factory()->create(['role'=>'teacher']); $student=User::factory()->create(['role'=>'student']); $exam=$this->exam($teacher,['allow_retake'=>true,'attempt_limit'=>2]);
        Attempt::create(['exam_id'=>$exam->id,'user_id'=>$student->id,'started_at'=>now()->subMinutes(10),'submitted_at'=>now(),'status'=>'submitted','total_points'=>10,'score'=>10]);
        $this->actingAs($student)->post(route('attempts.start',$exam))->assertRedirect();
        $attempt=Attempt::where('exam_id',$exam->id)->where('user_id',$student->id)->where('status','in_progress')->firstOrFail();
        $this->actingAs($student)->post(route('attempts.submit',$attempt),['answers'=>[]])->assertRedirect();
        $this->actingAs($student)->post(route('attempts.start',$exam))->assertForbidden();
    }

    public function test_expired_attempt_is_finished_server_side(): void
    {
        $teacher=User::factory()->create(['role'=>'teacher']); $student=User::factory()->create(['role'=>'student']); $exam=$this->exam($teacher);
        $attempt=Attempt::create(['exam_id'=>$exam->id,'user_id'=>$student->id,'started_at'=>now()->subMinutes(31),'status'=>'in_progress','total_points'=>10]);
        $correct=$exam->questions()->first()->options()->where('is_correct',true)->first();
        $this->actingAs($student)->post(route('attempts.submit',$attempt),['answers'=>[$exam->questions()->first()->id=>$correct->id]])->assertRedirect();
        $this->assertDatabaseHas('attempts',['id'=>$attempt->id,'status'=>'submitted']);
    }

    public function test_invalid_correct_option_is_rejected(): void
    {
        $teacher=User::factory()->create(['role'=>'teacher']); $exam=$this->exam($teacher,['is_published'=>false]);
        $this->actingAs($teacher)->post(route('exams.questions.store',$exam),['body'=>'Question','points'=>1,'options'=>['A','B'],'correct'=>9])->assertSessionHasErrors('correct');
    }

    public function test_teacher_cannot_publish_exam_without_questions(): void
    {
        $teacher=User::factory()->create(['role'=>'teacher']); $exam=Exam::create(['created_by'=>$teacher->id,'title'=>'Empty','duration_minutes'=>30]);
        $this->actingAs($teacher)->put(route('exams.update',$exam),['title'=>'Empty','duration_minutes'=>30,'is_published'=>1])->assertSessionHasErrors('is_published');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\{Exam,Attempt};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AttemptController extends Controller
{
    public function start(Exam $exam)
    {
        $user = auth()->user();
        abort_unless($user->isStudent(),403);
        abort_unless($exam->isOpen(),403,'This examination is not available.');
        abort_if(!$exam->questions()->exists(),422,'This examination has no questions.');

        $attempt = DB::transaction(function () use ($exam, $user) {
            $lockedExam = Exam::whereKey($exam->id)->lockForUpdate()->firstOrFail();
            $active = Attempt::where('exam_id',$lockedExam->id)->where('user_id',$user->id)->where('status','in_progress')->first();
            if ($active) return $active;
            $completed = Attempt::where('exam_id',$lockedExam->id)->where('user_id',$user->id)->where('status','submitted')->count();
            abort_if($completed > 0 && !$lockedExam->allow_retake,403,'Retakes are not allowed for this examination.');
            abort_if($lockedExam->allow_retake && $lockedExam->attempt_limit !== null && $completed >= $lockedExam->attempt_limit,403,'You have reached the maximum number of attempts.');
            return Attempt::create(['exam_id'=>$lockedExam->id,'user_id'=>$user->id,'started_at'=>now(),'status'=>'in_progress','duration_minutes'=>$lockedExam->duration_minutes,'total_points'=>$lockedExam->questions()->sum('points')]);
        });
        return redirect()->route('attempts.show',$attempt);
    }

    public function show(Attempt $attempt)
    {
        abort_unless($attempt->user_id === auth()->id(),403);
        abort_unless($attempt->isActive(),403,'Attempt already submitted.');
        $attempt->load('exam.questions.options');
        if ($attempt->hasExpired()) return $this->finish($attempt,[],'Exam time expired.');
        $deadline = $attempt->deadline();
        return view('attempts.show',compact('attempt','deadline'));
    }

    public function save(Request $r, Attempt $attempt)
    {
        abort_unless($attempt->user_id === auth()->id() && $attempt->isActive(),403);
        $data = $r->validate(['answers'=>'nullable|array']);
        if ($attempt->hasExpired()) return $this->finish($attempt,$data['answers']??[],'Exam time expired.');
        return $this->finish($attempt,$data['answers']??[],'Exam submitted successfully.');
    }

    private function finish(Attempt $attempt,array $answers,string $message)
    {
        return DB::transaction(function() use($attempt,$answers,$message){
            $attempt = Attempt::whereKey($attempt->id)->lockForUpdate()->firstOrFail();
            if (!$attempt->isActive()) return redirect()->route('results.show',$attempt);
            $exam=$attempt->exam->load('questions.options'); $questions=$exam->questions; $score=0; $totalPoints=$questions->sum('points');
            foreach($questions as $q){
                $optionId=$answers[$q->id]??null;
                $option=$optionId ? $q->options->firstWhere('id',(int)$optionId) : null;
                $correct=(bool) optional($option)->is_correct;
                $points=$correct?(float)$q->points:0;
                $attempt->answers()->updateOrCreate(['question_id'=>$q->id],['option_id'=>$option?->id,'is_correct'=>$correct,'points_awarded'=>$points]);
                $score+=$points;
            }
            $attempt->update(['score'=>$score,'total_points'=>$totalPoints,'submitted_at'=>now(),'status'=>'submitted']);
            return redirect()->route('results.show',$attempt)->with('success',$message);
        });
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\{Exam,Question};
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    private function own(Exam $e){ if(!auth()->user()->isAdmin() && $e->created_by !== auth()->id()) abort(403); }
    private function hasAttempts(Exam $exam): bool
    {
        return $exam->attempts()->withTrashed()->whereIn('status',['submitted','in_progress'])->exists();
    }
    public function index(Exam $exam){ $this->own($exam); $exam->load('questions.options'); return view('questions.index', compact('exam')); }

    public function store(Request $r, Exam $exam)
    {
        $this->own($exam);
        if ($this->hasAttempts($exam)) {
            return back()->withInput()->withErrors(['exam'=>'Questions cannot be added after an attempt has started. Unpublish the exam or create a new version instead.']);
        }
        $d = $r->validate([
            'body'=>'required|string|max:10000', 'points'=>'required|numeric|min:.1|max:100',
            'options'=>'required|array|min:2|max:6', 'options.*'=>'required|string|max:500',
            'correct'=>'required|integer|min:0',
        ]);
        if ($d['correct'] >= count($d['options'])) {
            return back()->withInput()->withErrors(['correct'=>'Select a valid correct option.']);
        }
        $q = $exam->questions()->create(['body'=>$d['body'],'type'=>'single','points'=>$d['points'],'position'=>$exam->questions()->max('position') + 1]);
        foreach($d['options'] as $i=>$label) $q->options()->create(['label'=>$label,'is_correct'=>$i===$d['correct'],'position'=>$i+1]);
        return back()->with('success','Question added.');
    }

    public function destroy(Exam $exam, Question $question)
    {
        $this->own($exam);
        abort_unless($question->exam_id === $exam->id,404);
        if ($this->hasAttempts($exam)) {
            return back()->withErrors(['exam'=>'Questions cannot be removed after an attempt has started. Unpublish the exam or create a new version instead.']);
        }
        $question->delete();
        return back()->with('success','Question removed.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use Illuminate\Http\Request;

class ExamController extends Controller
{
    public function index()
    {
        $u = auth()->user();
        $exams = $u->isStudent()
            ? Exam::published()->latest()->paginate(12)
            : ($u->isAdmin() ? Exam::with('creator')->latest()->paginate(12) : Exam::where('created_by', $u->id)->latest()->paginate(12));
        return view('exams.index', compact('exams'));
    }

    public function create(){ return view('exams.form', ['exam' => new Exam]); }

    public function store(Request $r)
    {
        $d = $this->validated($r);
        $d['created_by'] = auth()->id();
        $d['is_published'] = false;
        $d['allow_retake'] = false;
        $d['attempt_limit'] = null;
        $exam = Exam::create($d);
        return redirect()->route('exams.questions.index', $exam)->with('success', 'Exam created. Add questions before publishing.');
    }

    public function edit(Exam $exam){ $this->own($exam); return view('exams.form', compact('exam')); }

    public function update(Request $r, Exam $exam)
    {
        $this->own($exam);
        $d = $this->validated($r);
        if ($r->boolean('is_published') && !$exam->questions()->exists()) {
            return back()->withInput()->withErrors(['is_published' => 'Add at least one question before publishing this exam.']);
        }
        $d['is_published'] = $r->boolean('is_published');
        if (auth()->user()->isAdmin()) {
            $d['allow_retake'] = $r->boolean('allow_retake');
            $d['attempt_limit'] = $d['allow_retake'] ? $r->input('attempt_limit') : null;
        } else {
            $d['allow_retake'] = $exam->allow_retake;
            $d['attempt_limit'] = $exam->attempt_limit;
        }
        $exam->update($d);
        return back()->with('success', 'Exam updated.');
    }

    public function destroy(Exam $exam)
    {
        $this->own($exam);
        if ($exam->attempts()->withTrashed()->whereIn('status',['submitted','in_progress'])->exists()) {
            return back()->withErrors(['exam' => 'This exam cannot be deleted because it has recorded attempts. Unpublish it or create a new version instead.']);
        }
        $exam->delete();
        return redirect()->route('exams.index')->with('success', 'Exam deleted.');
    }

    private function validated(Request $r): array
    {
        $rules = [
            'title'=>'required|string|max:180', 'description'=>'nullable|string',
            'duration_minutes'=>'required|integer|min:1|max:600', 'starts_at'=>'nullable|date',
            'ends_at'=>'nullable|date|after_or_equal:starts_at',
        ];
        if (auth()->user()->isAdmin()) {
            $rules['attempt_limit'] = 'nullable|integer|min:2|max:100';
        }
        return $r->validate($rules);
    }

    private function own(Exam $exam){ if(!auth()->user()->isAdmin() && $exam->created_by !== auth()->id()) abort(403); }
}

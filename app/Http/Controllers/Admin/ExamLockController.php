<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Exam;

class ExamLockController extends Controller
{
    public function toggle(Request $request, $id)
    {
        $exam = Exam::findOrFail($id);
        $exam->is_active = !$exam->is_active;
        $exam->save();

        $status = $exam->is_active ? 'Activated' : 'Deactivated';
        return redirect()->back()->with('success', "Exam {$status} Successfully!");
    }
}

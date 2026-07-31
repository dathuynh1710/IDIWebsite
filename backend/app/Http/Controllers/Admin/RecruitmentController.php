<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JobPosition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class RecruitmentController extends Controller
{
    public function preview(Request $request, JobPosition $position)
    {
        Gate::authorize('recruitment.view');
        $locale = in_array($request->string('locale')->toString(), ['vi', 'en', 'zh'], true)
            ? $request->string('locale')->toString() : 'vi';
        return view('admin.recruitment.preview', compact('position', 'locale'));
    }
}

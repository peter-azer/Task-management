<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $activities = Activity::with(['causer', 'subject'])
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return view('activity-log.index', compact('activities'));
    }
}

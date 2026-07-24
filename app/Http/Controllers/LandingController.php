<?php

namespace App\Http\Controllers;

use App\Models\Job;
use Illuminate\Http\Request;

class LandingController extends Controller
{
    public function index(Request $request)
    {
        $jobs = Job::with('company')->where('status', 'open')->latest()->take(4)->get();
        $openJobsCount = Job::where('status', 'open')->count();

        return view('landing', compact('jobs', 'openJobsCount'));
    }

    public function allJobs(Request $request)
    {
        $query = Job::with('company')->where('status', 'open');

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('location', 'like', '%' . $request->search . '%');
            });
        }

        $jobs = $query->latest()->paginate(9)->withQueryString();

        return view('jobs-all', compact('jobs'));
    }
}
<?php

namespace App\Http\Controllers;

use App\Models\Job;
use Illuminate\Http\Request;

class LandingController extends Controller
{
    public function index(Request $request)
    {
        $jobType = $request->input('job_type', 'all'); // all | local | overseas | job_fair

        $query = Job::with('company')->where('status', 'open')
            ->when($jobType === 'local', fn($q) => $q->whereHas('company', fn($c) => $c->where('is_overseas', false)))
            ->when($jobType === 'overseas', fn($q) => $q->whereHas('company', fn($c) => $c->where('is_overseas', true)))
            ->when($jobType === 'job_fair', fn($q) => $q->where('schedule_type', 'job_fair'));

        $jobs = $query->latest()->take(4)->get();
        $openJobsCount = Job::where('status', 'open')->count();

        return view('landing', compact('jobs', 'openJobsCount', 'jobType'));
    }

    public function allJobs(Request $request)
    {
        $jobType = $request->input('job_type', 'all'); // all | local | overseas | job_fair

        $query = Job::with('company')->where('status', 'open')
            ->when($jobType === 'local', fn($q) => $q->whereHas('company', fn($c) => $c->where('is_overseas', false)))
            ->when($jobType === 'overseas', fn($q) => $q->whereHas('company', fn($c) => $c->where('is_overseas', true)))
            ->when($jobType === 'job_fair', fn($q) => $q->where('schedule_type', 'job_fair'));

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('location', 'like', '%' . $request->search . '%');
            });
        }

        $jobs = $query->latest()->paginate(9)->withQueryString();

        return view('jobs-all', compact('jobs', 'jobType'));
    }
}   
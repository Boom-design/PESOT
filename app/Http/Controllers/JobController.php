<?php

namespace App\Http\Controllers;

use App\Models\Job;
use Illuminate\Http\Request;

class JobController extends Controller
{
    // ── GET ALL OPEN JOBS (Browse Jobs page) ─────────────
    public function index(Request $request)
    {
        $query = Job::with('company.employer:id,email', 'company:id,user_id,company_name')
            ->where('status', 'open')
            ->orderBy('created_at', 'desc');

        // Search filter
        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%$search%")
                  ->orWhere('location', 'like', "%$search%")
                  ->orWhere('description', 'like', "%$search%");
            });
        }

        // Type filter (full_time, part_time, contractual)
        if ($request->has('type') && $request->type !== '') {
            $query->where('type', $request->type);
        }

        $jobs = $query->get()->map(function ($job) {
            return [
                'id'           => $job->job_qualifications_id,
                'title'        => $job->title,
                'description'  => $job->description,
                'location'     => $job->location,
                'type'         => $job->type,
                'slots'        => $job->slots,
                'deadline'     => $job->deadline,
                'status'       => $job->status,
                'company_name' => $job->company->company_name ?? 'Unknown',
                'posted_at'    => $job->created_at->format('M d, Y'),
                'created_at'   => $job->created_at,
            ];
        });

        return response()->json([
            'jobs'  => $jobs,
            'total' => $jobs->count(),
        ], 200);
    }

    // ── GET SINGLE JOB (Job Details page) ────────────────
    public function show($id)
    {
        $job = Job::with('company.employer:id,email', 'company:id,user_id,company_name,mobile_number')
            ->where('status', 'open')
            ->findOrFail($id);

        return response()->json([
            'job' => [
                'id'           => $job->job_qualifications_id,
                'title'        => $job->title,
                'description'  => $job->description,
                'location'     => $job->location,
                'type'         => $job->type,
                'slots'        => $job->slots,
                'deadline'     => $job->deadline,
                'status'       => $job->status,
                'company_name' => $job->company->company_name ?? 'Unknown',
                'company_email'=> $job->company->employer->email ?? '',
                'posted_at'    => $job->created_at->format('M d, Y'),
            ],
        ], 200);
    }

    // ── GET LATEST JOBS (Dashboard — Latest Jobs section) ─
    public function latest()
    {
        $jobs = Job::with('company:id,user_id,company_name')
            ->where('status', 'open')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->map(function ($job) {
                return [
                    'id'           => $job->job_qualifications_id,
                    'title'        => $job->title,
                    'location'     => $job->location,
                    'type'         => $job->type,
                    'company_name' => $job->company->company_name ?? 'Unknown',
                    'posted_at'    => $job->created_at->format('M d, Y'),
                    'deadline'     => $job->deadline,
                ];
            });

        return response()->json(['jobs' => $jobs], 200);
    }

    // ── GET COUNT (Dashboard — Jobs Available stat card) ──
    public function count()
    {
        $count = Job::where('status', 'open')->count();

        return response()->json(['count' => $count], 200);
    }
}
<?php

namespace Tests\Feature;

use App\Models\EmployerNsrpRegistration;
use App\Models\Job;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminReportsSolicitationStatsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_reports_show_job_solicitation_statistics(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'approved',
        ]);

        $localEmployer = EmployerNsrpRegistration::create([
            'user_id' => User::factory()->create(['role' => 'company', 'status' => 'approved'])->id,
            'company_name' => 'Local Company',
            'contact_person' => 'Local Contact',
            'position_title' => 'Manager',
            'mobile_number' => '09123456789',
            'employer_type' => 'Local',
            'is_overseas' => false,
        ]);

        $overseasEmployer = EmployerNsrpRegistration::create([
            'user_id' => User::factory()->create(['role' => 'company', 'status' => 'approved'])->id,
            'company_name' => 'Overseas Company',
            'contact_person' => 'Overseas Contact',
            'position_title' => 'Manager',
            'mobile_number' => '09123456788',
            'employer_type' => 'Overseas',
            'is_overseas' => true,
        ]);

        Job::create([
            'company_id' => $localEmployer->id,
            'title' => 'Local Solicitation',
            'description' => 'Local solicitation description',
            'location' => 'Cebu',
            'type' => 'permanent',
            'industry_group' => 'IT',
            'posting_type' => 'direct',
            'status' => 'open',
            'posting_status' => 'approved',
            'slots' => 1,
        ]);

        Job::create([
            'company_id' => $overseasEmployer->id,
            'title' => 'Overseas Solicitation',
            'description' => 'Overseas solicitation description',
            'location' => 'Cebu',
            'type' => 'permanent',
            'industry_group' => 'IT',
            'posting_type' => 'direct',
            'status' => 'open',
            'posting_status' => 'approved',
            'slots' => 1,
        ]);

        Job::create([
            'company_id' => $localEmployer->id,
            'title' => 'Non Direct Job',
            'description' => 'Non direct job description',
            'location' => 'Cebu',
            'type' => 'permanent',
            'industry_group' => 'IT',
            'posting_type' => 'job_fair',
            'status' => 'open',
            'posting_status' => 'approved',
            'slots' => 1,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.reports.staff', ['role' => 'lra']));

        $response->assertStatus(200)
            ->assertSee('LRA Job Solicitation')
            ->assertSee('SRA Job Solicitation')
            ->assertSee('Overall Job Solicitation');
    }
}

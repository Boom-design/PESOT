<?php

namespace Tests\Feature;

use App\Models\EmployerNsrpRegistration;
use App\Models\Job;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminReportsJobVacancyTopEmployersTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_vacancy_reports_show_top_employers_for_company_interview_interviews(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'approved']);
        $this->actingAs($admin);

        $employerA = EmployerNsrpRegistration::create([
            'user_id' => User::factory()->create()->id,
            'company_name' => 'Tech Solutions Inc',
            'contact_person' => 'Alice',
            'position_title' => 'Manager',
            'mobile_number' => '09100000005',
            'employer_type' => 'Local Employer',
            'is_overseas' => false,
            'certification_agreed' => true,
            'certification_date' => now()->toDateString(),
        ]);

        $employerB = EmployerNsrpRegistration::create([
            'user_id' => User::factory()->create()->id,
            'company_name' => 'Digital Services Ltd',
            'contact_person' => 'Bob',
            'position_title' => 'Manager',
            'mobile_number' => '09100000006',
            'employer_type' => 'Local Employer',
            'is_overseas' => false,
            'certification_agreed' => true,
            'certification_date' => now()->toDateString(),
        ]);

        // Create company interview jobs for employer A (2 jobs)
        Job::create([
            'company_id' => $employerA->id,
            'title' => 'Software Engineer',
            'schedule_type' => 'company_interview',
            'posting_status' => 'approved',
            'slots' => 5,
            'updated_at' => now(),
        ]);

        Job::create([
            'company_id' => $employerA->id,
            'title' => 'System Administrator',
            'schedule_type' => 'company_interview',
            'posting_status' => 'approved',
            'slots' => 3,
            'updated_at' => now(),
        ]);

        // Create company interview jobs for employer B (1 job)
        Job::create([
            'company_id' => $employerB->id,
            'title' => 'Network Engineer',
            'schedule_type' => 'company_interview',
            'posting_status' => 'approved',
            'slots' => 4,
            'updated_at' => now(),
        ]);

        // Create a non-company-interview job (should not be counted)
        Job::create([
            'company_id' => $employerA->id,
            'title' => 'Remote Developer',
            'schedule_type' => 'remote',
            'posting_status' => 'approved',
            'slots' => 2,
            'updated_at' => now()->subMonth(),
        ]);

        $response = $this->get(route('admin.reports.staffJobVacancy', [
            'tab' => 'top_employers',
            'top_employers_filter' => 'monthly',
            'top_employers_month' => now()->format('Y-m'),
        ]));

        $response->assertStatus(200);
        $response->assertSeeText('Top 5 Employers by Company Interview Interview Participation');
        $response->assertSeeText('Monthly');
        $response->assertSeeText('Yearly');
        $response->assertSeeText('Tech Solutions Inc');
        $response->assertSeeText('Digital Services Ltd');
        $response->assertSeeText('2'); // Tech Solutions Inc has 2 company interview jobs
    }

    public function test_job_vacancy_reports_top_employers_yearly_filter(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'approved']);
        $this->actingAs($admin);

        $employer = EmployerNsrpRegistration::create([
            'user_id' => User::factory()->create()->id,
            'company_name' => 'Future Corp',
            'contact_person' => 'Charlie',
            'position_title' => 'Manager',
            'mobile_number' => '09100000007',
            'employer_type' => 'Local Employer',
            'is_overseas' => false,
            'certification_agreed' => true,
            'certification_date' => now()->toDateString(),
        ]);

        // Create company interview jobs in current year
        Job::create([
            'company_id' => $employer->id,
            'title' => 'Developer',
            'schedule_type' => 'company_interview',
            'posting_status' => 'approved',
            'slots' => 5,
            'updated_at' => now(),
        ]);

        Job::create([
            'company_id' => $employer->id,
            'title' => 'Analyst',
            'schedule_type' => 'company_interview',
            'posting_status' => 'approved',
            'slots' => 3,
            'updated_at' => now(),
        ]);

        $response = $this->get(route('admin.reports.staffJobVacancy', [
            'tab' => 'top_employers',
            'top_employers_filter' => 'yearly',
            'top_employers_year' => now()->year,
        ]));

        $response->assertStatus(200);
        $response->assertSeeText('Future Corp');
        $response->assertSeeText('2');
    }
}

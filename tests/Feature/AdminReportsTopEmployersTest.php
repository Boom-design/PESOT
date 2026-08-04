<?php

namespace Tests\Feature;

use App\Models\EmployerNsrpRegistration;
use App\Models\InhouseSchedule;
use App\Models\JobFairEvent;
use App\Models\JobFairParticipant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminReportsTopEmployersTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_reports_show_top_five_employers_for_current_month_inhouse_interviews(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'approved']);
        $this->actingAs($admin);

        $employerA = EmployerNsrpRegistration::create([
            'user_id' => User::factory()->create()->id,
            'company_name' => 'Alpha Company',
            'contact_person' => 'Alice',
            'position_title' => 'Manager',
            'mobile_number' => '09100000001',
            'employer_type' => 'Local Employer',
            'is_overseas' => false,
            'certification_agreed' => true,
            'certification_date' => now()->toDateString(),
        ]);

        $employerB = EmployerNsrpRegistration::create([
            'user_id' => User::factory()->create()->id,
            'company_name' => 'Beta Company',
            'contact_person' => 'Bob',
            'position_title' => 'Manager',
            'mobile_number' => '09100000002',
            'employer_type' => 'Local Employer',
            'is_overseas' => false,
            'certification_agreed' => true,
            'certification_date' => now()->toDateString(),
        ]);

        InhouseSchedule::create([
            'employer_id' => $employerA->id,
            'preferred_date' => now()->toDateString(),
            'preferred_time' => '09:00:00',
            'num_applicants' => 10,
            'status' => 'accepted',
            'confirmed_date' => now()->toDateString(),
            'confirmed_time' => '09:00:00',
        ]);

        InhouseSchedule::create([
            'employer_id' => $employerA->id,
            'preferred_date' => now()->toDateString(),
            'preferred_time' => '10:00:00',
            'num_applicants' => 10,
            'status' => 'accepted',
            'confirmed_date' => now()->toDateString(),
            'confirmed_time' => '10:00:00',
        ]);

        InhouseSchedule::create([
            'employer_id' => $employerB->id,
            'preferred_date' => now()->toDateString(),
            'preferred_time' => '11:00:00',
            'num_applicants' => 10,
            'status' => 'accepted',
            'confirmed_date' => now()->toDateString(),
            'confirmed_time' => '11:00:00',
        ]);

        InhouseSchedule::create([
            'employer_id' => $employerB->id,
            'preferred_date' => now()->subMonth()->toDateString(),
            'preferred_time' => '12:00:00',
            'num_applicants' => 10,
            'status' => 'accepted',
            'confirmed_date' => now()->subMonth()->toDateString(),
            'confirmed_time' => '12:00:00',
        ]);

        $response = $this->get(route('admin.reports.staff', [
            'role' => 'lra',
            'tab' => 'top_employers',
            'top_employers_filter' => 'monthly',
            'top_employers_month' => now()->format('Y-m'),
        ]));

        $response->assertStatus(200);
        $response->assertSeeText('Top 5 Employers by In-House Interviews');
        $response->assertSeeText('Monthly');
        $response->assertSeeText('Yearly');
        $response->assertSeeText('Alpha Company');
        $response->assertSeeText('Beta Company');
        $response->assertSeeText('2');
    }

    public function test_job_fair_reports_show_top_employers_for_selected_event(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'approved']);
        $this->actingAs($admin);

        $staff = \App\Models\Staff::create([
            'user_id' => $admin->id,
            'staff_role' => 'job_fair',
            'first_name' => 'Admin',
            'last_name' => 'User',
            'phone' => '09100000000',
        ]);

        $event = JobFairEvent::create([
            'created_by' => $staff->id,
            'title' => 'Summer Job Fair',
            'event_date' => now()->toDateString(),
            'event_time' => '09:00:00',
            'venue' => 'Main Hall',
            'status' => 'upcoming',
        ]);

        $employerA = EmployerNsrpRegistration::create([
            'user_id' => User::factory()->create()->id,
            'company_name' => 'Job Fair Alpha',
            'contact_person' => 'Alice',
            'position_title' => 'Manager',
            'mobile_number' => '09100000003',
            'employer_type' => 'Local Employer',
            'is_overseas' => false,
            'certification_agreed' => true,
            'certification_date' => now()->toDateString(),
        ]);

        $employerB = EmployerNsrpRegistration::create([
            'user_id' => User::factory()->create()->id,
            'company_name' => 'Job Fair Beta',
            'contact_person' => 'Bob',
            'position_title' => 'Manager',
            'mobile_number' => '09100000004',
            'employer_type' => 'Local Employer',
            'is_overseas' => false,
            'certification_agreed' => true,
            'certification_date' => now()->toDateString(),
        ]);

        JobFairParticipant::create([
            'job_fair_id' => $event->id,
            'employer_id' => $employerA->id,
            'confirmation_status' => 'confirmed',
        ]);

        JobFairParticipant::create([
            'job_fair_id' => $event->id,
            'employer_id' => $employerA->id,
            'confirmation_status' => 'confirmed',
        ]);

        JobFairParticipant::create([
            'job_fair_id' => $event->id,
            'employer_id' => $employerB->id,
            'confirmation_status' => 'confirmed',
        ]);

        $response = $this->get(route('admin.reports.staff', [
            'role' => 'job_fair',
            'tab' => 'top_employers',
            'event_id' => $event->id,
            'top_employers_filter' => 'monthly',
            'top_employers_month' => now()->format('Y-m'),
        ]));

        $response->assertStatus(200);
        $response->assertSeeText('Top 5 Employers by Job Fair Participation');
        $response->assertSeeText('Monthly');
        $response->assertSeeText('Yearly');
        $response->assertSeeText('Job Fair Alpha');
        $response->assertSeeText('Job Fair Beta');
        $response->assertSeeText('2');
    }
}

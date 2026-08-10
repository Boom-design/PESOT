<?php

namespace App\Http\Controllers;

use App\Models\JobseekerRegistration;
use App\Models\Notification;
use Illuminate\Http\Request;

/**
 * RegistrationFormController
 * 
 * Handles jobseeker registration form submissions and retrieval.
 * Manages CRUD operations for jobseeker registration records and
 * creates notifications when new registrations are submitted.
 */
class RegistrationFormController extends Controller
{
    /**
     * Store or Update Jobseeker Registration Form
     * 
     * Receives registration form data from the Flutter app and either creates
     * a new registration record or updates an existing one for the authenticated user.
     * Notifications are created only when a new registration is submitted.
     * 
     * @param Request $request The HTTP request containing registration form data
     * @return \Illuminate\Http\JsonResponse JSON response with registration details or error message
     */
    public function store(Request $request)
    {
        // Get the currently authenticated user
        $user = $request->user();

        // Verify that the user is authenticated and has the 'jobseeker' role
        // Return a 403 Forbidden response if the user is not authorized
        if (!$user || $user->role !== 'jobseeker') {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        // Check if this user already has an existing registration record
        $existing = JobseekerRegistration::where('user_id', $user->users_id)->first();

        // Prepare the registration data by merging the request input with
        // the user_id and setting the initial status to 'submitted'
        $data = array_merge($request->all(), [
            'user_id' => $user->users_id,
            'status'  => 'submitted',
        ]);

        // If a registration already exists, update it with the new data
        // Otherwise, create a new registration record
        if ($existing) {
            $existing->update($data);
            $registration = $existing;
            $isNew = false;
        } else {
            $registration = JobseekerRegistration::create($data);
            $isNew = true;
        }

        // Create a notification only when a new registration is submitted (not on updates)
        // This notifies admins/staff of the new submission
        if ($isNew) {
            // Build the applicant's full name from form fields (surname, first_name, middle_name)
            // Use the user's name as fallback if form fields are not provided
            $name = trim(
                ($request->input('surname') ?? '') . ', ' .
                ($request->input('first_name') ?? '') . ' ' .
                ($request->input('middle_name') ?? '')
            );
            $name = $name ?: $user->name;

            // Create a notification record for admins/staff about the new registration submission
            Notification::create([
                'type'           => 'registration_submitted',
                'title'          => 'New Registration Form',
                'message'        => $name . ' submitted a jobseeker registration form.',
                'is_read'        => false,
                'user_id'        => $user->users_id,
                'reference_type' => 'registration',
                'reference_id'   => $registration->jobseeker_registrations_id,
            ]);
        }

        // Return the registration data with a success message
        return response()->json([
            'message'      => 'Registration form submitted successfully.',
            'registration' => $registration,
        ], 200);
    }

    /**
     * Retrieve the Current User's Registration Form
     * 
     * Fetches the jobseeker registration record for the authenticated user.
     * Returns a 404 error if no registration record exists for the user.
     * 
     * @param Request $request The HTTP request
     * @return \Illuminate\Http\JsonResponse JSON response with registration data or error message
     */
    public function show(Request $request)
    {
        // Get the currently authenticated user
        $user = $request->user();

        // Query the database for the registration record associated with this user
        $registration = JobseekerRegistration::where('user_id', $user->users_id)->first();

        // If no registration record is found, return a 404 Not Found response
        if (!$registration) {
            return response()->json(['message' => 'No registration found.'], 404);
        }

        // Return the registration record in a JSON response
        return response()->json(['registration' => $registration], 200);
    }
}
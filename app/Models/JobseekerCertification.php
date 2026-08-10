<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobseekerCertification extends Model
{
    protected $table = 'jobseeker_certifications';

    protected $primaryKey = 'jobseeker_certifications_id';

    protected $fillable = [
        'jobseeker_nsrp_registration_id',
        'category',
        'name',
        'date_taken',
        'valid_until',
        'certificate_file',
    ];

    protected $casts = [
        'date_taken'  => 'date',
        'valid_until' => 'date',
    ];

    public function nsrpRegistration()
    {
        return $this->belongsTo(JobseekerNsrpRegistration::class, 'jobseeker_nsrp_registration_id');
    }
}
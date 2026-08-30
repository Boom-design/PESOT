<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A report the Job Fair staff keeps by hand and uploads, kept apart from
 * anything the system produces itself.
 *
 * Nothing reads these rows into a total. They are shown as a table and can be
 * downloaded again — that is all. See the migration for why the columns are
 * whatever the file happened to contain.
 */
class JobFairImportedReport extends Model
{
    protected $primaryKey = 'job_fair_imported_reports_id';

    protected $fillable = [
        'job_fair_id',
        'uploaded_by',
        'title',
        'original_filename',
        'headers',
        'rows',
        'row_count',
    ];

    protected $casts = [
        'headers' => 'array',
        'rows'    => 'array',
    ];

    public function jobFair()
    {
        return $this->belongsTo(JobFairEvent::class, 'job_fair_id');
    }

    public function uploader()
    {
        return $this->belongsTo(Staff::class, 'uploaded_by', 'staff_id');
    }
}

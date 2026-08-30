<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A report the Job Vacancy staff keeps by hand and uploads, kept apart from
 * anything the system produces itself.
 *
 * Nothing reads these rows into a total. They are shown as a table and can be
 * downloaded again — that is all. See the migration for why the columns are
 * whatever the file happened to contain.
 */
class JobVacancyImportedReport extends Model
{
    protected $primaryKey = 'job_vacancy_imported_reports_id';

    protected $fillable = [
        'uploaded_by',
        'title',
        'period',
        'original_filename',
        'headers',
        'rows',
        'row_count',
    ];

    protected $casts = [
        'headers' => 'array',
        'rows'    => 'array',
    ];

    public function uploader()
    {
        return $this->belongsTo(Staff::class, 'uploaded_by', 'staff_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// ── Rekord sa kada HR handover sa usa ka employer account. Basaha ang
// ── migration para sa rason nganong permanente kini. ──
class EmployerAccountTransfer extends Model
{
    protected $primaryKey = 'employer_account_transfers_id';

    protected $fillable = [
        'employer_id',
        'performed_by',
        'performed_by_user_id',
        'method',
        'previous_contact_person',
        'previous_email',
        'new_contact_person',
        'new_email',
        'reason',
    ];

    public function employer()
    {
        return $this->belongsTo(EmployerNsrpRegistration::class, 'employer_id');
    }

    public function performer()
    {
        return $this->belongsTo(Staff::class, 'performed_by');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Dandory extends Model
{
    use HasFactory;

    protected $fillable = [
        'ddcnk_id',
        'line_production',
        'customer',
        'nama_part',
        'nomor_part',
        'proses',
        'mesin',
        'qty_pcs',
        'planning_shift',
        'dies_type',
        'estimate_completion',
        'status',
        'check_in',
        'check_out',
        'total_work_time_seconds',
        'notes',
        'assigned_to',
        'added_by',
    ];

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function addedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by');
    }
}

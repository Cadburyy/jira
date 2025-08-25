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
        'status',
        'check_in',
        'check_out',
        'notes',
        'assigned_to',
        'added_by',
    ];

    /**
     * Get the user who is assigned to this dandory ticket.
     */
    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Get the user who created this dandory ticket.
     */
    public function addedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by');
    }
}

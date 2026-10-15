<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImportHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'file_name',
        'inserted',
        'updated',
        'family_processed',
        'family_inserted',
        'family_updated',
        'skipped',
        'sheet_results',
        'skipped_details',
        'errors',
    ];

    protected function casts(): array
    {
        return [
            'sheet_results' => 'array',
            'skipped_details' => 'array',
            'errors' => 'array',
            'inserted' => 'integer',
            'updated' => 'integer',
            'family_processed' => 'integer',
            'family_inserted' => 'integer',
            'family_updated' => 'integer',
            'skipped' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

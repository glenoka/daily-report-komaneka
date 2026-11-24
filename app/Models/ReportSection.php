<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReportSection extends Model
{
    use HasFactory;

    protected $fillable = [
        'daily_report_id',
        'tab_key',
        'section_key',
        'title',
        'field_type',
        'sort_order',
        'content',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    public function report(): BelongsTo
    {
        return $this->belongsTo(DailyReport::class, 'daily_report_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ReportItem::class)->orderBy('sort_order');
    }

    public function isList(): bool
    {
        return $this->field_type === 'list';
    }

    public function isText(): bool
    {
        return $this->field_type === 'text';
    }
}

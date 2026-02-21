<?php

namespace App\Models\ServiceProvider;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;
use Carbon\Carbon;

class ServiceProviderSavedColor extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'service_provider_saved_colors';

    protected $fillable = [
        'user_id',
        'name',
        'hex_code',
        'rgb_values',
        'hsl_values',
        'hue',
        'saturation',
        'lightness',
        'contrast_ratio',
        'luminance',
        'temperature',
        'accessibility',
        'color_family',
        'stability',
        'frequency',
        'quantum_state',
        'source_colors',
        'palettes',
        'is_favorite',
    ];

    protected $casts = [
        'source_colors' => 'array',
        'palettes' => 'array',
        'is_favorite' => 'boolean',
        'hue' => 'integer',
        'saturation' => 'integer',
        'lightness' => 'integer',
        'contrast_ratio' => 'float',
        'luminance' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $appends = [
        'created_at_formatted',
        'updated_at_formatted',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getCreatedAtFormattedAttribute(): string
    {
        return $this->created_at ? $this->created_at->format('M d, Y h:i A') : 'N/A';
    }

    public function getUpdatedAtFormattedAttribute(): string
    {
        return $this->updated_at ? $this->updated_at->format('M d, Y h:i A') : 'N/A';
    }

    public function getPalettesAttribute($value): array
    {
        if (!$value) {
            return [
                'monochromatic' => [],
                'analogous' => [],
                'complementary' => [],
            ];
        }
        
        if (is_array($value)) {
            return $value;
        }
        
        try {
            $palettes = json_decode($value, true) ?? [];
        } catch (\Exception $e) {
            $palettes = [];
        }
        
        return [
            'monochromatic' => $palettes['monochromatic'] ?? $palettes[0] ?? [],
            'analogous' => $palettes['analogous'] ?? $palettes[1] ?? [],
            'complementary' => $palettes['complementary'] ?? $palettes[2] ?? [],
        ];
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeFavorites($query)
    {
        return $query->where('is_favorite', true);
    }

    public function scopeByColorFamily($query, $family)
    {
        return $query->where('color_family', $family);
    }

    public function scopeRecent($query, $limit = 10)
    {
        return $query->orderBy('created_at', 'desc')->limit($limit);
    }
}
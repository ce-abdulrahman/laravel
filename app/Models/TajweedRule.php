<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TajweedRule extends Model
{
    use HasFactory, HasTranslations;

    protected $translatable = ['name', 'description'];

    protected $with = ['translations'];

    protected $fillable = [
        'name',
        'name_ku',
        'name_ar',
        'slug',
        'tajweed_rule_category_id',
        'color_code',
        'description',
        'description_ku',
        'example_text',
        'priority',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function ayahTajweedSegments()
    {
        return $this->hasMany(AyahTajweedSegment::class);
    }

    public function category()
    {
        return $this->belongsTo(TajweedRuleCategory::class, 'tajweed_rule_category_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Order rules by parent category's resolved translation name.
     */
    public function scopeOrderByCategoryTranslation($query, string $direction = 'asc')
    {
        $direction = strtolower($direction) === 'desc' ? 'desc' : 'asc';
        $sql = TajweedRuleCategory::getResolvedTranslationSql('name');
        
        // Correlate the category ID field to the rule category ID field
        $sql = str_replace('tajweed_rule_categories.id', 'tajweed_rules.tajweed_rule_category_id', $sql);
        
        return $query->orderByRaw("({$sql}) {$direction}");
    }
}

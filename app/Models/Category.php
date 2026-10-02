<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasFactory, SoftDeletes;

    public const LEVEL_DEPARTMENT  = 'department';
    public const LEVEL_CATEGORY    = 'category';
    public const LEVEL_SUBCATEGORY = 'subcategory';

    protected $fillable = [
        'company_id',
        'parent_id',
        'level',
        'name',
        'code',
        'description',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id')->orderBy('name');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'category_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    public function scopeDepartments($query)
    {
        return $query->where('level', self::LEVEL_DEPARTMENT);
    }

    public function scopeCategories($query)
    {
        return $query->where('level', self::LEVEL_CATEGORY);
    }

    public function scopeSubcategories($query)
    {
        return $query->where('level', self::LEVEL_SUBCATEGORY);
    }

    public function isDepartment(): bool
    {
        return $this->level === self::LEVEL_DEPARTMENT;
    }

    public function isCategory(): bool
    {
        return $this->level === self::LEVEL_CATEGORY;
    }

    public function isSubcategory(): bool
    {
        return $this->level === self::LEVEL_SUBCATEGORY;
    }

    /**
     * Retorna a trilha hierárquica legível (Ex: Bebidas > Refrigerantes > Cola).
     */
    public function getFullPathAttribute(): string
    {
        $path = [$this->name];
        $current = $this->parent;

        while ($current) {
            array_unshift($path, $current->name);
            $current = $current->parent;
        }

        return implode(' > ', $path);
    }
}

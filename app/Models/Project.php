<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // لاستخدام BelongsTo
use Illuminate\Database\Eloquent\Relations\HasMany; // لاستخدام HasMany
use Illuminate\Database\Eloquent\Relations\BelongsToMany; // لاستخدام BelongsToMany

class Project extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'slug',
        'views_count',
    ];

    /**
     * Get the user that owns the project.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the media for the project.
     *
     * @return HasMany
     */
    public function media(): HasMany
    {
        return $this->hasMany(ProjectMedia::class);
    }

    /**
     * Get the categories associated with the project.
     *
     * @return BelongsToMany
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'project_category');
    }

    /**
     * Get the tags associated with the project.
     *
     * @return BelongsToMany
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'project_tag');
    }

    /**
     * Get the likes for the project.
     *
     * @return HasMany
     */
    public function likes(): HasMany
    {
        return $this->hasMany(Like::class);
    }

    /**
     * Get the comments for the project.
     *
     * @return HasMany
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }
}

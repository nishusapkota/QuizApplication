<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Quiz extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = "quizzes";

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int,string>
     */
    protected $fillable = [
        'title', 'slug', 'category_id', 'thumbnail', 'description', 'time', 'retry_after', 'status','pass_percentage'
    ];

    /**
     * @var array<int,string>
     */
    public $appends = [
        'thumbnail_url'
    ];

    /**
     * @return string
     */
    public function getThumbnailUrlAttribute(): string
    {
        return asset($this->thumbnail);
    }

    /**
     * @return BelongsTo
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(QuizCategory::class, 'category_id');
    }

    /**
     * @return BelongsToMany
     */
    public function questionCategories(): BelongsToMany
    {
        return $this->belongsToMany(QuestionCategory::class,"question_category_quiz",'quiz_id','question_category_id')
            ->withTimestamps();
    }

    /**
     * @return HasMany
     */
    public function results(): HasMany
    {
        return $this->hasMany(Result::class, "quiz_id");
    }

    /**
     * @return HasOne
     */
    public function result(): HasOne
    {
        return $this->hasOne(Result::class, "quiz_id")->where("user_id", auth()->id())->latest();
    }

    /**
     * @param Builder $query
     * @return Builder
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status',1);
    }
}

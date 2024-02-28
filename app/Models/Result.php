<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Result extends Model
{
    use HasFactory;

    /**
     * @var array<int,string>
     */
    protected $fillable = [
        'user_id',
        'quiz_id',
        'passed',
        'total_question',
        'total_answered',
        'total_right_answer',
        'total_time'
    ];

    /**
     *
     * @return BelongsTo
     */
    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class, 'quiz_id');
    }
      /**
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @return null|string
     */
    public function getNextRetryAttribute(): string|null
    {
        if($this->passed) return null;
        $quiz = $this->quiz()->select("id","retry_after")->first();
        $retryDate = $this->created_at->addDays($quiz->retry_after);
        if(now()->gte($retryDate)) return null;
        return $retryDate->diffForHumans();
    }
}

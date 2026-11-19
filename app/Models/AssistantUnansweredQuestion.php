<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssistantUnansweredQuestion extends Model
{
    use HasFactory;

    protected $table = 'assistant_unanswered_questions';

    protected $fillable = [
        'pertanyaan',
        'ip_address',
        'user_id',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

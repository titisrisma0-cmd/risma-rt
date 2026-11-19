<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssistantFaq extends Model
{
    use HasFactory;

    protected $table = 'assistant_faqs';

    protected $fillable = [
        'pertanyaan',
        'kata_kunci',
        'jawaban',
        'kategori',
        'urutan',
        'is_active',
        'perlu_tinjauan',
        'catatan_tinjauan',
        'field_pengurus',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'perlu_tinjauan' => 'boolean',
        'urutan' => 'integer',
        'field_pengurus' => 'array',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

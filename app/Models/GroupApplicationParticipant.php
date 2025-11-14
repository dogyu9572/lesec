<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GroupApplicationParticipant extends Model
{
    protected $fillable = [
        'group_application_id',
        'name',
        'grade',
        'class',
        'birthday',
    ];

    protected $casts = [
        'birthday' => 'date',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(GroupApplication::class, 'group_application_id');
    }
}




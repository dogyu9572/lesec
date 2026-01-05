<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SidoSggCode extends Model
{
    use HasFactory;

    protected $table = 'sido_sgg_codes';

    protected $fillable = [
        'sido_code',
        'sido_name',
        'sgg_code',
        'sgg_name',
    ];
}

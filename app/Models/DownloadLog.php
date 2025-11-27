<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DownloadLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'full_name',
        'address',
        'occupation',
        'institution',
        'phone_number',
        'purpose',
        'download_type',
        'file_type',
        'category',
        'filters',
        'ip_address',
        'user_agent',
        'is_seen',
    ];

    protected $casts = [
        'filters' => 'array',
    ];
}

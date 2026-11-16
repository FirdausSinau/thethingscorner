<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'provider',
    'type',
    'event',
    'external_id',
    'status',
    'payload',
    'message',
])]
class IntegrationLog extends Model
{
    public const STATUS_RECEIVED = 'received';

    public const STATUS_PROCESSED = 'processed';

    public const STATUS_FAILED = 'failed';

    public const STATUS_IGNORED = 'ignored';

    public const UPDATED_AT = null;

    /**
     * Mirror database defaults so a newly created model can be checked
     * before it is refreshed from the database.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'status' => self::STATUS_RECEIVED,
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'payload' => 'array',
        ];
    }
}

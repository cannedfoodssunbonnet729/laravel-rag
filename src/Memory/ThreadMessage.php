<?php

declare(strict_types=1);

namespace Moneo\LaravelRag\Memory;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $thread_id
 * @property string $role
 * @property string $content
 * @property int $tokens
 * @property array<string, mixed>|null $metadata
 */
class ThreadMessage extends Model
{
    protected $table = 'rag_thread_messages';

    protected $fillable = [
        'thread_id',
        'role',
        'content',
        'tokens',
        'metadata',
    ];

    protected $casts = [
        'tokens' => 'integer',
        'metadata' => 'array',
    ];

    /**
     * Get the thread that owns this message.
     *
     * @return BelongsTo<RagThread, $this>
     */
    public function thread(): BelongsTo
    {
        return $this->belongsTo(RagThread::class, 'thread_id');
    }
}

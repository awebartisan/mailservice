<?php

namespace App;

use App\Services\BatchMessage;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Batch extends Model
{
    public $guarded = [];

    protected $casts = ['retried_at' => 'date'];

    public static function record(BatchMessage $message, string $service): void
    {
        Batch::create([
            'service' => $service,
            'batch_message_hash' => $message->getHash(),
            'batch_message' => $message->serialize(),
        ]);
    }

    public function scopeWhereService(Builder $query, string $service): void
    {
        $query->where('service', $service);
    }

    public function scopeWhereHash(Builder $query, string $service): void
    {
        $query->where('service', $service);
    }

    public function scopeProcessed(Builder $query): void
    {
        $query->whereNull('retried_at');
    }

    public function scopeRetried(Builder $query): void
    {
        $query->whereNotNull('retried_at');
    }

    public function processedAlready(BatchMessage $message): bool
    {
        return static::whereService(get_current_service())
            ->whereHash($message->getHash())
            ->retried()
            ->exists();
    }

    public function getBatchMessageAttribute($serialized): BatchMessage
    {
        $batchMessage = new BatchMessage();
        $batchMessage->unserialize($serialized);

        return $batchMessage;
    }

    public function markAsRetried()
    {
        $this->retried_at = Carbon::now();
        $this->save();

        return $this;
    }
}
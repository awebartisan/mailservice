<?php

declare(strict_types=1);

namespace App\Http\Controllers\V1;

use App\Jobs\ProcessBatchMessage;
use App\Http\Requests\BatchRequest;
use App\Services\MailClient;

class SendBatchController
{
    public function handle(BatchRequest $request, MailClient $mailClient)
    {
        foreach ($request->batchMessages() as $batchMessage) {
            dispatch(new ProcessBatchMessage($mailClient, $batchMessage));
        }

        return response()->json(['message' => 'success']);
    }
}

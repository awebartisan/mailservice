<?php

namespace App\Http\Controllers;

use App\Batch;
use Illuminate\Http\Request;

class RetryBatchController
{
    public function handle(Request $request)
    {
        $batch = Batch::findOrFail($request->get('batch_id'));

        $mailClient = get_mail_client($batch->service);

        $batchMessage = $mailClient->getFailedRecipients($batch->batch_message, [
            'start_date' => $batch->created_at->format('Y-m-d')
        ]);

        $mailClient = get_mail_client($request->service);

        $mailClient->sendBatch($batchMessage);

        $batch->markAsRetried();

        return redirect('/settings');
    }
}
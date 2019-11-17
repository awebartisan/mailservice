<?php

namespace App\Http\Controllers\V1;

use App\Services\MailClient;
use App\Services\Pepipost\PepipostClient;
use Illuminate\Http\Request;
use App\Jobs\ProcessMailMessage;
use Laravel\Lumen\Routing\ProvidesConvenienceMethods;

class SendMailMessageController
{
    use ProvidesConvenienceMethods;

    public function handle(Request $request, MailClient $mailClient)
    {
        $this->validate($request, [
            'from' => 'required|email',
            'to' => 'required|email',
            'subject' => 'required',
            'body' => 'required',
            'attachment' => 'sometimes|required|url',
        ]);

        if ($mailClient instanceof PepipostClient) {
            $mailClient->sendMessage($request->all());
        } else {
            dispatch(new ProcessMailMessage($mailClient, $request->all()));
        }

        return response()->json(['message' => 'success']);
    }
}
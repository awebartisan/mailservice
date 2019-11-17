<?php

declare(strict_types=1);

namespace App\Http\Controllers\V1;

use Illuminate\Http\Request;
use App\Repositories\MailLogsRepository;
use Laravel\Lumen\Routing\ProvidesConvenienceMethods;

class MailLogsController
{
    use ProvidesConvenienceMethods;

    public function index(Request $request, MailLogsRepository $logsRepository)
    {
        $this->validate($request, [
            'service' => 'required|in:mailgun,pepipost',
            'items' => 'sometimes|integer',
        ]);

        $key = "{$request->service}:logs";

        $logs = $logsRepository->getLogs(0,(int) $request->get('items', 10), $key);

        return response()->json($logs);
    }
}
<?php

namespace App\Http\Controllers;

use App\Batch;

class BatchSettingsController
{
    public function index()
    {
        $batches = Batch::all();

        return view('settings', compact('batches'));
    }
}
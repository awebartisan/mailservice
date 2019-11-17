<?php

abstract class TestCase extends Laravel\Lumen\Testing\TestCase
{
    /**
     * Creates the application.
     *
     * @return \Laravel\Lumen\Application
     */
    public function createApplication()
    {
        return require __DIR__.'/../bootstrap/app.php';
    }

    public function data($response)
    {
        return $response->response->original;
    }

    public function authHeaders()
    {
        return ['signature' => env('AUTH_SIGNATURE')];
    }
}

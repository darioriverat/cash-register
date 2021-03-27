<?php

namespace App\Providers;

use Illuminate\Support\Facades\Response;
use Illuminate\Support\ServiceProvider;

class ApiResponseProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        Response::macro('rest', function ($data = [], $status = 200) {
            return Response::json($data, $status)->withHeaders(['Content-Type' => 'application/json;charset=UTF-8']);
        });
    }
}

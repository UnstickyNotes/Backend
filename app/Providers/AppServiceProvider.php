<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Response;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Response::macro('success', function($data, $message, $code){
            return response()->json([
                'status'=>true,
                'message'=>$message,
                'data'=>$data
            ],$code);
        });

        Response::macro('error', function($message = 'Error', $code){
            return response()->json([
                'status' =>false,
                'message'=>$message,
            ], $code);
        });
    }
}

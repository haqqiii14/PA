<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\AbsensiController;


Route::get('/', function () {
    return view('welcome');  
});



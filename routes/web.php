<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});
//checking user trial on diferent controller from router group with midleware
Route::group(['middleware'=>'trailcheck'],function(){
    Route::get('dashboard', '\App\Http\Admin\Dashboard@trialTest')->name('dashboard');
    Route::get('payment', \App\Admin\Payment::class)->name('payment');
});

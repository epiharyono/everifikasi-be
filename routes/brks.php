<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BANK\BRKSController as BRKS;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('/', function () {
      return response()->json([
          'success' => false,
          'message' => 'Server API BRKS Running',
          'date'  => date('Y-m-d'),
      ], 200);
});

// , 'middleware'=>'jwt.verify'
Route::post('/api/v2/get-token',[BRKS::class,'GetToken'])->middleware('auth.basic.hardcode');
Route::post('/api/v2/bank-callbacks',[BRKS::class,'BankCallback'])->middleware('jwt.verify');

Route::group(['prefix'=>'api/v2', 'middleware'=>'auth.basic.hardcode'], function() {
    Route::post('/bank-callback',[BRKS::class,'BankCallback']);
});

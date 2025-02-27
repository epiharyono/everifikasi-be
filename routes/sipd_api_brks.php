<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BANK\SIPDController as SIPD;

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
          'message' => 'Server API E-Verifikasi Running',
          'date'  => date('Y-m-d'),
      ], 200);
});

// , 'middleware'=>'jwt.verify'
Route::group(['prefix'=>'v2'], function() {
    Route::get('/',[SIPD::class,'index']);
    Route::get('/{satu}',[SIPD::class,'IndexRouteSatu']);
    Route::get('/{satu}/{dua}',[SIPD::class,'IndexRouteDua']);
    Route::get('/{satu}/{dua}/{tiga}',[SIPD::class,'IndexRouteTiga']);

    Route::post('/',[SIPD::class,'index']);
    Route::post('/{satu}',[SIPD::class,'IndexRouteSatu']);
    Route::post('/{satu}/{dua}',[SIPD::class,'IndexRouteDua']);
    Route::post('/{satu}/{dua}/{tiga}',[SIPD::class,'IndexRouteTiga']);
});

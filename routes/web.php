<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Sipd\RouteController as SipdRoutes;
use App\Http\Controllers\Kasda\KasdaController as KASDA;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


Route::get('/', function () {
      return response()->json([
          'success' => false,
          'message' => 'Server Web E-Verifikasi Running',
          'date'  => date('Y-m-d'),
      ], 200);
});

Route::group(['prefix'=>'sipd'], function() {
    Route::get('/',[SipdRoutes::class,'index']);
    Route::get('/{satu}',[SipdRoutes::class,'IndexRouteSatu']);
    Route::get('/{satu}/{dua}',[SipdRoutes::class,'IndexRouteDua']);
    Route::get('/{satu}/{dua}/{tiga}',[SipdRoutes::class,'IndexRouteTiga']);

    Route::post('/',[SipdRoutes::class,'index']);
    Route::post('/{satu}',[SipdRoutes::class,'IndexRouteSatu']);
    Route::post('/{satu}/{dua}',[SipdRoutes::class,'IndexRouteDua']);
    Route::post('/{satu}/{dua}/{tiga}',[SipdRoutes::class,'IndexRouteTiga']);
})->middleware('cors');

Route::group(['prefix'=>'kasda'], function() {
    Route::get('/',[KASDA::class,'index']);
    Route::get('/conn.aspx',[KASDA::class,'Connection']);
    Route::get('/inqueryAll',[KASDA::class,'InqueryAll']);
    Route::get('/payment.aspx',[KASDA::class,'Payment']);
})->middleware('cors');










// fds

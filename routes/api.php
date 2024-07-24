<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Users\RouteController as UserRoutes;

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
Route::group(['prefix'=>'user'], function() {
    Route::get('/',[UserRoutes::class,'index']);
    Route::get('/{satu}',[UserRoutes::class,'IndexRouteSatu']);
    Route::get('/{satu}/{dua}',[UserRoutes::class,'IndexRouteDua']);
    Route::get('/{satu}/{dua}/{tiga}',[UserRoutes::class,'IndexRouteTiga']);

    Route::post('/',[UserRoutes::class,'index']);
    Route::post('/{satu}',[UserRoutes::class,'IndexRouteSatu']);
    Route::post('/{satu}/{dua}',[UserRoutes::class,'IndexRouteDua']);
    Route::post('/{satu}/{dua}/{tiga}',[UserRoutes::class,'IndexRouteTiga']);
});

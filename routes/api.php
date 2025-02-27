<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Users\RouteController as UserRoutes;
use App\Http\Controllers\Sipd\RouteController as SipdRoutes;
use App\Http\Controllers\Master\RouteController as MasterRoutes;
use App\Http\Controllers\SPP\RouteController as SPPRoutes;
use App\Http\Controllers\SPM\RouteController as SPMRoutes;
use App\Http\Controllers\SPPD\RouteController as SPPDRoutes;
use App\Http\Controllers\Pengajuan\RouteController as PengajuanRoutes;
use App\Http\Controllers\DPA\RouteController as DPARoutes;
use App\Http\Controllers\BANK\RouteController as BANKRoutes;
use App\Http\Controllers\NPD\RouteController as NPDRoutes;

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

Route::group(['prefix'=>'master'], function() {
    Route::get('/',[MasterRoutes::class,'index']);
    Route::get('/{satu}',[MasterRoutes::class,'IndexRouteSatu']);
    Route::get('/{satu}/{dua}',[MasterRoutes::class,'IndexRouteDua']);
    Route::get('/{satu}/{dua}/{tiga}',[MasterRoutes::class,'IndexRouteTiga']);

    Route::post('/',[MasterRoutes::class,'index']);
    Route::post('/{satu}',[MasterRoutes::class,'IndexRouteSatu']);
    Route::post('/{satu}/{dua}',[MasterRoutes::class,'IndexRouteDua']);
    Route::post('/{satu}/{dua}/{tiga}',[MasterRoutes::class,'IndexRouteTiga']);
});

Route::group(['prefix'=>'spp'], function() {
    Route::get('/',[SPPRoutes::class,'index']);
    Route::get('/{satu}',[SPPRoutes::class,'IndexRouteSatu']);
    Route::get('/{satu}/{dua}',[SPPRoutes::class,'IndexRouteDua']);
    Route::get('/{satu}/{dua}/{tiga}',[SPPRoutes::class,'IndexRouteTiga']);

    Route::post('/',[SPPRoutes::class,'index']);
    Route::post('/{satu}',[SPPRoutes::class,'IndexRouteSatu']);
    Route::post('/{satu}/{dua}',[SPPRoutes::class,'IndexRouteDua']);
    Route::post('/{satu}/{dua}/{tiga}',[SPPRoutes::class,'IndexRouteTiga']);
});

Route::group(['prefix'=>'spm'], function() {
    Route::get('/',[SPMRoutes::class,'index']);
    Route::get('/{satu}',[SPMRoutes::class,'IndexRouteSatu']);
    Route::get('/{satu}/{dua}',[SPMRoutes::class,'IndexRouteDua']);
    Route::get('/{satu}/{dua}/{tiga}',[SPMRoutes::class,'IndexRouteTiga']);

    Route::post('/',[SPMRoutes::class,'index']);
    Route::post('/{satu}',[SPMRoutes::class,'IndexRouteSatu']);
    Route::post('/{satu}/{dua}',[SPMRoutes::class,'IndexRouteDua']);
    Route::post('/{satu}/{dua}/{tiga}',[SPMRoutes::class,'IndexRouteTiga']);
});

Route::group(['prefix'=>'sppd'], function() {
    Route::get('/',[SPPDRoutes::class,'index']);
    Route::get('/{satu}',[SPPDRoutes::class,'IndexRouteSatu']);
    Route::get('/{satu}/{dua}',[SPPDRoutes::class,'IndexRouteDua']);
    Route::get('/{satu}/{dua}/{tiga}',[SPPDRoutes::class,'IndexRouteTiga']);

    Route::post('/',[SPPDRoutes::class,'index']);
    Route::post('/{satu}',[SPPDRoutes::class,'IndexRouteSatu']);
    Route::post('/{satu}/{dua}',[SPPDRoutes::class,'IndexRouteDua']);
    Route::post('/{satu}/{dua}/{tiga}',[SPPDRoutes::class,'IndexRouteTiga']);
});

Route::group(['prefix'=>'pengajuan'], function() {
    Route::get('/',[PengajuanRoutes::class,'index']);
    Route::get('/{satu}',[PengajuanRoutes::class,'IndexRouteSatu']);
    Route::get('/{satu}/{dua}',[PengajuanRoutes::class,'IndexRouteDua']);
    Route::get('/{satu}/{dua}/{tiga}',[PengajuanRoutes::class,'IndexRouteTiga']);

    Route::post('/',[PengajuanRoutes::class,'index']);
    Route::post('/{satu}',[PengajuanRoutes::class,'IndexRouteSatu']);
    Route::post('/{satu}/{dua}',[PengajuanRoutes::class,'IndexRouteDua']);
    Route::post('/{satu}/{dua}/{tiga}',[PengajuanRoutes::class,'IndexRouteTiga']);
});

Route::group(['prefix'=>'npd'], function() {
    Route::get('/',[NPDRoutes::class,'index']);
    Route::get('/{satu}',[NPDRoutes::class,'IndexRouteSatu']);
    Route::get('/{satu}/{dua}',[NPDRoutes::class,'IndexRouteDua']);
    Route::get('/{satu}/{dua}/{tiga}',[NPDRoutes::class,'IndexRouteTiga']);

    Route::post('/',[NPDRoutes::class,'index']);
    Route::post('/{satu}',[NPDRoutes::class,'IndexRouteSatu']);
    Route::post('/{satu}/{dua}',[NPDRoutes::class,'IndexRouteDua']);
    Route::post('/{satu}/{dua}/{tiga}',[NPDRoutes::class,'IndexRouteTiga']);
});

Route::group(['prefix'=>'dpa'], function() {
    Route::get('/',[DPARoutes::class,'index']);
    Route::get('/{satu}',[DPARoutes::class,'IndexRouteSatu']);
    Route::get('/{satu}/{dua}',[DPARoutes::class,'IndexRouteDua']);
    Route::get('/{satu}/{dua}/{tiga}',[DPARoutes::class,'IndexRouteTiga']);

    Route::post('/',[DPARoutes::class,'index']);
    Route::post('/{satu}',[DPARoutes::class,'IndexRouteSatu']);
    Route::post('/{satu}/{dua}',[DPARoutes::class,'IndexRouteDua']);
    Route::post('/{satu}/{dua}/{tiga}',[DPARoutes::class,'IndexRouteTiga']);
});

// Route::middleware('auth:api', 'throttle:rate_limit,1')->group(function () {
    Route::group(['prefix'=>'sipd'], function() {
        Route::get('/',[SipdRoutes::class,'index']);
        Route::get('/{satu}',[SipdRoutes::class,'IndexRouteSatu']);
        Route::get('/{satu}/{dua}',[SipdRoutes::class,'IndexRouteDua']);
        Route::get('/{satu}/{dua}/{tiga}',[SipdRoutes::class,'IndexRouteTiga']);

        Route::post('/',[SipdRoutes::class,'index']);
        Route::post('/{satu}',[SipdRoutes::class,'IndexRouteSatu']);
        Route::post('/{satu}/{dua}',[SipdRoutes::class,'IndexRouteDua']);
        Route::post('/{satu}/{dua}/{tiga}',[SipdRoutes::class,'IndexRouteTiga']);
    });
// });

Route::group(['prefix'=>'bank'], function() {
    Route::get('/',[BANKRoutes::class,'index']);
    Route::get('/{satu}',[BANKRoutes::class,'IndexRouteSatu']);
    Route::get('/{satu}/{dua}',[BANKRoutes::class,'IndexRouteDua']);
    Route::get('/{satu}/{dua}/{tiga}',[BANKRoutes::class,'IndexRouteTiga']);

    Route::post('/',[BANKRoutes::class,'index']);
    Route::post('/{satu}',[BANKRoutes::class,'IndexRouteSatu']);
    Route::post('/{satu}/{dua}',[BANKRoutes::class,'IndexRouteDua']);
    Route::post('/{satu}/{dua}/{tiga}',[BANKRoutes::class,'IndexRouteTiga']);
});

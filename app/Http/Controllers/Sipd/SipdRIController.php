<?php

namespace App\Http\Controllers\Sipd;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Hash;
use App\Models\User;
use App\Models\OPD;
use App\Models\SPP;
use App\Models\SPM;
use App\Models\SPPD;
use App\Models\GAJI;
use App\Models\DPA;
use App\Models\DPA_REKENING;
use Input;
use Response;
use Auth;
use Crypt;
use Date;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

use App\Http\Controllers\Sipd\APIController as API;

use GuzzleHttp\Client as GuzzleHttpClient;

class SipdRIController extends Controller
{
    static function PostData($req){


          return response()->json([
              'success' => true,
              'message' => 'Gagal Request Data.',
              'payload' => $req->all()
          ], 200);
    }

}

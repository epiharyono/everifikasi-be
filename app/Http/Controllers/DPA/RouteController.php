<?php

namespace App\Http\Controllers\DPA;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Hash;
use App\User;
use Input;
use Response;
use Auth;
use Crypt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

use App\Http\Controllers\SPM\SPMController as SPM;
use App\Http\Controllers\DPA\DPAController as DPA;

class RouteController extends Controller
{

    public function index(Request $req) {

        return response()->json([
            'success' => false,
            'message' => 'Gagal Request Data',
        ], 200);
    }

    public function IndexRouteSatu(Request $req, $satu) {
        if($satu == 'get-data'){
            return DPA::GetData($req);
        }
        return response()->json([
            'success' => false,
            'message' => 'Gagal Request Data.',
        ], 200);

    }

    public function IndexRouteDua(Request $req, $satu,$dua) {

        return response()->json([
            'success' => false,
            'message' => 'Gagal Request Data..',
        ], 200);

    }

    public function IndexRouteTiga(Request $req, $satu,$dua,$tiga) {

        return response()->json([
            'success' => false,
            'message' => 'Gagal Request Data ...',
        ], 200);

    }
}

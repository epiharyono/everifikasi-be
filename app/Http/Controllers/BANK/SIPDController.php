<?php

namespace App\Http\Controllers\BANK;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use Crypt;
use Input;
use View;
use Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

use GuzzleHttp\Client as GuzzleHttpClient;

class SIPDController extends Controller
{

    public function index(Request $req) {

        return response()->json([
            'success' => false,
            'message' => 'Gagal Request Data',
            'payload' => $req->all()
        ], 200);
    }

    public function IndexRouteSatu(Request $req, $satu) {




        return response()->json([
            'success' => false,
            'message' => 'Gagal Request Data.',
            'payload' => $req->all()
        ], 200);

    }

    public function IndexRouteDua(Request $req, $satu,$dua) {



        return response()->json([
            'success' => false,
            'message' => 'Gagal Request Data..',
            'payload' => $req->all()
        ], 200);

    }

    public function IndexRouteTiga(Request $req, $satu,$dua,$tiga) {

        return response()->json([
            'success' => false,
            'message' => 'Gagal Request Data ...',
            'payload' => $req->all()
        ], 200);

    }

}

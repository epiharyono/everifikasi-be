<?php

namespace App\Http\Controllers\Users;

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

use App\Http\Controllers\Users\DataController as DATA;
use App\Http\Controllers\Users\UserController as Userc;

class RouteController extends Controller
{
    public function __construct()
    {
        // $this->middleware('auth:api', ['except' => ['login','register','refresh','logout']]);
    }

    public function index(Request $req) {

        return response()->json([
            'success' => false,
            'message' => 'Gagal Request Data',
        ], 200);
    }

    public function IndexRouteSatu(Request $req, $satu) {

        if($satu == 'testing'){
            $token = $req->bearerToken();
            return DATA::Testing($req);
        }

        elseif($satu == 'get-all'){
            return Userc::GetAll($req);
        }

        elseif($satu == 'get-asis'){
            return Userc::GetUserAsis($req);
        }
        elseif($satu == 'search-asis'){
            return Userc::SearchUserAsis($req);
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

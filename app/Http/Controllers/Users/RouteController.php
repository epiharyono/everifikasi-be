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
use App\Http\Controllers\Users\HakAksesController as HALocal;

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

        elseif($satu == 'get-all-users'){
            return Userc::GetAll($req);
        }

        elseif($satu == 'get-data-opd'){
            return Userc::GetOPD();
        }

        return response()->json([
            'success' => false,
            'message' => 'Gagal Request Data.',
        ], 200);

    }

    public function IndexRouteDua(Request $req, $satu,$dua) {
        if($satu == 'find-by-id'){
            return Userc::FindByID($dua);
        }
        elseif($satu == 'get-ha-by-id'){
            return HALocal::GetDataHAUser($req,$dua);
        }

        elseif($satu == 'update-hak-akses'){
            return HALocal::UpdateHAUser($dua,$req);
        }

        elseif($satu == 'update-data'){
            return Userc::Update($dua,$req);
        }

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

<?php

namespace App\Http\Controllers\SPPD;

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

use App\Http\Controllers\SPPD\SPPDController as SPPD;
use App\Http\Controllers\SPPD\VeriController as VERI;
use App\Http\Controllers\SPPD\OPDController as OPD;
use App\Http\Controllers\SPPD\BankController as BANK;

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
            return SPPD::GetAll($req);
        }
        return response()->json([
            'success' => false,
            'message' => 'Gagal Request Data.',
        ], 200);

    }

    public function IndexRouteDua(Request $req, $satu,$dua) {
        if($satu == 'data' && $dua == 'opd'){
            return OPD::GetData($req);
        }

        elseif($satu == 'find'){
            return SPPD::FindByID($dua);
        }

        elseif($satu == 'veri' && $dua == 'get-data'){
            return VERI::GetAll($req);
        }
        elseif($satu == 'veri' && $dua == 'final'){
            return VERI::Final($req);
        }
        elseif($satu == 'upload-dokumen'){
            return VERI::UploadDokumen($dua,$req);
        }

        elseif($satu == 'bank' && $dua == 'get-data'){
            return BANK::GetAll($req);
        }
        elseif($satu == 'bank' && $dua == 'final'){
            return BANK::Final($req);
        }

        elseif($satu == 'edit-potongan'){
            return SPPD::EditPotongan($req,$dua);
        }

        return response()->json([
            'success' => false,
            'message' => 'Gagal Request Data..',
        ], 200);

    }

    public function IndexRouteTiga(Request $req, $satu,$dua,$tiga) {

        if($satu == 'bank' && $dua == 'find'){
            return BANK::FindByID($tiga);
        }

        return response()->json([
            'success' => false,
            'message' => 'Gagal Request Data ...',
        ], 200);

    }
}

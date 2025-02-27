<?php

namespace App\Http\Controllers\BANK;

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

use App\Http\Controllers\BANK\AccessToken as AT;
use App\Http\Controllers\BANK\AccountInquery as AI;
use App\Http\Controllers\BANK\ResponBRKS as RESP;
use App\Http\Controllers\BANK\BankController as BANK;

class RouteController extends Controller
{

    public function index(Request $req) {

        return response()->json([
            'success' => false,
            'message' => 'Gagal Request Data',
        ], 200);
    }

    public function IndexRouteSatu(Request $req, $satu) {
        if($satu == 'access_token'){
            return AT::GetAccessToken();
        }

        elseif($satu == 'test'){
            return BANK::Testing($req);
        }

        elseif($satu == 'validasi-rekening'){
            // ini hanya untuk kasda online
            return RESP::ValidasiRekening($req);
            return BANK::ValidasiRekening($req);
        }

        elseif($satu == 'inquiry-balance'){
            return BANK::FEInquiryBalance($req);
        }
        elseif($satu == 'account-inquiry'){
            return BANK::FEAccountInquiry($req);
        }
        elseif($satu == 'idbilling-inquiry'){
            return BANK::FEIDBillingInq($req);
        }
        elseif($satu == 'idbilling-status'){
            return BANK::FEIDBillingStatus($req);
        }
        elseif($satu == 'transaksi-history'){
            return BANK::FETransaksiHistory($req);
        }
        elseif($satu == 'transaksi-history-next'){
            return BANK::FETransaksiHisNext($req);
        }

        return response()->json([
            'success' => false,
            'message' => 'Gagal Request Data.',
        ], 200);

    }

    public function IndexRouteDua(Request $req, $satu,$dua) {
        // test akses
        if($satu == 'local' && $dua == 'access-token'){
            return RESP::AccessToken($req);
        }

        elseif($satu == 'check-status'){
            return BANK::CekStatusTransaksi($req,$dua);
        }

        elseif($satu == 'validasi-pajak'){
            // ini hanya untuk kasda online
            return RESP::ValidasiPajak($req,$dua);
            return BANK::ValidasiPajak($req,$dua);
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

<?php

namespace App\Http\Controllers\SPP;

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

use App\Http\Controllers\SPP\SPPController as SPP;
use App\Http\Controllers\SPP\VeriController as VERI;
use App\Http\Controllers\SPP\OPDController as OPD;
use App\Http\Controllers\Referensi\RefController as REF;

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
            return SPP::GetAll($req);
        }

        elseif($satu == 'get-ref-dokumen'){
            return REF::GetRefDokumen();
        }

        elseif($satu == 'final-bend'){
            return SPP::FinalBendahara($req);
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
            return SPP::FindByID($dua,$req);
        }

        elseif($satu == 'upload-dokumen'){
            return SPP::UploadDokumen($dua,$req);
        }
        elseif($satu == 'get-dokumen'){
            return SPP::GetDokumen($dua);
        }
        elseif($satu == 'add-transaksi'){
            return SPP::AddTransaksi($req,$dua);
        }
        elseif($satu == 'delete-transaksi'){
            return SPP::DeleteTransaksi($req,$dua);
        }

        elseif($satu == 'veri' && $dua == 'get-data'){
            return VERI::GetAll($req);
        }
        elseif($satu == 'veri' && $dua == 'final'){
            return VERI::FinalVerifikasi($req);
        }
        elseif($satu == 'veri' && $dua == 'save-notes'){
            return VERI::SaveNotes($req);
        }
        elseif($satu == 'veri' && $dua == 'update-ceklist'){
            return VERI::UpdateCekList($req);
        }


        return response()->json([
            'success' => false,
            'message' => 'Gagal Request Data..',
        ], 200);

    }

    public function IndexRouteTiga(Request $req, $satu,$dua,$tiga) {

        if($satu == 'veri' && $dua == 'find'){
            return VERI::FindByID($tiga,$req);
        }

        return response()->json([
            'success' => false,
            'message' => 'Gagal Request Data ...',
        ], 200);

    }
}

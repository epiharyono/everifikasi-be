<?php

namespace App\Http\Controllers\SPM;

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
use App\Http\Controllers\SPM\VeriController as VERI;
use App\Http\Controllers\SPM\OPDController as OPD;

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
            return SPM::GetAll($req);
        }

        elseif($satu == 'final-bend'){
            return SPM::FinalBendahara($req);
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
            return SPM::Find($req,$dua);
        }

        elseif($satu == 'edit-potongan'){
            return SPM::EditPotongan($req,$dua);
        }

        elseif($satu == 'upload-dokumen'){
            return SPM::UploadDokumen($dua,$req);
        }

        elseif($satu == 'veri' && $dua == 'get-data'){
            return VERI::GetAll($req);
        }
        elseif($satu == 'veri' && $dua == 'update-ceklist'){
            return VERI::UpdateCekList($req);
        }
        elseif($satu == 'veri' && $dua == 'save-notes'){
            return VERI::SaveNotesDokumen($req);
        }
        elseif($satu == 'veri' && $dua == 'final'){
            return VERI::FinalVerifikasi($req);
        }
        elseif($satu == 'add-transaksi'){
            return SPM::AddTransaksi($req,$dua);
        }
        elseif($satu == 'proses-verifikasi'){
            return SPM::ProsesVerifikasi($req,$dua);
        }

        return response()->json([
            'success' => false,
            'message' => 'Gagal Request Data..',
        ], 200);

    }

    public function IndexRouteTiga(Request $req, $satu,$dua,$tiga) {
        if($satu == 'veri' && $dua == 'edit-ceklist'){
            return VERI::EditCekList($req,$tiga);
        }

        return response()->json([
            'success' => false,
            'message' => 'Gagal Request Data ...',
        ], 200);

    }
}

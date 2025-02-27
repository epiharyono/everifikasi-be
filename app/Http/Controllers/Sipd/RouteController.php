<?php

namespace App\Http\Controllers\Sipd;

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

use App\Http\Controllers\Sipd\SipdController as SIPD;
use App\Http\Controllers\Sipd\SipdRIController as SIPDRI;
use App\Http\Controllers\Sipd\CEController as CE;
use App\Http\Controllers\Sipd\AgenSingkronController as ASC;
use App\Http\Controllers\Sipd\SPPController as SPP;
use App\Http\Controllers\Sipd\SPMController as SPM;
use App\Http\Controllers\Sipd\NPDController as NPD;
use App\Http\Controllers\Sipd\SP2DController as SP2D;
use App\Http\Controllers\Sipd\GajiController as GAJI;
use App\Http\Controllers\Sipd\RekananController as REKANAN;

use GuzzleHttp\Client as GuzzleHttpClient;

class RouteController extends Controller
{

    public function index(Request $req) {

        return response()->json([
            'success' => false,
            'message' => 'Gagal Request Data',
            'payload' => $req->all(),
        ], 200);
    }

    public function IndexRouteSatu(Request $req, $satu) {
        if($satu == 'singkron-users'){
            return SIPD::SyncPengguna();
        }
        elseif($satu == 'singkron-opd'){
            try {
                return SIPD::SyncOPD();
            } catch (Exception $e) {
                return ['success' => false, 'message' => 'Error Get API', 'ex'=>$e ];
            }
        }

        elseif($satu == 'singkron-spp'){
            return ASC::SincSPP($req);
        }
        elseif($satu == 'singkron-spp-cetak'){
            return ASC::SincSPPCetak($req);
        }

        elseif($satu == 'singkron-spm'){
            return SIPD::SyncSPM();
        }
        elseif($satu == 'singkron-sppd'){
            return ASC::SincSPPD($req);
        }
        elseif($satu == 'singkron-sppd-cetak'){
            return ASC::SincSPPDCetak($req);
        }
        elseif($satu == 'singkron-spm-cetak'){
            return ASC::SincSPMCetak($req);
        }
        elseif($satu == 'singkron-npd'){
            return ASC::SingkronNPD($req);
        }

        elseif($satu == 'sipd-ri'){
            return SIPDRI::PostData($req);
        }

        elseif($satu == 'singkron-gaji'){
            return ASC::SincGaji($req);
        }
        elseif($satu == 'singkron-rekanan'){
            return ASC::SingRekanan($req);
        }

        elseif($satu == 'singkron-chrome-ext'){

            if($req->action == 'singkron_sp2d'){
                return CE::SingSP2D($req);
            }
            elseif($req->action == 'singkron_sp2d_detail'){
                return CE::SingSP2DDetail($req);
            }
        }

        return response()->json([
            'success' => false,
            'status'  => 'success',
            'message' => 'Gagal Request Data.',
            'payload' => $req->all()
        ], 200);

    }

    public function IndexRouteDua(Request $req, $satu,$dua) {

        if($satu == 'dpa'){
            return SIPD::SyncSubGiat($req);
        }

        elseif($satu == 'singkron-chrome-ext' && $dua == 'realtime'){
            return ASC::GetRealtime($req);
        }

        elseif($satu == 'singkron-chrome-ext' && $dua == 'spp'){
            return SPP::SingkronSPP($req);
        }
        elseif($satu == 'singkron-chrome-ext' && $dua == 'spp_cetak'){
            return SPP::SingkronSPPCetak($req);
        }

        elseif($satu == 'singkron-chrome-ext' && $dua == 'spm'){
            return SPM::SingkronSPM($req);
        }
        elseif($satu == 'singkron-chrome-ext' && $dua == 'spm_cetak'){
            return SPM::SingkronSPMCetak($req);
        }

        elseif($satu == 'singkron-chrome-ext' && $dua == 'npd'){
            return NPD::SingkronNPD($req);
        }

        elseif($satu == 'singkron-chrome-ext' && $dua == 'sp2d'){
            return SP2D::SingkronSP2D($req);
        }
        elseif($satu == 'singkron-chrome-ext' && $dua == 'sp2d_cetak'){
            return SP2D::SingkronSP2DCetak($req);
        }

        elseif($satu == 'singkron-chrome-ext' && $dua == 'gaji_cetak'){
            return GAJI::SingkronGajiCetak($req);
        }

        elseif($satu == 'singkron-chrome-ext' && $dua == 'save-rekanan'){
            return REKANAN::SingkronRekanan($req);
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




    static function ApiSIPD($token,$url,$method){
          try {
              $url    = 'https://service.sipd.kemendagri.go.id'.$url;
              $client = new GuzzleHttpClient();
              $apiRequest = $client->request($method, $url,[
                  'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $token
                  ],
                  'verify' => false,
              ]);
              $content = json_decode($apiRequest->getBody()->getContents());
              return $content;
          } catch (Throwable $e) {
              return ['success' => false, 'message' => 'Provided JWT is invalid.'];
          }
    }

}

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
use Carbon\Carbon;

use GuzzleHttp\Client as GuzzleHttpClient;
use App\Http\Controllers\JwtController as JWT;
use App\Models\Ta_Transaksi as TRANSAKSI;
use App\Models\Ta_Histori as HISTORI;
use App\Models\SPPD_CETAK;
use App\Models\SPPD;
use App\Models\SPPD_POTONGAN as POTONGAN;

class BankController extends Controller
{
    static function Testing($req){

        $id_billing = $req->id_billing;
        $tx_overbook_id = $req->tx_overbook_id;
        // return self::BillingInquery($id_billing);
        return self::TransactionHistory($req);
        // return self::CekStatusIDBilling($id_billing,$tx_overbook_id);

        $random  =  bin2hex(random_bytes(12));
        $external_id  =  bin2hex(random_bytes(12));
        $account = "1700200001";
        $account = $req->account_no;
        $bank_code = "119";
        $user_id = "198409082012121001";
        return self::AccountInquiry($random,$account,$user_id,$bank_code,$external_id);
        return self::InquiryBalance($account,$external_id);
    }

    static function InquiryBalance($account,$external_id){
          // 3. API Inquiry Balance  = Inquiry Balance dilakukan untuk memeriksa (check) saldo (balance) nomor rekening dari sebuah akun bank
          $random  =  bin2hex(random_bytes(12));
          $url = 'balance-inquiry';
          $body = [
              "partnerReferenceNo" => $random,
              "accountNo"  => $account,
              "additionalInfo"  => [
                  "userId"  => "198409082012121001",
                  "bankCode"  => "119",
                  "kodeWilayah" => "21.05"
              ],
          ];
          return ApiRequest::APINEW($url,$body,$random);
    }

    static function CekStatusxOB($req){
          // 3. API check-status  = Check Status dilakukan untuk memeriksa (check) status transaksi dari overbooking yang dilakukan.
          $random  =  bin2hex(random_bytes(12));
          $random  = 'Gv3tlcHnzk9MA05DNqKZqZNr7i1iZU7c';
          $date = $req->tanggal;
          $date    = Carbon::parse($date)->toIso8601String(); //$tanggal->toIso8601String();
          $url = 'check-status';

          $body  = [
              "originalPartnerReferenceNo"  => "z90G5lCcwhw3h6k84Tj0iIVrmPfVw2Qo",
              "originalExternalId"  => "41807553358950093184162180797837",
              "transactionDate"  => "2019-07-03T12:08:56+07:00",
              "amount"  => [
                  "value"  => "50000.00",
                  "currency"  => "IDR"
              ],
              "additionalInfo"  => [
                  "userId"  => "12345679237",
                  "bankCode"  => "119",
                  "kodeWilayah"  => "21.05"
              ]
          ];
          return ApiRequest::API($url,$body,$external_id);
    }

    static function CekStatusOB($data){
          // 3. API check-status  = Check Status dilakukan untuk memeriksa (check) status transaksi dari overbooking yang dilakukan.

          $tx_partner_id = $data['tx_partner_id'];
          $external_id = $data['external_id'];
          $jumlah_ditransfer = $data['jumlah_ditransfer'];
          $user_id = $data['user_id'];
          $date  = $data['tanggal'];
          // $random  =  bin2hex(random_bytes(12));
          // $random  = 'Gv3tlcHnzk9MA05DNqKZqZNr7i1iZU7c';
          $date    = Carbon::parse($date)->toIso8601String(); //$tanggal->toIso8601String();
          $url = 'check-status';

          // return $req->all();
          $body = [
              "originalPartnerReferenceNo" => $tx_partner_id,  //primary
              "originalExternalId"  => $external_id,
              "transactionDate"  => $date,
              "amount"  => [
                  "value"  => "".$jumlah_ditransfer.".00",
                  "currency"  => "IDR"
              ],
              "additionalInfo"  => [
                  "userId"  => $user_id,
                  "bankCode"  => "119",
                  "kodeWilayah" => "21.05"
              ],
          ];
          return ApiRequest::API($url,$body,$external_id);
    }

    static function TransactionHistory($req){
          // transaction-history
          $random  =  bin2hex(random_bytes(12));
          $url = 'transaction-history';
          $body = [
              "partnerReferenceNo" => $random,  //primary
              "fromDateTime"  => $req->from_date,
              "toDateTime"  => $req->to_date,
              "pageSize" => "100",
              "pageNumber" => "1",
              "accountNo"  => $req->account_no,
              "additionalInfo"  => [
                  "userId"  => $req->user_id,
                  "bankCode"  => "119",
                  "kodeWilayah" => "21.05"
              ],
          ];
          return ApiRequest::APINEW($url,$body,$random);
    }

    static function AccountInquiry($random,$account,$user_id,$bank_code,$external_id){
          // 2. API Account Inquiry = Inquiry dilakukan untuk memeriksa (check) nomor rekening dari sebuah akun bank.
          $url = 'account-inquiry';
          $body = [
              "partnerReferenceNo" => $random,  //primary
              "accountNo"  => $account,
              "additionalInfo"  => [
                  "userId"  => $user_id,
                  "bankCode"  => $bank_code,
                  "kodeWilayah" => "21.05",
                  "type" => "rekening"
              ]
          ];
          // return $body;
          return ApiRequest::APINEW($url,$body,$external_id);
    }

    static function BillingInquery($id_billing,$user_id){
         // id-billing-inquiry  untuk cek billing pajak
         $random  =  bin2hex(random_bytes(12));
         $url = 'id-billing-inquiry';
         $body = [
             "partnerReferenceNo" => $random,  //primary
             "additionalInfo"  => [
                 "userId"  => $user_id,
                 "bankCode"  => "119",
                 "idBilling"  => $id_billing,
                 "kodeWilayah" => "21.05"
             ],
         ];
         return ApiRequest::APINEW($url,$body,$random);
    }

    static function CekStatusIDBilling($id_billing,$tx_overbook_id){
          // check-status-id-billing
          $url = 'check-status-id-billing';
          $body = [
              "originalReferenceNo" => $tx_overbook_id,  //ini adalah tx_overbook_id
              "additionalInfo"  => [
                  "userId"  => 123456,
                  "bankCode"  => 119,
                  "idBilling" => $id_billing,
                  "kodeWilayah" => "21.05"
              ],
          ];
          return ApiRequest::APINEW($url,$body,$tx_overbook_id);
    }

    static function ValidasiPajak($req,$id){
          $success = false; $message = 'Gagal Validasi Billing Pajak'; $data = ''; $id_sp2d = 0;
          $token = $req->bearerToken();
          $user  = JWT::CheckJWT($token);
          if(!$user['success']){
             return response()->json(['success' => false,'message' => $message], 401);
          }
          $nip  = $user['data']['nip'];

          $pajak  = DB::table('ta_sppd_potongan')->where('id',$req->id)->first();
          if($pajak){
              if($pajak->id_pajak_potongan < 12 || $pajak->is_valid != 1){
                  $data  = self::BillingInquery($pajak->id_billing,$nip);
                  if($data['success']){
                      $data = $data['data'];
                      if(isset($data->responseCode)){
                          if($data->responseCode == '907'){
                              $validasi = 2; //sudah dibayar
                              $status_message = $data->responseMessage;
                          }
                          else{
                              $status_message = $data->transactionStatusDesc;
                              $validasi = 1;
                          }
                          DB::table('ta_sppd_potongan')->where('id',$pajak->id)->update([
                              'is_valid'  => $validasi,
                              'status_message'  => $status_message,
                              'status_code'  => $data->responseCode,
                          ]);
                      }
                  }
              }
              $id_sp2d  = $pajak->id_sp_2_d;
          }
          $potongan = DB::table('ta_sppd_potongan')->where('id_sp_2_d',$id_sp2d)->get();
          $potongan->transform(function ($value) {
              if($value->id_pajak_potongan < 12){
                  $btn  = false;
                  $status_message = $value->status_message;
                  $valid  = $value->is_valid;
                  if($valid == 1){
                      $btn_disable = true;
                      $btn_status  = "bg-success";
                  }else{
                      $btn_disable = false;
                      $btn_status  = "bg-danger";
                  }
              }else{
                  $btn  = true;
                  $btn_status  = "bg-success";
                  $status_message = "VALID";
                  $valid = 1;
              }
              $value->btn_disable = $btn;
              $value->status_message = $status_message;
              $value->is_valid = $valid;
              $value->btn_status = $btn_status;
              return $value;
          });

          return response()->json([
              'success' => true,
              'message' => 'Validasi Sudah Diproses',
              'data'  => $data,
              'potongan'  => $potongan,
              'payload'  => $req->all()
          ], 200);
    }

    static function ValidasiRekening($req){
        $success = true; $message = 'Sukses Validasi Rekening';
        $data    = '';
        $token = $req->bearerToken();
        $user  = JWT::CheckJWT($token);
        if(!$user['success']){
           return response()->json(['success' => false,'message' => $message], 401);
        }
        $nip  = $user['data']['nip'];

        $trans  = DB::table('ta_transaksi')->where('id',$req->id)->first();
        if($trans){
            $random  =  bin2hex(random_bytes(12));
            $account = "8212110798";
            $bank_code = "119";
            // $bank_code = "002";
            $user_id = "12345679237";

            $account = $trans->rekanan_nomor_rekening;
            // $bank_code = $trans->account_bank;
            $user_id = $nip;

            $external_id  = bin2hex(random_bytes(12));
            $rekanan  = DB::table('ta_rekanan')->where('nomor_rekening',$trans->rekanan_nomor_rekening)->first();
            // $account = '1700200001';
            $data  = self::AccountInquiry($random,$account,$user_id,$bank_code,$external_id);
            if($data['success']){
                $data = $data['data'];
                $code  = $data->responseCode;
                $message  = $data->responseMessage;
                $name  = $data->accountName;
                if($data->responseCode == "900"){
                    DB::table('ta_transaksi')->where('id',$trans->id)->update([
                        'validasi_nomor_rekening' => 1,
                        'rekanan_nama_rekening'  => $data->accountName,
                    ]);
                    DB::table('ta_rekanan')->where('nomor_rekening',$trans->rekanan_nomor_rekening)->update([
                        'reference_no'  => $data->referenceNo,
                    ]);
                }else{
                    DB::table('ta_transaksi')->where('id',$trans->id)->update([
                        'validasi_nomor_rekening' => 0,
                        'rekanan_nama_rekening'  => $data->responseMessage
                    ]);
                }
                $success = true;
            }
            $trans->rekanan = $rekanan;

            $transaksi  = DB::table('ta_transaksi')->where('id_spp',$trans->id_spp)->get();
        }else{
            $transaksi  = [];
        }

        $rekanan  = DB::table('ta_rekanan')->where('nomor_rekening',$trans->rekanan_nomor_rekening)->first();

        $tes = $account;

        return response()->json([
            'success' => $success,
            'message' => $message,
            'tes' => $tes,
            'number'  => is_numeric($tes),
            'string'  => is_string($tes),
            'trans' => $trans,
            'data'  => $data,
            'transaksi' => $transaksi,
            'payload'  => $req->all()
        ], 200);
    }

    static function CekStatusTransaksi($req,$id_sp2d){
        $success = false; $message = 'Gagal Cek Status';
        $token = $req->bearerToken();
        $user  = JWT::CheckJWT($token);
        if(!$user['success']){
           return response()->json(['success' => false,'message' => $message], 401);
        }
        $nip  = $user['data']['nip'];

        $trans  = TRANSAKSI::where('id',$req->id)->with('spm')->first();
        $sppd   = SPPD::where('id_spm',$trans->spm->id_spm)->with('cetak')->first();
        $data   = [
          'tx_partner_id' => $trans->tx_partner_id,
          'external_id' => $sppd->cetak->external_id,
          'tanggal' => $trans->created_at,
          'jumlah_ditransfer' => $trans->jumlah_ditransfer,
          'user_id' => $nip,
        ];
        $data  = self::CekStatusOB($data);

        if(isset($data->responseCode)){
            if($data->responseCode == '403'){
                $status_message = $data->responseMessage;
            }
            else{
                $status_message = $data->transactionStatusDesc;
            }
            TRANSAKSI::where('id',$req->id)->update([
                'status_code' => $data->latestTransactionStatus,
                'status_message'  => $status_message,
            ]);
        }else{
            TRANSAKSI::where('id',$req->id)->update([
                'status_code' => 400,
                'status_message'  => 'Transaction ID Is Not Valid'
            ]);
        }

        // 'btn'  => 'bg-success'
        $transaksi  = TRANSAKSI::where('id_spp',$trans->id_spp)->get();
        $transaksi->transform(function ($value) {
            $value->cek_status();
            return $value;
        });

        if(sizeOf($transaksi)){
            $success = true;
            $message = 'Berhasil Cek Status';
        }

        return response()->json([
            'success' => $success,
            'message' => $message,
            'sppd'  => $sppd,
            'data'  => $data,
            'trans' => $trans,
            'transaksi' => $transaksi,
            'payload'  => $req->all(),
        ], 200);

    }

    static function FEInquiryBalance($req){
        $success = false; $message = 'Maaf Otoritas Tidak Diizinkan';
        $data  = ''; $code = '000';
        $token = $req->bearerToken();
        $user  = JWT::CheckJWT($token);
        if(!$user['success']){
           return response()->json(['success' => false,'message' => $message], 200);
        }
        $nip  = $user['data']['nip'];
        if($nip !== '198409082012121001'){
           return response()->json(['success' => false,'message' => $message], 200);
        }

        $external_id  =  bin2hex(random_bytes(12));
        $account_no   = $req->account_no;
        $name = ''; $saldo = 0;

        $bank  = self::InquiryBalance($account_no,$external_id);
        if($bank['success']){
            $data = $bank['data'];
            $code  = $data->responseCode;
            $message  = $data->responseMessage;
            $name  = $data->name;
            $account  = $data->accountNo;
            $accountInfo  = $data->accountInfo[0]->availableBalance;

            $saldo  = (int)$accountInfo->value;
            $data = [
              'code'  => $code,
              'message'  => $message,
              'name'  => $name,
              'account'  => $account,
              'saldo'  => $saldo
            ];
            $success = true;
        }else{
            $data = $bank['message'];
            $message  = $data->responseMessage;
            $name = $message;
            $success = false;
        }

        return response()->json([
            'success' => $success,
            'message' => $message,
            'code' => $code,
            'is_show' => $success,
            'account_no' => $account_no,
            'name'  => $name,
            'saldo' => $saldo,
            'bank'  => $bank,
            'payload'  => $req->all(),
        ], 200);
    }

    static function FETransaksiHistory($req){
        $success = false; $message = 'Maaf Otoritas Tidak Diizinkan ...';
        $data  = '';
        // $data  = HISTORI::where('account_no',$req->account_no)->whereBetween('date_time',[$req->from_date,$req->to_date])->paginate(4);
        //
        // return response()->json([
        //     'success' => true,
        //     'message' => $message,
        //     'data'  => $data,
        //     'payload'  => $req->all(),
        // ], 200);


        $token = $req->bearerToken();
        $user  = JWT::CheckJWT($token);
        if(!$user['success']){
           return response()->json(['success' => false,'message' => $message], 200);
        }
        $nip  = $user['data']['nip'];
        if($nip !== '198409082012121001'){
           return response()->json(['success' => false,'message' => $message], 200);
        }

        $req['user_id'] = $nip;
        $req['from_date']    = Carbon::parse($req->from_date)->toIso8601String();
        $req['to_date']    = Carbon::parse($req->to_date)->toIso8601String();

        // return $req->all();

        $bank  = self::TransactionHistory($req);
        if($bank['success']){
            $data = $bank['data'];
            $code  = $data->responseCode;
            $message  = $data->responseMessage;
            $account  = $data->accountNo;

            $detail  = $data->detailData;
            foreach($detail as $dat){
                $date_time  = Carbon::parse($dat->dateTime)->toDateTimeString();
                $remark  = $dat->remark;
                $status  = $dat->status;
                $type  = $dat->type;
                $amount_value  = $dat->amount->value;

                $source_value  = $dat->sourceOfFunds[0]->amount->value;

                $datas[]  = [
                    'dateTime'  => $date_time,
                    'remark'  => $remark,
                    'status'  => $status,
                    'type'  => $type,
                    'amount_value'  => $amount_value,
                    'source_value'  => $source_value,
                ];

                $cek  = HISTORI::where('account_no',$account)->where('date_time',$date_time)->first();
                if(!$cek){
                    HISTORI::insert([
                        'account_no'  => $account,
                        'date_time' => $date_time,
                        'remark' => $remark,
                        'status' => $status,
                        'type' => $type,
                        'amount_value' => $amount_value,
                        'source_value' => $source_value,
                        'nip' => $nip,
                    ]);
                }
            }

            $data = [
              'code'  => $code,
              'account'  => $account,
              'message'  => $message,
              'data'  => $datas,
              'detail'  => $detail,
            ];
            $success = true;
        }


        return response()->json([
            'success' => $success,
            'message' => $message,
            'data'  => $data,
            'bank'  => $bank,
            'payload'  => $req->all(),
        ], 200);
    }

    static function FETransaksiHisNext($req){
        $success = false; $message = 'Maaf Otoritas Tidak Diizinkan ...';
        $data  = '';
        $data  = HISTORI::where('account_no',$req->account_no)->whereBetween('date_time',[$req->from_date,$req->to_date])->paginate(4);

        return response()->json([
            'success' => true,
            'message' => $message,
            'data'  => $data,
            'payload'  => $req->all(),
        ], 200);
    }

    static function FEIDBillingInq($req){
        $success = false; $message = 'Maaf Otoritas Tidak Diizinkan ...';
        $data  = ''; $code = '000';
        $token = $req->bearerToken();
        $user  = JWT::CheckJWT($token);
        if(!$user['success']){
           return response()->json(['success' => false,'message' => $message], 200);
        }
        $nip  = $user['data']['nip'];
        if($nip !== '198409082012121001'){
           return response()->json(['success' => false,'message' => $message], 200);
        }

        $id_billing  = $req->id_billing;
        $bank  = self::BillingInquery($id_billing,$nip);
        if($bank['success']){
            $data = $bank['data'];
            $code  = $data->responseCode;
            $message  = $data->responseMessage;
            $success = true;
        }
        // $message  = '<span class="badge label-table bg-success"> oke ya </span>';

        return response()->json([
            'success' => true,
            'message' => $message,
            'code' => $code,
            'is_show' => true,
            'bg_code' => 'bg-success',
            'id_billing' => $id_billing,
            'bank'  => $bank,
        ], 200);
    }

    static function FEIDBillingStatus($req){
        $success = false; $message = 'Maaf Otoritas Tidak Diizinkan ...';
        $data  = ''; $code = '111';
        $token = $req->bearerToken();
        $user  = JWT::CheckJWT($token);
        if(!$user['success']){
           return response()->json(['success' => false,'message' => $message], 200);
        }
        $nip  = $user['data']['nip'];
        if($nip !== '198409082012121001'){
           return response()->json(['success' => false,'message' => $message], 200);
        }

        $id_billing  = $req->id_billing;

        // id_sp_2_d
        $potongan  = POTONGAN::where('id_billing',$id_billing)->with('sppd')->first();
        if($potongan->sppd){
            $tx_overbook_id  =  $potongan->sppd->tx_overbook_id;
        }else{
            $tx_overbook_id  =  bin2hex(random_bytes(12));
        }

        $bank  = self::CekStatusIDBilling($id_billing,$tx_overbook_id);
        if($bank['success']){
            $data = $bank['data'];
            $code  = $data->responseCode;
            $message  = $data->responseMessage;
            $success = true;

            // respong 400 not valid
            if($code == "900"){

            }elseif($code == "907"){
                $info  = $data->additionalInfo;
                $message .= ', Nominal : '.(int)$info->amountValue;
                $message .= ', NTPN : '.$info->noNtpn;
                $message .= ', NTB : '.$info->noNtb;
            }
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'code' => $code,
            'is_show' => true,
            'bg_code' => 'bg-success',
            'id_billing' => $id_billing,
            'tx_overbook_id' => $tx_overbook_id,
            'bank'  => $bank,
            // 'potongan'  => $potongan,
        ], 200);
    }

    static function FEAccountInquiry($req){
        $success = false; $message = 'Maaf Otoritas Tidak Diizinkan ...';
        $data  = ''; $code = ''; $name = '';
        $token = $req->bearerToken();
        $user  = JWT::CheckJWT($token);
        if(!$user['success']){
           return response()->json(['success' => false,'message' => $message], 200);
        }
        $nip  = $user['data']['nip'];
        if($nip !== '198409082012121001'){
           return response()->json(['success' => false,'message' => $message], 200);
        }

        $account_no  = $req->account_no;
        $random  =  bin2hex(random_bytes(12));
        $bank_code = "119";
        $bank  = self::AccountInquiry($random,$account_no,$nip,$bank_code,$random);
        if($bank['success']){
            $data = $bank['data'];
            $code  = $data->responseCode;
            $message  = $data->responseMessage;
            $name  = $data->accountName;
            $success = true;
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'code' => $code,
            'is_show' => true,
            'account_no' => $account_no,
            'name'  => $name,
            'bank'  => $bank
        ], 200);
    }

}

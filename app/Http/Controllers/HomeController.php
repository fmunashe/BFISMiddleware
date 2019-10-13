<?php

namespace App\Http\Controllers;

use App\Batch;
use App\DebitAccount;
use App\Exports\RecordsExport;
use App\Http\Requests\UserRequest;
use App\Record;
use App\Services\CheckData;
use App\User;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Request;
use Alert;
use Excel;
use App\Charts\SampleChart;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    protected $dataservice;
    public function __construct(CheckData $dataservice)
    {
        $this->middleware('auth');
        $this->dataservice=$dataservice;
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        if (session('success_message')) {
            Alert::success('success', session('success_message'))->persistent('Dismiss');
        }

        $batches=Batch::all();
        return view('production.localBatches',compact('batches'));
        }



      public function DataNotifications(){
       // $ck=DB::connection('postilion')->table('pc_cards_3_B')->where('pan','5048759001234560')->exists();
      //  dd($ck);
          set_time_limit(0);
          $notification = $this->dataservice->checkNotifications();
          foreach ($notification as $i) {
              $batch = new Batch();
              $exists = Batch::where('batch_split_id', $i->msgId)->exists();
              if (!$exists) {
                  $batch->batch_split_id = $i->msgId;
                  $batch->date = $i->creDtTm;
                  $batch->transactions = $i->nbOfTxs;
                  $batch->total = $i->ctrlSum;
                  $batch->initiator = $i->initgPty->nm;
                  $batch->payment_method = $i->pmtInf->pmtMtd;
                  $batch->save();
                  $AgriplusTotal=0;
                  $records = $this->dataservice->viewRecords($i->msgId);
                  $paymentInfo = $records->pmtInf;
                  if ($paymentInfo->pmtMtd == "TRF") {
                      $apiDate=explode('-',$paymentInfo->reqdExctnDt);
                      $year=$apiDate[0];
                      $month=$apiDate[1];
                      $day=$apiDate[2];
                      $date=$year.$month.$day;
                      $header = $records->grpHdr;
                      $body = $records->pmtInf->cdtTrfTxInf;
                      $filename = "BAT".$header->msgId . "TRANS" . $paymentInfo->pmtInfId . ".txt";
                      foreach ($body as $agplus){
                          if(Str::startsWith($agplus->cdtrAcct->id->iban,"504875")) {
                              $AgriplusTotal += $agplus->amt->instdAmt->value;
                          }
                      }
                      foreach ($body as $i) {
                          $record = new Record();
                          $exists = Record::where('record_id', $i->pmtId->endToEndId)->exists();
                          if (!$exists) {
//                              $record->batch_split_id = $header->msgId;
//                              $record->payment_info_id = $paymentInfo->pmtInfId;
//                              $record->record_id = $i->pmtId->endToEndId;
//                              $record->initiator = $header->initgPty->nm;
//                              $record->debiting_agent = $paymentInfo->dbtrAgt->finInstnId->bic;
//                              $record->debit_account = $paymentInfo->dbtrAcct->id->iban;
//                              $record->amount = $i->amt->instdAmt->value;
//                              $record->currency = $i->amt->instdAmt->ccy;
//                              $record->payment_method = $paymentInfo->pmtTpInf->ctgyPurp->cd." ".$paymentInfo->pmtMtd;
//                              $record->beneficiary_name = $i->cdtr->nm;
//                              $record->beneficiary_account = $i->cdtrAcct->id->iban;
//                              $record->crediting_agent = $i->cdtrAgt->finInstnId->bic;
//                              $record->reference = $i->rmtInf->strd->cdtrRefInf->ref;
//                              $record->save();
                              $bank_code=explode('-',$paymentInfo->dbtrAgt->finInstnId->bic);
                              if(Str::startsWith($i->cdtrAcct->id->iban,"504875")){
                                  //look the account up in postilion if its there put response if not put dummy data in both agriplus and CDrive
                                  $value=DB::connection('postilion')->table('pc_cards_3_A')->where('pan', $i->cdtrAcct->id->iban)->exists();
                                  $contents = $i->cdtrAcct->id->iban . "," . $i->amt->instdAmt->value*100 ;//$paymentInfo->pmtTpInf->ctgyPurp->cd . "," . $header->msgId . "," . $paymentInfo->pmtInfId . "," . $i->pmtId->endToEndId . "," .
                                  Storage::disk('AgriplusDrive')->append($filename, $contents);
                                  if($value) {
                                      if($bank_code[0]=='10'){
                                          $corpAcc=$paymentInfo->dbtrAcct->id->iban;
                                      }
                                      else{
                                          $corpAcc=$debitAcc=DebitAccount::where('bank_code','=',$bank_code[0])->first();
                                      }
                                      $agSuspense = DebitAccount::where('bank_code', '=', 'Agriplus')->first();
                                      $contents2 = $paymentInfo->pmtTpInf->ctgyPurp->cd . "," . $header->msgId . "," . $paymentInfo->pmtInfId . "," . $i->pmtId->endToEndId . "," . $date . "," . $corpAcc->bank_suspense_account . "," . $paymentInfo->dbtrAgt->finInstnId->bic . "," . $i->cdtrAgt->finInstnId->bic . "," . $header->initgPty->nm . "," . $agSuspense->bank_suspense_account  . "," . $i->cdtr->nm . "," . $i->amt->instdAmt->value . "," . $i->amt->instdAmt->ccy . "," . $i->rmtInf->strd->cdtrRefInf->ref . "," . $header->ctrlSum . "," . $AgriplusTotal . "," . "0";
                                      Storage::disk('CDrive')->append($filename, $contents2);
                                  }
                                  else{
                                      if($bank_code[0]=='10'){
                                          $corpAcc=$paymentInfo->dbtrAcct->id->iban;
                                      }
                                      else{
                                          $corpAcc=$debitAcc=DebitAccount::where('bank_code','=',$bank_code[0])->first();
                                      }
                                      $contents2 = $paymentInfo->pmtTpInf->ctgyPurp->cd . "," . $header->msgId . "," . $paymentInfo->pmtInfId . "," . $i->pmtId->endToEndId . "," . $date . "," . $corpAcc->bank_suspense_account . "," . $paymentInfo->dbtrAgt->finInstnId->bic . "," . $i->cdtrAgt->finInstnId->bic . "," . $header->initgPty->nm . "," . "-1" . "," . $i->cdtr->nm . "," . $i->amt->instdAmt->value . "," . $i->amt->instdAmt->ccy . "," . $i->rmtInf->strd->cdtrRefInf->ref . "," . $header->ctrlSum . "," . $AgriplusTotal . "," . "0";
                                      Storage::disk('CDrive')->append($filename, $contents2);
                                  }
                              }
                              else {
                                  $EOP=Str::startsWith($i->pmtId->endToEndId,'EOP');
                                  if ($bank_code[0] == '10') {
                                      if($EOP){
                                      }
                                      else {
                                          $contents = $paymentInfo->pmtTpInf->ctgyPurp->cd . "," . $header->msgId . "," . $paymentInfo->pmtInfId . "," . $i->pmtId->endToEndId . "," . $date . "," . $paymentInfo->dbtrAcct->id->iban . "," . $paymentInfo->dbtrAgt->finInstnId->bic . "," . $i->cdtrAgt->finInstnId->bic . "," . $header->initgPty->nm . "," . str_pad($i->cdtrAcct->id->iban,12,"0",STR_PAD_LEFT ). "," . $i->cdtr->nm . "," . $i->amt->instdAmt->value . "," . $i->amt->instdAmt->ccy . "," . $i->rmtInf->strd->cdtrRefInf->ref . "," . $header->ctrlSum . "," . $AgriplusTotal . "," . "0";
                                          Storage::disk('CDrive')->append($filename, $contents);
                                      }
                                  }
                                  else {
                                      $debitAcc=DebitAccount::where('bank_code','=',$bank_code[0])->first();
                                      $contents = $paymentInfo->pmtTpInf->ctgyPurp->cd . "," . $header->msgId . "," . $paymentInfo->pmtInfId . "," . $i->pmtId->endToEndId . "," . $date . "," . $debitAcc->bank_suspense_account . "," . $paymentInfo->dbtrAgt->finInstnId->bic . "," . $i->cdtrAgt->finInstnId->bic . "," . $header->initgPty->nm . "," . str_pad($i->cdtrAcct->id->iban,12,"0",STR_PAD_LEFT ) . "," . $i->cdtr->nm . "," . $i->amt->instdAmt->value . "," . $i->amt->instdAmt->ccy . "," . $i->rmtInf->strd->cdtrRefInf->ref.",".$header->ctrlSum.",".$AgriplusTotal.","."0";
                                      Storage::disk('CDrive')->append($filename, $contents);
                                  }

                              }
                          }
                      }
                      $full_path_source= Storage::disk('CDrive')->getDriver()->getAdapter()->getPathPrefix() . $filename;
                      $full_path_dest = Storage::disk('WorkingDirectory')->getDriver()->getAdapter()->applyPathPrefix(basename($filename));
                      File::move($full_path_source,$full_path_dest);
                  }
                  elseif($paymentInfo->pmtMtd == "DD"){
                      $apiDate=explode('-',$paymentInfo->reqdColltnDt);
                      $year=$apiDate[0];
                      $month=$apiDate[1];
                      $day=$apiDate[2];
                      $date=$year.$month.$day;
                      $header = $records->grpHdr;
                      $body = $records->pmtInf->drctDbtTxInf;
                      $filename = "BAT".$header->msgId . "TRANS" . $paymentInfo->pmtInfId . ".txt";
                      foreach ($body as $k) {
                          $record = new Record();
                          $exists = Record::where('record_id', $k->pmtId->endToEndId)->exists();
                          if (!$exists) {
//                              $record->batch_split_id = $header->msgId;
//                              $record->payment_info_id = $paymentInfo->pmtInfId;
//                              $record->record_id = $k->pmtId->endToEndId;
//                              $record->initiator = $header->initgPty->nm;
//                              $record->debiting_agent = $k->dbtr->nm;//$paymentInfo->cdtrAgt->finInstnId->bic
//                              $record->debit_account = $k->dbtrAcct->id->iban;
//                              $record->amount = $k->instdAmt->value;
//                              $record->currency = $k->instdAmt->ccy;
//                              $record->payment_method = $paymentInfo->pmtMtd;
//                              $record->beneficiary_name = $header->initgPty->nm;
//                              $record->beneficiary_account = $paymentInfo->cdtrAcct->id->iban;
//                              $record->crediting_agent = $paymentInfo->cdtrAgt->finInstnId->bic;
//                              $record->reference = $k->rmtInf->strd->cdtrRefInf->ref;
//                              $record->save();
                              $contents=$paymentInfo->pmtMtd.",".$header->msgId.",".$paymentInfo->pmtInfId.",".$k->pmtId->endToEndId."," . $date.",".$k->dbtrAcct->id->iban.",".$paymentInfo->cdtrAgt->finInstnId->bic.",".$paymentInfo->cdtrAgt->finInstnId->bic.",".$k->dbtr->nm. "," . str_pad($paymentInfo->cdtrAcct->id->iban,12,"0",STR_PAD_LEFT)."," . $header->initgPty->nm."," . $k->instdAmt->value .",".$k->instdAmt->ccy.",".$k->rmtInf->strd->cdtrRefInf->ref.",".$header->ctrlSum.","."0".","."0";
                              Storage::disk('CDrive')->append($filename, $contents);
                          }
                      }
                      $full_path_source= Storage::disk('CDrive')->getDriver()->getAdapter()->getPathPrefix() . $filename;
                      $full_path_dest = Storage::disk('WorkingDirectory')->getDriver()->getAdapter()->applyPathPrefix(basename($filename));
                      File::move($full_path_source,$full_path_dest);
                  }
              }
          }
      }




    public function balance(Request $request){
        dd($request);
//    $loginData=$request->validate([
//        'name'=>'required',
//        'password'=>'required',
//    ]);
//    if(!auth()->attempt($loginData)){
//        return response(['message'=>'Invalid Credentials']);
//    }
//    $accessToken =auth()->user()->createToken('authToken')->accessToken;
//    $acc=$request->input('account');
//   // $accountDetails=DB::connection('postilion')->select("select * from pc_cards_3_A where pan=$acc");
//    return response(['account_details'=>$acc,'access_token'=>$accessToken]);
    }

    public function show($batch){
        $records=Record::where('batch_split_id','=',$batch)->orderby('response')->paginate(500);
        $header=Batch::where('batch_split_id','=',$batch)->first();
        $successful=Record::where('batch_split_id','=',$batch)->where( 'response','=','ACWC')->count();
        $failed=Record::where('batch_split_id','=',$batch)->where( 'response','=','RJCT')->count();
        if($header->payment_method=="DD")
        {
        return view('production.localDebitRecords',compact('records','header','successful','failed'));
        }
        else
            {
            return view('production.localRecords',compact('records','header','successful','failed'));
            }
    }
    public function changeProfile(){
        return view('production.profile');
    }
    public function uploadProfile(UserRequest $request,User $user){
        $input = $request->all();
        if ($file = $request->File('path')) {
            $name = $file->getClientOriginalName();
            $file->move('images', $name);
            $input['path']=$name;
        }
        $user->update(['path'=>$input['path']]);
        return redirect()->route('localBatches')->withSuccessMessage("Profile Picture Successfully Changed");
    }

    public function processed(){
    $batches=Batch::latest()->where('status','!=',null)->get();
    return view('production.processedBatches',compact('batches'));
    }
    public function pending(){
    $batches=Batch::latest()->where('status','=',null)->get();
    return view('production.pendingBatches',compact('batches'));
    }
    public function corporateBatches()
    {
        $batches = Batch::all()->groupBy('initiator');
        return view('production.corporateBatches',compact('batches'));
    }
    public function individualCorporateBatches($batch){
    $batches=Batch::where('initiator',$batch)->orderby('status','ASC')->get();
    return view('production.corporate',compact('batches'));
    }
public function graphs(){
    $processed = Batch::where('status','!=',null)->count();
    $pending = Batch::where('status','=',null)->count();
    $below100=Batch::where('transactions','<',100)->count();
    $below200=Batch::where('transactions','<',200)->where('transactions','>',100)->count();
    $below400=Batch::where('transactions','<',400)->where('transactions','>',200)->count();
    $chart = new SampleChart;

    $chart->labels(['Pending','Processed']);
    $dataset = $chart->dataset('','bar',[$pending,$processed]);
    $dataset->backgroundColor(collect(['#FF0000','#007ED6','#7f7fd5','#ad5389','#3c1053','#a8ff78','#78ffd6']));
    $dataset->color(collect([ '#FF0000','#007ED6','#7f7fd5','#ad5389','#3c1053','#a8ff78','#78ffd6']));
    $chart->loaderColor('#32ff7e');
    return view('production.graphs',compact('chart'));
}
    public  function export($id){
        return Excel::download(new RecordsExport(),'Batch-'.$id.'-Records-.xlsx');
    }

}

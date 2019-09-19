<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Alert;
use App\Services\CheckData;
use App\Batch;
use App\Record;
use Illuminate\Support\Facades\Storage;

class DataController extends Controller
{
    protected $dataservice;
    public function __construct(CheckData $dataservice)
    {
        $this->dataservice=$dataservice;
        $this->middleware('auth');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if(session('success_message')){
            Alert::success('success', session('success_message'))->persistent('Dismiss');
        }
        $notification=$this->dataservice->checkNotifications();
            foreach ($notification as $i) {
                $batch = new Batch();
                $filename=$i->msgId."-Salary.txt";
                $exists = Batch::where('batch_split_id', $i->msgId)->exists();
                if (!$exists) {
                    $batch->batch_split_id = $i->msgId;
                    $batch->date = $i->creDtTm;
                    $batch->transactions = $i->nbOfTxs;
                    $batch->total = $i->ctrlSum;
                    $batch->initiator = $i->initgPty->nm;
                    $batch->payment_method = $i->pmtInf->pmtMtd;
                    $batch->save();

                   $contents=$i->msgId.",".$i->creDtTm.",".$i->nbOfTxs.",".$i->ctrlSum.",".$i->initgPty->nm.",".$i->pmtInf->pmtMtd;
                    //$file = ($filename) ? Storage::disk('CDrive')->append($filename,$contents):Storage::disk('CDrive')->put($filename,$contents);
                 // Storage::disk('CDrive')->append($filename,$contents);
                }
               // $files=File::allFiles(storage_path($filename));
             //   dd(File::get(storage_path($filename)));
            }
        return view('production.home',compact('notification'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
//        $input = $request->all();
//        if ($file = $request->File('file')) {
//            $name = $file->getClientOriginalName();
//            $file->move('images', $name);
//            $input['imageColumnNameInDb']=$name;
//        }
//        ModelName::create($input);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $records = $this->dataservice->viewRecords($id);
       // dd($records);
        $paymentInfo = $records->pmtInf;
        if ($paymentInfo->pmtMtd == "TRF") {
            $header = $records->grpHdr;
            $body = $records->pmtInf->cdtTrfTxInf;

            foreach ($body as $i) {
                $record = new Record();
                $filename=$header->msgId."-Salary.txt";
                $exists = Record::where('record_id', $i->pmtId->endToEndId)->exists();
                if (!$exists) {
                    $record->batch_split_id = $header->msgId;
                    $record->payment_info_id = $paymentInfo->pmtInfId;
                    $record->record_id = $i->pmtId->endToEndId;
                    $record->initiator = $header->initgPty->nm;
                    $record->debiting_agent = $paymentInfo->dbtrAgt->finInstnId->bic;
                    $record->debit_account = $paymentInfo->dbtrAcct->id->iban;
                    $record->amount = $i->amt->instdAmt->value;
                    $record->currency = $i->amt->instdAmt->ccy;
                    $record->payment_method = $paymentInfo->pmtMtd;
                    $record->beneficiary_name = $i->cdtr->nm;
                    $record->beneficiary_account = $i->cdtrAcct->id->iban;
                    $record->crediting_agent = $i->cdtrAgt->finInstnId->bic;
                    $record->reference = $i->rmtInf->strd->cdtrRefInf->ref;
                    $record->save();
                    $contents=$paymentInfo->pmtMtd.",".$header->msgId.",".$paymentInfo->pmtInfId.",".$i->pmtId->endToEndId.",".$paymentInfo->reqdExctnDt.",".$paymentInfo->dbtrAcct->id->iban.",".$paymentInfo->dbtrAgt->finInstnId->bic.",".$i->cdtrAgt->finInstnId->bic.",".$header->initgPty->nm.",".$i->cdtrAcct->id->iban.",".$i->cdtr->nm.",".$i->amt->instdAmt->value.",".$i->amt->instdAmt->ccy.",".$i->rmtInf->strd->cdtrRefInf->ref;
                    Storage::disk('CDrive')->append($filename,$contents);
                }
            }
            return view('production.records', compact('body', 'header', 'paymentInfo'));
        }
       return abort(404);
    }
public function generateFile($id)
{
    $records = $this->dataservice->viewRecords($id);
    // dd($records);
    $paymentInfo = $records->pmtInf;
    if ($paymentInfo->pmtMtd == "TRF") {
        $header = $records->grpHdr;
        $body = $records->pmtInf->cdtTrfTxInf;

        foreach ($body as $i) {
            $record = new Record();
            $filename=$header->msgId."-Salary.txt";
            $exists = Record::where('record_id', $i->pmtId->endToEndId)->exists();
            if (!$exists) {
                $record->batch_split_id = $header->msgId;
                $record->payment_info_id = $paymentInfo->pmtInfId;
                $record->record_id = $i->pmtId->endToEndId;
                $record->initiator = $header->initgPty->nm;
                $record->debiting_agent = $paymentInfo->dbtrAgt->finInstnId->bic;
                $record->debit_account = $paymentInfo->dbtrAcct->id->iban;
                $record->amount = $i->amt->instdAmt->value;
                $record->currency = $i->amt->instdAmt->ccy;
                $record->payment_method = $paymentInfo->pmtMtd;
                $record->beneficiary_name = $i->cdtr->nm;
                $record->beneficiary_account = $i->cdtrAcct->id->iban;
                $record->crediting_agent = $i->cdtrAgt->finInstnId->bic;
                $record->reference = $i->rmtInf->strd->cdtrRefInf->ref;
                $record->save();
                $contents=$paymentInfo->pmtMtd.",".$header->msgId.",".$paymentInfo->pmtInfId.",".$i->pmtId->endToEndId.",".$paymentInfo->reqdExctnDt.",".$paymentInfo->dbtrAcct->id->iban.",".$paymentInfo->dbtrAgt->finInstnId->bic.",".$i->cdtrAgt->finInstnId->bic.",".$header->initgPty->nm.",".$i->cdtrAcct->id->iban.",".$i->cdtr->nm.",".$i->amt->instdAmt->value.",".$i->amt->instdAmt->ccy.",".$i->rmtInf->strd->cdtrRefInf->ref;
                Storage::disk('CDrive')->append($filename,$contents);
            }
        }
        return redirect()->route('home')->withSuccessMessage("Batch Id ".$id. " successfully sent to core banking for processing");
    }
    return abort(404);
}
    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}

<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use App\DebitAccount;
use App\Batch;
use App\Record;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use App\Services\CheckData;
use Illuminate\Support\Str;

class CheckSalaryBatch extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:salary';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check pending salary notifications and push them to a db, save the record and write data to a text file that will be picked by T24 service';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    protected $dataservice;
    public function __construct(CheckData $dataservice)
    {
        $this->dataservice=$dataservice;
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //
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
                            $record->batch_split_id = $header->msgId;
                            $record->payment_info_id = $paymentInfo->pmtInfId;
                            $record->record_id = $i->pmtId->endToEndId;
                            $record->initiator = $header->initgPty->nm;
                            $record->debiting_agent = $paymentInfo->dbtrAgt->finInstnId->bic;
                            $record->debit_account = $paymentInfo->dbtrAcct->id->iban;
                            $record->amount = $i->amt->instdAmt->value;
                            $record->currency = $i->amt->instdAmt->ccy;
                            $record->payment_method = $paymentInfo->pmtTpInf->ctgyPurp->cd." ".$paymentInfo->pmtMtd;
                            $record->beneficiary_name = $i->cdtr->nm;
                            $record->beneficiary_account = $i->cdtrAcct->id->iban;
                            $record->crediting_agent = $i->cdtrAgt->finInstnId->bic;
                            $record->reference = $i->rmtInf->strd->cdtrRefInf->ref;
                            $record->save();
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
                                        $corpAcc=DebitAccount::where('bank_code','=',$bank_code[0])->first();
                                        $corpAcc=$corpAcc->bank_suspense_account;
                                    }
                                    $agSuspense = DebitAccount::where('bank_code', '=', 'Agriplus')->first();
                                    $contents2 = $paymentInfo->pmtTpInf->ctgyPurp->cd . "," . $header->msgId . "," . $paymentInfo->pmtInfId . "," . $i->pmtId->endToEndId . "," . $date . "," . $corpAcc. "," . $paymentInfo->dbtrAgt->finInstnId->bic . "," . $i->cdtrAgt->finInstnId->bic . "," . $header->initgPty->nm . "," . $agSuspense->bank_suspense_account  . "," . $i->cdtr->nm . "," . $i->amt->instdAmt->value . "," . $i->amt->instdAmt->ccy . "," . $i->rmtInf->strd->cdtrRefInf->ref . "," . $header->ctrlSum . "," . $AgriplusTotal . "," . "0";
                                    Storage::disk('CDrive')->append($filename, $contents2);
                                }
                                else{
                                    if($bank_code[0]=='10'){
                                        $corpAcc=$paymentInfo->dbtrAcct->id->iban;
                                    }
                                    else{
                                        $corpAcc=DebitAccount::where('bank_code','=',$bank_code[0])->first();
                                        $corpAcc=$corpAcc->bank_suspense_account;
                                    }
                                    $contents2 = $paymentInfo->pmtTpInf->ctgyPurp->cd . "," . $header->msgId . "," . $paymentInfo->pmtInfId . "," . $i->pmtId->endToEndId . "," . $date . "," . $corpAcc . "," . $paymentInfo->dbtrAgt->finInstnId->bic . "," . $i->cdtrAgt->finInstnId->bic . "," . $header->initgPty->nm . "," . "-1" . "," . $i->cdtr->nm . "," . $i->amt->instdAmt->value . "," . $i->amt->instdAmt->ccy . "," . $i->rmtInf->strd->cdtrRefInf->ref . "," . $header->ctrlSum . "," . $AgriplusTotal . "," . "0";
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
                            $record->batch_split_id = $header->msgId;
                            $record->payment_info_id = $paymentInfo->pmtInfId;
                            $record->record_id = $k->pmtId->endToEndId;
                            $record->initiator = $header->initgPty->nm;
                            $record->debiting_agent = $k->dbtr->nm;//$paymentInfo->cdtrAgt->finInstnId->bic
                            $record->debit_account = $k->dbtrAcct->id->iban;
                            $record->amount = $k->instdAmt->value;
                            $record->currency = $k->instdAmt->ccy;
                            $record->payment_method = $paymentInfo->pmtMtd;
                            $record->beneficiary_name = $header->initgPty->nm;
                            $record->beneficiary_account = $paymentInfo->cdtrAcct->id->iban;
                            $record->crediting_agent = $paymentInfo->cdtrAgt->finInstnId->bic;
                            $record->reference = $k->rmtInf->strd->cdtrRefInf->ref;
                            $record->save();
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
}

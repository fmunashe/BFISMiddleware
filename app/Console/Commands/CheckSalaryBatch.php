<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use App\DebitAccount;
use App\Batch;
use App\Record;
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
                    $header = $records->grpHdr;
                    $body = $records->pmtInf->cdtTrfTxInf;
                    foreach ($body as $agplus){
                        if(Str::startsWith($agplus->cdtrAcct->id->iban,"5")) {
                            $AgriplusTotal += $agplus->amt->instdAmt->value;
                        }
                    }
                    foreach ($body as $i) {
                        $record = new Record();
                        $filename = "BAT".$header->msgId . "TRANS" . $paymentInfo->pmtInfId . ".txt";
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
                            $bank_code=explode('-',$paymentInfo->dbtrAgt->finInstnId->bic);
                            if(Str::startsWith($i->cdtrAcct->id->iban,"5")){
                                $contents = $paymentInfo->pmtTpInf->ctgyPurp->cd . "," . $header->msgId . "," . $paymentInfo->pmtInfId . "," . $i->pmtId->endToEndId . "," . $i->cdtrAcct->id->iban . "," . $i->amt->instdAmt->value*100 ;
                                Storage::disk('AgriplusDrive')->append($filename, $contents);
                            }
                            else {
                                $contents = $paymentInfo->pmtTpInf->ctgyPurp->cd . "," . $header->msgId . "," . $paymentInfo->pmtInfId . "," . $i->pmtId->endToEndId . "," . $paymentInfo->reqdExctnDt . "," . $paymentInfo->dbtrAcct->id->iban . "," . $paymentInfo->dbtrAgt->finInstnId->bic . "," . $i->cdtrAgt->finInstnId->bic . "," . $header->initgPty->nm . "," . $i->cdtrAcct->id->iban . "," . $i->cdtr->nm . "," . $i->amt->instdAmt->value . "," . $i->amt->instdAmt->ccy . "," . $i->rmtInf->strd->cdtrRefInf->ref.",".$header->ctrlSum.",".$AgriplusTotal.","."0";
                                 Storage::disk('CDrive')->append($filename, $contents);
//                                $EOP=Str::startsWith($i->pmtId->endToEndId,'EOP');
//                                if (($bank_code[0] == '10') && (!$EOP)) {
//                                    $contents = $paymentInfo->pmtMtd . "," . $header->msgId . "," . $paymentInfo->pmtInfId . "," . $i->pmtId->endToEndId . "," . $paymentInfo->reqdExctnDt . "," . $paymentInfo->dbtrAcct->id->iban . "," . $paymentInfo->dbtrAgt->finInstnId->bic . "," . $i->cdtrAgt->finInstnId->bic . "," . $header->initgPty->nm . "," . $i->cdtrAcct->id->iban . "," . $i->cdtr->nm . "," . $i->amt->instdAmt->value . "," . $i->amt->instdAmt->ccy . "," . $i->rmtInf->strd->cdtrRefInf->ref.",".$header->ctrlSum.",".$AgriplusTotal.","."0";
//                                    Storage::disk('CDrive')->append($filename, $contents);
//                                }
//                                else {
//                                    $debitAcc=DebitAccount::where('bank_code','=',$bank_code[0])->first();
//                                    $contents = $paymentInfo->pmtMtd . "," . $header->msgId . "," . $paymentInfo->pmtInfId . "," . $->pmtId->endToEndId . "," . $paymentInfo->reqdExctnDt . "," . $debitAcc->bank_suspense_account . "," . $paymentInfo->dbtrAgt->finInstnId->bic . "," . $i->cdtrAgt->finInstnId->bic . "," . $header->initgPty->nm . "," . $i->cdtrAcct->id->iban . "," . $i->cdtr->nm . "," . $i->amt->instdAmt->value . "," . $i->amt->instdAmt->ccy . "," . $i->rmtInf->strd->cdtrRefInf->ref.",".$header->ctrlSum.",".$AgriplusTotal.","."0";
//                                    Storage::disk('CDrive')->append($filename, $contents);
//                                }
                            }
                       }
                    }
                }
                elseif($paymentInfo->pmtMtd == "DD"){
                    $header = $records->grpHdr;
                    $body = $records->pmtInf->drctDbtTxInf;
                    foreach ($body as $k) {
                        $record = new Record();
                        $filename = "BAT".$header->msgId . "TRANS" . $paymentInfo->pmtInfId . ".txt";
                        $exists = Record::where('record_id', $k->pmtId->endToEndId)->exists();
                        if (!$exists) {
                            $record->batch_split_id = $header->msgId;
                            $record->payment_info_id = $paymentInfo->pmtInfId;
                            $record->record_id = $k->pmtId->endToEndId;
                            $record->initiator = $header->initgPty->nm;
                            $record->debiting_agent = $paymentInfo->cdtrAgt->finInstnId->bic;
                            $record->debit_account = $k->dbtrAcct->id->iban;
                            $record->amount = $k->instdAmt->value;
                            $record->currency = $k->instdAmt->ccy;
                            $record->payment_method = $paymentInfo->pmtMtd;
                            $record->beneficiary_name = $header->initgPty->nm;
                            $record->beneficiary_account = $paymentInfo->cdtrAcct->id->iban;
                            $record->crediting_agent = $paymentInfo->cdtrAgt->finInstnId->bic;
                            $record->reference = $k->rmtInf->strd->cdtrRefInf->ref;
                            $record->save();
                            $contents=$paymentInfo->pmtMtd.",".$header->msgId.",".$paymentInfo->pmtInfId.",".$k->pmtId->endToEndId."," . $paymentInfo->reqdColltnDt.",".$k->dbtrAcct->id->iban.",".$paymentInfo->cdtrAgt->finInstnId->bic.",".$paymentInfo->cdtrAgt->finInstnId->bic.",".$k->dbtr->nm. "," . $paymentInfo->cdtrAcct->id->iban."," . $header->initgPty->nm."," . $k->instdAmt->value .",".$k->instdAmt->ccy.",".$k->rmtInf->strd->cdtrRefInf->ref.",".$header->ctrlSum.","."0".","."0";
                            Storage::disk('CDrive')->append($filename, $contents);
                        }
                    }

                }
            }
        }
    }
}

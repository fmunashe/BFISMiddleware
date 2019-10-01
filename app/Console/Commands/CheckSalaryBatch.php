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
                                $agriplusSuspense=DebitAccount::where('bank_code','=','Agriplus')->first();
                                $contents = $paymentInfo->pmtMtd . "," . $header->msgId . "," . $paymentInfo->pmtInfId . "," . $i->pmtId->endToEndId . "," . $paymentInfo->reqdExctnDt . "," . $agriplusSuspense->bank_suspense_account . "," . $paymentInfo->dbtrAgt->finInstnId->bic . "," . $i->cdtrAgt->finInstnId->bic . "," . $header->initgPty->nm . "," . $i->cdtrAcct->id->iban . "," . $i->cdtr->nm . "," . $i->amt->instdAmt->value*100 . "," . $i->amt->instdAmt->ccy . "," . $i->rmtInf->strd->cdtrRefInf->ref.",".$header->ctrlSum;
                                Storage::disk('AgriplusDrive')->append($filename, $contents);
                                $AgriplusTotal+=$i->amt->instdAmt->value;
                                Storage::disk('LogsDrive')->put($filename,$AgriplusTotal);
                            }
                            else {
                                if ($bank_code[0] == "10") {
                                    $contents = $paymentInfo->pmtMtd . "," . $header->msgId . "," . $paymentInfo->pmtInfId . "," . $i->pmtId->endToEndId . "," . $paymentInfo->reqdExctnDt . "," . $paymentInfo->dbtrAcct->id->iban . "," . $paymentInfo->dbtrAgt->finInstnId->bic . "," . $i->cdtrAgt->finInstnId->bic . "," . $header->initgPty->nm . "," . $i->cdtrAcct->id->iban . "," . $i->cdtr->nm . "," . $i->amt->instdAmt->value . "," . $i->amt->instdAmt->ccy . "," . $i->rmtInf->strd->cdtrRefInf->ref.",".$header->ctrlSum;
                                    Storage::disk('CDrive')->append($filename, $contents);
                                }
                                else {
                                    $debitAcc=DebitAccount::where('bank_code','=',$bank_code[0])->first();
                                    $contents = $paymentInfo->pmtMtd . "," . $header->msgId . "," . $paymentInfo->pmtInfId . "," . $i->pmtId->endToEndId . "," . $paymentInfo->reqdExctnDt . "," . $debitAcc->bank_suspense_account . "," . $paymentInfo->dbtrAgt->finInstnId->bic . "," . $i->cdtrAgt->finInstnId->bic . "," . $header->initgPty->nm . "," . $i->cdtrAcct->id->iban . "," . $i->cdtr->nm . "," . $i->amt->instdAmt->value . "," . $i->amt->instdAmt->ccy . "," . $i->rmtInf->strd->cdtrRefInf->ref.",".$header->ctrlSum;
                                    Storage::disk('CDrive')->append($filename, $contents);
                                }
                            }
                        }
                    }
                }
                elseif($paymentInfo->pmtMtd == "DD"){
                    $header = $records->grpHdr;
                    $filename = "BAT".$header->msgId . "TRANS" . $paymentInfo->pmtInfId . ".txt";
                    Storage::disk('CDrive')->put($filename, "put debit orders code here");
                }
            }
        }
    }
}

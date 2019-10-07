<?php

namespace App\Console\Commands;

use App\Batch;
use App\Record;
use App\Services\CheckData;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CheckResponse extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:response';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Service to check for processed responses and pass them to BFIS API';

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
            $records = $this->dataservice->viewRecords($i->msgId);
            $paymentInfo = $records->pmtInf;
            $header = $records->grpHdr;
//            if ($paymentInfo->pmtMtd == "TRF") {
                $filename = "BAT".$header->msgId . "TRANS" . $paymentInfo->pmtInfId . ".txt";
                $status = "Batch Processed";
                $AgricashPath = Storage::disk('ResponseDrive')->getDriver()->getAdapter()->getPathPrefix() . $filename;
                $AgriplusPath = Storage::disk('AgriplusResponse')->getDriver()->getAdapter()->getPathPrefix() . $filename;
                if (file_exists($AgricashPath)) {
                    $content = file_get_contents($AgricashPath);
                    $individualEntry = explode("\r\n", $content);
                    $batch = explode(',', $individualEntry[0]);
                    for ($j = 0; $j < count($individualEntry); $j++) {
                        $data = explode(',', $individualEntry[$j]);
                        Record::where('record_id', $data[2])->update([
                            'response' => $data[3],
                            'naration' => $data[4]
                        ]);
                        // echo "logic to push response to api goes here";
                        $this->dataservice->updateNotification($data[2], $header->msgId, $paymentInfo->pmtInfId, $data[3], $data[4]);
                    }
                    Batch::where('batch_split_id', $batch[0])->update([
                        'status' => $status
                    ]);
                    storage::disk('ResponseDrive')->delete($filename);
                   // storage::disk('LogsDrive')->append($filename,"something happened :".Carbon::now());
                }

                if (file_exists($AgriplusPath)) {
                    $content = file_get_contents($AgriplusPath);
                    $individualEntry = explode("\r\n", $content);
                    for ($j = 0; $j < count($individualEntry); $j++) {
                        $data = explode(',', $individualEntry[$j]);
                        Record::where('record_id', $data[2])->update([
                            'response' => $data[3],
                            'naration' => $data[4]
                        ]);
                        $this->dataservice->updateNotification($data[2], $header->msgId, $paymentInfo->pmtInfId, $data[3], $data[4]);
                    }
                    Batch::where('batch_split_id', $batch[0])->update([
                        'status' => $status
                    ]);
                    storage::disk('AgriplusResponse')->delete($filename);
                }
        }
    }
}

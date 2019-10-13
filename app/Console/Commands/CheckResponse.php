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
    protected $description = 'Service to check for processed responses and log them to database';

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
 //           set_time_limit(0);
//            $status = "Batch Processed";
//            $files=Storage::disk('ResponseDrive')->files();
//            for ($i=0;$i<count($files);$i++) {
//                $path = Storage::disk('ResponseDrive')->getDriver()->getAdapter()->getPathPrefix() . $files[$i];
//                $content = file_get_contents($path);
//                $individualEntry = explode("\n", $content);
//                for ($j = 0; $j < count($individualEntry); $j++) {
//                    $data = explode(',', $individualEntry[$j]);
//                    try {
//                          Record::where('record_id', $data[2])->update([
//                                'response' => $data[3],
//                                'naration' => $data[4]
//                            ]);
//                        $this->dataservice->updateNotification($data[2], $data[0], $data[1], $data[3], $data[4]);
//                    } catch (\Exception $ex) {
//                    }
//                }
//                    Batch::where('batch_split_id', $batch[0])->update([
//                        'status' => $status
//                    ]);
//                storage::disk('ResponseDrive')->delete($files[$i]);
//            }






        $notification = $this->dataservice->checkNotifications();
        foreach ($notification as $i) {
            $records = $this->dataservice->viewRecords($i->msgId);
            $paymentInfo = $records->pmtInf;
            $header = $records->grpHdr;
                $filename = "BAT".$header->msgId . "TRANS" . $paymentInfo->pmtInfId . ".txt";
                $status = "Batch Processed";
                $AgricashPath = Storage::disk('ResponseDrive')->getDriver()->getAdapter()->getPathPrefix() . $filename;
                if (file_exists($AgricashPath)) {
                    $content = file_get_contents($AgricashPath);
                    $individualEntry = explode("\n", $content);
                    $batch = explode(',', $individualEntry[0]);
                    for ($j = 0; $j < count($individualEntry); $j++) {
                        $data = explode(',', $individualEntry[$j]);
                        try {
                            Record::where('record_id', $data[2])->update([
                                'response' => $data[3],
                                'naration' => $data[4]
                            ]);
                            // echo "logic to push response to api goes here";
                            $this->dataservice->updateNotification($data[2], $header->msgId, $paymentInfo->pmtInfId, $data[3], $data[4]);
                        }
                        catch(\Exception $ex){

                        }
                    }
                    Batch::where('batch_split_id', $batch[0])->update([
                        'status' => $status
                    ]);
                    storage::disk('ResponseDrive')->delete($filename);
                }
        }
    }
}

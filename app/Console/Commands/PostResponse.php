<?php

namespace App\Console\Commands;

use App\Record;
use App\Services\CheckData;
use Illuminate\Console\Command;

class PostResponse extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'post:response';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This service checks the database for responses from T24 and updates the BFIS API';

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
        try {
            $responses=Record::where('response','!=',"")->where('api_response','=',"")->take(600)->get();
            foreach ($responses as $respo) {
              $remoteAnswer= $this->dataservice->updateNotification($respo->record_id, $respo->batch_split_id, $respo->payment_info_id, $respo->response, $respo->naration);
              Record::where('record_id', $respo->record_id)->update(['api_response' => $remoteAnswer]);
            }
        }
        catch (\Exception $ex) { }

    }
}

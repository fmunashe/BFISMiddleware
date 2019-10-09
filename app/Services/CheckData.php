<?php


namespace App\Services;
use Carbon\Carbon;
use GuzzleHttp\Client;
use App\Services\RetrieveToken;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class CheckData
{
    protected $tokenservice;
    public function __construct(RetrieveToken $tokenservice)
    {
        $this->tokenservice=$tokenservice;
    }
    public function checkNotifications()
    {
        $client=new Client();
        $check=$client->request('GET','https://secure.zimswitch.co.zw/lab/bfis/v1/api/banks/notification/check',[
            'headers'=>[
                'accept'=>'application/json',
                'Authorization' => "Bearer ".$this->tokenservice->getToken()
            ]
        ])->getBody()->getContents();
        $notify=json_decode($check);
        $notification=$notify->notification;
       // dd($notification);
       return $notification;
    }
    public function viewRecords($id)
    {
        $client=new Client();
        $reco=$client->request('GET','https://secure.zimswitch.co.zw/lab/bfis/v1/api/banks/notification/'.$id.'/view',[
            'headers'=>[
                'accept'=>'application/json',
                'Authorization' => "Bearer ".$this->tokenservice->getToken()
            ]
        ])->getBody()->getContents();
        ini_set('memory_limit', '-1');
        $record=json_decode($reco);
      //dd($record);
        return $record;
    }

    public function updateNotification($record_id,$split_id,$pmtInfo_Id,$response,$narration){
        $client=new Client();
        try {
            $arr = array('grpHdr'=>array("msgId"=>$split_id,"creDtTm"=>Carbon::now()),"orgnlGrpInfAndSts"=>array('orgnlMsgId'=>$split_id),"orgnlPmtInfAndSts"=>array("orgnlPmtInfId"=>$pmtInfo_Id,"txInfAndSts"=>array('orgnlEndToEndId'=>$record_id,'txSts'=>$response,'stsRsnInf'=>array('addtlInf'=>$narration))));
            $update = $client->request('PUT', 'https://secure.zimswitch.co.zw/lab/bfis/v1/api/banks/NotificationTransaction/' . $record_id . '/update', [
                'headers' => [
                    'accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'Authorization' => "Bearer " . $this->tokenservice->getToken()
                ],
                'json' => $arr
            ])->getBody()->getContents();
            ini_set('memory_limit', '-1');
            $result = json_decode($update);
            return $result;
        }
        catch(RequestException $ex){
            dd($ex);
        }
    }
}

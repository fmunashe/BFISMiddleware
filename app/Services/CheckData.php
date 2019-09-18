<?php


namespace App\Services;
use GuzzleHttp\Client;
use App\Services\RetrieveToken;

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
        $re=json_decode($reco);
        $record=$re;
      //dd($record);
        return $record;
    }
}

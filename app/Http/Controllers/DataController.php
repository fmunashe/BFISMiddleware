<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Alert;
use App\Services\RetrieveToken;
use App\Services\CheckData;
use App\Batch;
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
//        $batch=new Batch();
//        for($i=0;$i<count($notification);$i++) {
//                $batch->batch_split_id = $notification->msgId;
//                $batch->date = $notify->creDtTm;
//                $batch->transactions = $notify->nbOfTxs;
//                $batch->total = $notify->ctrlSum;
//                $batch->initiator = $notify->initgPty->nm;
//                $batch->payment_method = $notify->pmtInf->pmtMtd;
//                $batch->save();
//        }
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
        $path="\C:/zip/Salary".Carbon::now()."txt";
        $paymentInfo = $records->pmtInf;
        if ($paymentInfo->pmtMtd == "TRF") {
            $header = $records->grpHdr;
            $body = $records->pmtInf->cdtTrfTxInf;
            File::put($path,);
            return view('production.records', compact('body', 'header', 'paymentInfo'));
        }
       return abort(404);
    }
public function generateFile($id){

        return redirect()->route('home')->withSuccessMessage("Batch Id ".$id. " successfully sent to core banking for processing");
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

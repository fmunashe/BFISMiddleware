<?php

namespace App\Http\Controllers;

use App\DebitAccount;
use App\Services\RetrieveToken;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Alert;
use App\Services\CheckData;
use App\Batch;
use App\Record;
use Illuminate\Support\Facades\Storage;
use Psy\Util\Str;

class DataController extends Controller
{
    protected $dataservice;

    public function __construct(CheckData $dataservice)
    {
        $this->dataservice = $dataservice;
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (session('success_message')) {
            Alert::success('success', session('success_message'))->persistent('Dismiss');
        }
        $notification = $this->dataservice->checkNotifications();
        return view('production.home', compact('notification'));
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
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $records = $this->dataservice->viewRecords($id);
        $header = $records->grpHdr;
        $paymentInfo = $records->pmtInf;
        if($paymentInfo->pmtMtd=="DD"){
        $body=$records->pmtInf->drctDbtTxInf;
        return view('production.debitRecords',compact('body','header','paymentInfo'));
        }
        else {
            $body = $records->pmtInf->cdtTrfTxInf;
            return view('production.records', compact('body', 'header', 'paymentInfo'));
        }
    }

    public function getResponse($id)
    {

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

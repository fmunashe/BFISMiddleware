<?php

namespace App\Http\Controllers;

use App\Batch;
use App\Exports\RecordsExport;
use App\Record;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Alert;
use Excel;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
    $batches=Batch::all();
    return view('production.localBatches',compact('batches'));
    }

    public function show($batch){
        $records=Record::where('batch_split_id','=',$batch)->paginate(25);
        $header=Batch::where('batch_split_id','=',$batch)->first();
        //dd($header);
        return view('production.localRecords',compact('records','header'));
    }
    public function processed(){
    $batches=Batch::latest()->where('status','!=',null)->get();
    return view('production.processedBatches',compact('batches'));
    }
    public function pending(){
    $batches=Batch::latest()->where('status','=',null)->get();
    return view('production.pendingBatches',compact('batches'));
    }
    public function corporateBatches()
    {
        $batches = Batch::all()->groupBy('initiator');
            return view('production.corporateBatches',compact('batches'));
    }
    public function individualCorporateBatches($batch){
    $batches=Batch::where('initiator',$batch)->orderby('status','ASC')->get();
    return view('production.corporate',compact('batches'));
    }

    public  function export($id){
        return Excel::download(new RecordsExport(),'Batch-'.$id.'-Records-.xlsx');
    }
}

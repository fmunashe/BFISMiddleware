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
        $records=Record::where('batch_split_id','=',$batch)->paginate(5);
        $header=Batch::where('batch_split_id','=',$batch)->first();
        //dd($header);
        return view('production.localRecords',compact('records','header'));
    }
    public  function export($id){
        return Excel::download(new RecordsExport(),'Batch-'.$id.'-Records-.xlsx');
    }
}

<?php

namespace App\Http\Controllers;

use App\Batch;
use App\Exports\RecordsExport;
use App\Http\Requests\UserRequest;
use App\Record;
use App\User;
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
        if (session('success_message')) {
            Alert::success('success', session('success_message'))->persistent('Dismiss');
        }
    $batches=Batch::all();
    return view('production.localBatches',compact('batches'));
    }

    public function show($batch){
        $records=Record::where('batch_split_id','=',$batch)->paginate(25);
        $header=Batch::where('batch_split_id','=',$batch)->first();
        //dd($header);
        return view('production.localRecords',compact('records','header'));
    }
    public function changeProfile(){
        return view('production.profile');
    }
    public function uploadProfile(UserRequest $request,User $user){
        $input = $request->all();
        if ($file = $request->File('path')) {
            $name = $file->getClientOriginalName();
            $file->move('images', $name);
            $input['path']=$name;
        }
        $user->update(['path'=>$input['path']]);
        return redirect()->route('localBatches')->withSuccessMessage("Profile Picture Successfully Changed");
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

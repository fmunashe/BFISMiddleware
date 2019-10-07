<?php

namespace App\Http\Controllers;

use App\Batch;
use App\Exports\RecordsExport;
use App\Http\Requests\UserRequest;
use App\Record;
use App\User;
use Illuminate\Http\Request;
use Alert;
use Excel;
use App\Charts\SampleChart;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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
    $batches=Batch::latest()->get();
    return view('production.localBatches',compact('batches'));
    }

    public function balance(Request $request){
        dd($request);
//    $loginData=$request->validate([
//        'name'=>'required',
//        'password'=>'required',
//    ]);
//    if(!auth()->attempt($loginData)){
//        return response(['message'=>'Invalid Credentials']);
//    }
//    $accessToken =auth()->user()->createToken('authToken')->accessToken;
//    $acc=$request->input('account');
//   // $accountDetails=DB::connection('postilion')->select("select * from pc_cards_3_A where pan=$acc");
//    return response(['account_details'=>$acc,'access_token'=>$accessToken]);
    }

    public function show($batch){
        $records=Record::where('batch_split_id','=',$batch)->orderby('response')->paginate(25);
        $header=Batch::where('batch_split_id','=',$batch)->first();
        $successful=Record::where('batch_split_id','=',$batch)->where( 'response','=','ACWC')->count();
        $failed=Record::where('batch_split_id','=',$batch)->where( 'response','=','RJCT')->count();
        if($header->payment_method=="DD")
        {
        return view('production.localDebitRecords',compact('records','header','successful','failed'));
        }
        else
            {
            return view('production.localRecords',compact('records','header','successful','failed'));
            }
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
public function graphs(){
    $processed = Batch::where('status','!=',null)->count();
    $pending = Batch::where('status','=',null)->count();
    $chart = new SampleChart;
    $chart->labels(['Processed','Pending']);
    $dataset = $chart->dataset('','doughnut',[$processed,$pending]);
    $dataset->backgroundColor(collect(['#FF0000','#007ED6','#7f7fd5','#ad5389','#3c1053']));
    $dataset->color(collect([ '#FF0000','#007ED6','#7f7fd5','#ad5389','#3c1053']));
    $chart->loaderColor('#32ff7e');
    return view('production.graphs',compact('chart'));
}
    public  function export($id){
        return Excel::download(new RecordsExport(),'Batch-'.$id.'-Records-.xlsx');
    }

}

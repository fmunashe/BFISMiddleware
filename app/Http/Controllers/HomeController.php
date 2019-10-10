<?php

namespace App\Http\Controllers;

use App\Batch;
use App\Exports\RecordsExport;
use App\Http\Requests\UserRequest;
use App\Record;
use App\Services\CheckData;
use App\User;
use Illuminate\Http\Request;
use Alert;
use Excel;
use App\Charts\SampleChart;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    protected $dataservice;
    public function __construct(CheckData $dataservice)
    {
        $this->middleware('auth');
        $this->dataservice=$dataservice;
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


//        $notification = $this->dataservice->checkNotifications();
//        foreach ($notification as $i) {
//            $records = $this->dataservice->viewRecords($i->msgId);
//            $paymentInfo = $records->pmtInf;
//            $header = $records->grpHdr;
////            if ($paymentInfo->pmtMtd == "TRF") {
//            $filename = "BAT" . $header->msgId . "TRANS" . $paymentInfo->pmtInfId . ".txt";
//            $status = "Batch Processed";
//            $AgricashPath = Storage::disk('ResponseDrive')->getDriver()->getAdapter()->getPathPrefix() . $filename;
//            if (file_exists($AgricashPath)) {
//                $content = file_get_contents($AgricashPath);
//                $individualEntry = explode("\n", $content);
//                $batch = explode(',', $individualEntry[0]);
//                for ($j = 0; $j < count($individualEntry); $j++) {
//                    $data = explode(',', $individualEntry[$j]);
//                    try {
//                        Record::where('record_id', $data[2])->update([
//                            'response' => $data[3],
//                            'naration' => $data[4]
//                        ]);
//                    // echo "logic to push response to api goes here";
//                    $this->dataservice->updateNotification($data[2], $header->msgId, $paymentInfo->pmtInfId, $data[3], $data[4]);
//                    }
//                    catch(\Exception $ex){
//
//                    }
//                }
//                Batch::where('batch_split_id', $batch[0])->update([
//                    'status' => $status
//                ]);
//                storage::disk('ResponseDrive')->delete($filename);
//
//                // storage::disk('LogsDrive')->append($filename,"something happened :".Carbon::now());
//            }
//
//
            $batches = Batch::latest()->get();
            return view('production.localBatches', compact('batches'));
//        }
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
    $below100=Batch::where('transactions','<',100)->count();
    $below200=Batch::where('transactions','<',200)->where('transactions','>',100)->count();
    $below400=Batch::where('transactions','<',400)->where('transactions','>',200)->count();
    $chart = new SampleChart;

    $chart->labels(['Pending','Processed']);
    $dataset = $chart->dataset('','bar',[$pending,$processed]);
    $dataset->backgroundColor(collect(['#FF0000','#007ED6','#7f7fd5','#ad5389','#3c1053','#a8ff78','#78ffd6']));
    $dataset->color(collect([ '#FF0000','#007ED6','#7f7fd5','#ad5389','#3c1053','#a8ff78','#78ffd6']));
    $chart->loaderColor('#32ff7e');
    return view('production.graphs',compact('chart'));
}
    public  function export($id){
        return Excel::download(new RecordsExport(),'Batch-'.$id.'-Records-.xlsx');
    }

}

<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Alert;

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
        if(session('success_message')){
            Alert::success('success', session('success_message'))->persistent('Dismiss');
        }
        //make a login call to the api and get the token
        $client=new Client();
        $username="AGRIBANK";
        $password="34578901234";
        $authorisation=" Basic YmF6OjEyMzQ1";
        $grant_type="password";
        $response = $client->request('POST', 'https://secure.zimswitch.co.zw/lab/bfis/v1/oauth/token', [
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Authorization' => $authorisation
            ],
            'form_params' => [
                'username' => $username,
                'password' => $password,
                'grant_type' => $grant_type
            ]
        ])->getBody()->getContents();
        $res=json_decode($response);
        $token=$res->access_token;
        //check for notifications and pass them to the home view for display
        $client2=new Client();
        $check=$client2->request('GET','https://secure.zimswitch.co.zw/lab/bfis/v1/api/banks/notification/check',[
            'headers'=>[
                'accept'=>'application/json',
                'Authorization' => "Bearer ".$token
            ]
        ])->getBody()->getContents();
        $notify=json_decode($check);
        $notification=$notify->notification;
        return view('home',compact('notification'));
        // view individual entries per given notification and click on send to core banking to generate a file for processing
        //the generated file should be dumped to a given directory
        //pull responses back and update the notification
    }
}

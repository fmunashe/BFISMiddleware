<?php

namespace App\Http\Controllers;

use App\DebitAccount;
use Illuminate\Http\Request;
use App\Http\Requests\DebitAccountSuspenseRequest;
use Alert;

class DebitAccountController extends Controller
{
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
      $debits=DebitAccount::all();
        return view('production.debitAccounts',compact('debits'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
        return view('production.createDebitAccount');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(DebitAccountSuspenseRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\DebitAccount  $debitAccount
     * @return \Illuminate\Http\Response
     */
    public function show(DebitAccount $debitAccount)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\DebitAccount  $debitAccount
     * @return \Illuminate\Http\Response
     */
    public function edit(DebitAccount $debitAccount)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\DebitAccount  $debitAccount
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, DebitAccount $debitAccount)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\DebitAccount  $debitAccount
     * @return \Illuminate\Http\Response
     */
    public function destroy(DebitAccount $debitAccount)
    {
        //
    }
}

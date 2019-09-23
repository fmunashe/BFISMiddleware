@extends('layouts.master')

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <h3 class="page-header"><i class="fa fa-file-text-o"></i>&nbsp; Update Suspense Account Details</h3>
            <ol class="breadcrumb">
                <li><i class="fa fa-home"></i><a href="{{route('home')}}">   Home</a> </li>
                <li><i class="fa fa-folder-open-o"></i> Suspense Account Details</li>
                <li><i class="fa fa-file-text-o"></i>   Suspense Accounts form</li>
            </ol>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12 col-sm-12 col-xs-12">
            <div class="x_panel">
                <div class="x_title">
                    <h2>Suspense Account Update</h2>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content">

                    <form class="form-horizontal form-label-left" novalidate method="post" action="{{route('updateDebitAccount',['id'=>$debitAccount->id])}}">
                        {{csrf_field()}}
                        <input type="hidden" name="_method" value="PUT">
                        <span class="section">Account Info</span>
                        <div class="item form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="bank_name">Bank Name <span class="required">*</span>
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                                <input id="bank_name" class="form-control col-md-7 col-xs-12 @error('bank_name') is-invalid @enderror" name="bank_name"  required="required" type="text" value="{{$debitAccount->bank_name}}">
                            </div>
                            @error('bank_name')
                            <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="item form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="bank_code">Bank Code <span class="required">*</span>
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                                <input id="bank_code" type="text" name="bank_code" required="required" class="form-control col-md-7 col-xs-12 @error ('bank_code') is-valid @enderror" value="{{ $debitAccount->bank_code }}">
                            </div>
                            @error('bank_code')
                            <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="item form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="suspense_account">Suspense Account <span class="required">*</span>
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                                <input type="number" id="suspense_account" name="bank_suspense_account"  required="required" class="form-control col-md-7 col-xs- @error ('bank_suspense_account') is-valid @enderror" value="{{ $debitAccount->bank_suspense_account }}">
                            </div>
                            @error('bank_suspense_account')
                            <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="ln_solid"></div>
                        <div class="form-group">
                            <div class="col-md-6 col-md-offset-3">
                                <input type="submit" class="btn btn-success btn-block">
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

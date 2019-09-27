@extends('layouts.master')

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <h3 class="page-header"><i class="fa fa-file-text-o"></i>&nbsp;Registered Suspense Account</h3>
            <ol class="breadcrumb">
                <li><i class="fa fa-home"></i><a href="{{route('home')}}">   Home</a> </li>
                <li><i class="fa fa-folder-open-o"></i> Suspense Account Information</li>
                <li><i class="fa fa-file-text-o"></i>   Individual Suspense Account</li>
            </ol>
        </div>
    </div>
    <div class="panel panel-default">
        <div class="panel-body">
            <table class="table table-hover table-condensed table-striped jambo_table bulk_action">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Bank Code</th>
                    <th>Bank Name</th>
                    <th>Suspense Account</th>
                    <th>Created</th>
                    <th>Updated</th>
                    <th>Action</th>
                </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{$debitAccount->id}}</td>
                        <td>{{$debitAccount->bank_code}}</td>
                        <td>{{$debitAccount->bank_name}}</td>
                        <td>{{$debitAccount->bank_suspense_account}}</td>
                        <td>{{$debitAccount->created_at->diffForHumans()}}</td>
                        <td>{{$debitAccount->updated_at}}</td>
                        <td><a data-toggle="tooltip" data-placement="right" title="Go Back" href="{{route('debitAccounts')}}" class="btn btn-sm btn-success fa fa-arrow-circle-left">Back</a></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
@endsection

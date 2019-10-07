@extends('layouts.master')

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <h3 class="page-header"><i class="fa fa-file-text-o"></i>&nbsp;Registered Suspense Accounts</h3>
            <ol class="breadcrumb">
                <li><i class="fa fa-home"></i><a href="{{route('home')}}">   Home</a> </li>
                <li><i class="fa fa-folder-open-o"></i> Suspense</li>
                <li><i class="fa fa-file-text-o"></i>   Suspense Accounts List</li>
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
{{--                    <th>&nbsp;</th>--}}
                    <th>Action</th>
{{--                    <th>&nbsp;</th>--}}
                </tr>
                </thead>
                <tbody>
                @foreach($debits as $debit)
                    <tr>
                        <td>{{$debit->id}}</td>
                        <td>{{$debit->bank_code}}</td>
                        <td>{{$debit->bank_name}}</td>
                        <td>{{$debit->bank_suspense_account}}</td>
                        <td><a data-toggle="tooltip" data-placement="right" title="Show" href="{{route('showDebitAccount',['id'=>$debit->id])}}" class="btn btn-sm btn-success fa fa-eye"></a></td>
{{--                        <td><a data-toggle="tooltip" data-placement="right" title="Edit Account" href="{{route('editDebitAccount',['id'=>$debit->id])}}" class="btn btn-sm btn-info fa fa-edit"></a></td>--}}
{{--                        <td><a data-toggle="tooltip" data-placement="right" title="Remove Account" href="{{route('removeDebitAccount',['id'=>$debit->id])}}"><span class="btn btn-sm btn-danger fa fa-trash"></span></a></td>--}}
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection

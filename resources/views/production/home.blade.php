@extends('layouts.master')

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <h3 class="page-header"><i class="fa fa-file-text-o"></i>&nbsp;Pending Batches</h3>
            <ol class="breadcrumb">
                <li><i class="fa fa-home"></i><a href="{{route('home')}}">   Home</a> </li>
                <li><i class="fa fa-folder-open-o"></i> Notifications</li>
                <li><i class="fa fa-file-text-o"></i>   Notification List</li>
            </ol>
        </div>
    </div>
    <div class="panel panel-default">
        <div class="panel-body">
        <table class="table table-hover table-condensed table-striped jambo_table bulk_action">
            <thead>
            <tr>
                <th>Batch SplitId</th>
                <th>Upload Date</th>
                <th>Number of Trans</th>
                <th>Total Amount</th>
                <th>Sender Id</th>
                <th>Payment Type</th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody>
            @foreach($notification as $notice)
            <tr>
                <td>{{$notice->msgId}}</td>
                <td>{{$notice->creDtTm}}</td>
                <td>{{$notice->nbOfTxs}}</td>
                <td>{{$notice->ctrlSum}}</td>
                <td>{{$notice->initgPty->nm}}</td>
                <td>{{$notice->pmtInf->pmtMtd}}</td>
                <td><a href="{{route('viewRecords',['id'=>$notice->msgId])}}"><span class="btn btn-sm btn-success fa fa-eye"></span></a><a href="{{route('generateFile',['id'=>$notice->msgId])}}" class="btn btn-info btn-sm fa fa-arrow-circle-right">&nbsp;Process File</a></td>
            </tr>
                @endforeach
            </tbody>
        </table>
        </div>
    </div>
@endsection

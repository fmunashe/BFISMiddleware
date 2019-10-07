@extends('layouts.master')

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <h3 class="page-header"><i class="fa fa-file-text-o"></i>  Processed Batch History Report</h3>
            <ol class="breadcrumb">
                <li><i class="fa fa-home"></i><a href="{{route('home')}}">  Home</a> </li>
                <li><i class="fa fa-folder-open-o"></i> Batches</li>
                <li><i class="fa fa-file-text-o"></i>   Batch List</li>
            </ol>
        </div>
    </div>
    <div class="panel panel-default">
        <div class="panel-body">
            <table class="table table-hover table-condensed table-striped jambo_table bulk_action">
                <thead>
                <tr>
                    <th>Split Id</th>
                    <th>Upload Date</th>
                    <th>Total Trans</th>
                    <th>Total Amount</th>
                    <th>Sender</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Received</th>
                    <th>Processed</th>
                    <th>Action</th>
                </tr>
                </thead>
                <tbody>
                @foreach($batches as $batch)
                    <tr>
                        <td>{{$batch->batch_split_id}}</td>
                        <td>{{substr($batch->date,0,16)}}</td>
                        <td>{{$batch->transactions}}</td>
                        <td>{{$batch->total}}</td>
                        <td>{{$batch->initiator}}</td>
                        <td>{{$batch->payment_method}}</td>
                        <td>{{$batch->status}}</td>
                        <td>{{$batch->created_at}}</td>
                        <td>{{($batch->status)!=""?$batch->updated_at:""}}</td>
                        <td><a data-toggle="tooltip" data-placement="left" title="All Records" href="{{route('viewLocalRecords',['id'=>$batch->batch_split_id])}}"><span class="btn btn-sm btn-success fa fa-eye"></span></a></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection

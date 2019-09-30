@extends('layouts.master')

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <h3 class="page-header"><i class="fa fa-file-text-o"></i>  Batch History Grouped By Corporate</h3>
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
                    <th>Corporate</th>
                    <th>Total Batches</th>
                    <th>Total Value</th>
                    <th>Action</th>
                </tr>
                </thead>
                <tbody>
                @foreach($batches as $batch=>$corp)
                    <tr>
                        <td>{{$batch}}</td>
                        <td>{{$corp->count()}}</td>
                        <td>@foreach($corp as $co)@endforeach{{$corp->sum()}}</td>
                        <td><a data-toggle="tooltip" data-placement="right" title="Show Batches" href="{{route('viewCorporateBatches',['id'=>$batch])}}"><span class="btn btn-sm btn-success fa fa-eye"></span></a></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection

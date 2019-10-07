@extends('layouts.master')

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <h3 class="page-header"><i class="fa fa-file-text-o"></i>&nbsp;System Performance Graphs</h3>
            <ol class="breadcrumb">
                <li><i class="fa fa-home"></i><a href="{{route('home')}}">   Home</a> </li>
                <li><i class="fa fa-folder-open-o"></i> Graph</li>
                <li><i class="fa fa-file-text-o"></i>   Collection Graphs</li>
            </ol>
        </div>
    </div>
    <div class="panel panel-default">
        <div class="panel-body">

        </div>
        <div class="col-lg-6 col-xs-8">
            {!! $chart->container() !!}
            <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.1/Chart.min.js" charset="utf-8"></script>
            {!! $chart->script() !!}
        </div>
    </div>
@endsection

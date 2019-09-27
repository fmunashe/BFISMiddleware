@extends('layouts.master')

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <h3 class="page-header"><i class="fa fa-file-text-o"></i>&nbsp;Batch Split Records History</h3>
            <ol class="breadcrumb">
                <li><i class="fa fa-home"></i><a href="{{route('home')}}">   Home</a> </li>
                <li><i class="fa fa-folder-open-o"></i> Notification Records History</li>
                <li><i class="fa fa-file-text-o"></i> Record List </li>
            </ol>
        </div>
    </div>
    <div class="panel panel-default">
        <div class="panel-body">
            <h4>Payment and Transaction Header Information &nbsp;<a href="{{route('excelExport',['id'=>$header->batch_split_id])}}" class="btn btn-info btn-sm fa fa-arrow-circle-down pull-right">&nbsp;Excel Export</a> &nbsp;<a href="{{route('localBatches')}}" class="btn btn-info btn-sm fa fa-arrow-circle-left pull-right">&nbsp;Back</a></h4>
            <table class="table table-condensed">
                @foreach($records as $record)
                @endforeach
                <tr>
                    <td>Batch Split Id</td>
                    <td>{{$header->batch_split_id}}</td>
                    <td>Payment Information Id</td>
                    <td>{{$record->payment_info_id}}</td>
                </tr>
                <tr>
                    <td>Transaction Date</td>
                    <td>{{$header->date}}</td>
                    <td>payment method</td>
                    <td>{{$header->payment_method}}</td>
                </tr>
                <tr>
                    <td>Total Transactions</td>
                    <td>{{$header->transactions}}</td>
                    <td>Debit Account</td>
                    <td>{{$record->debit_account}}</td>
                </tr>
                <tr>
                    <td>Total Sum</td>
                    <td>{{$header->total}}</td>
                    <td>Currency</td>
                    <td>{{$record->currency}}</td>
                </tr>
                <tr>
                    <td>Initiating Party</td>
                    <td>{{$header->initiator}}</td>
                    <td>Debiting Agent</td>
                    <td>{{$record->debiting_agent}}</td>
                </tr>
            </table>
            <table class="table table-hover table-condensed table-striped jambo_table bulk_action">
                <thead>
                <tr>
                    <th>Record Id</th>
                    <th>Amount</th>
                    <th>Currency</th>
                    <th>Crediting Agent</th>
                    <th>Beneficiary Name</th>
                    <th>Beneficiary Account</th>
                    <th>Reference</th>
                    <th>Response</th>
                    <th>Narration</th>
                    <th>Processed</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                @foreach($records as $record)
                    <tr>
                        <td>{{$record->record_id}}</td>
                        <td>{{$record->amount}}</td>
                        <td>{{$record->currency}}</td>
                        <td>{{$record->crediting_agent}}</td>
                        <td>{{$record->beneficiary_name}}</td>
                        <td>{{$record->beneficiary_account}}</td>
                        <td>{{$record->reference}}</td>
                        <td>{{$record->response}}</td>
                        <td>{{$record->naration}}</td>
                        <td>{{($record->naration)!=""?$record->updated_at:""}}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            {{$records->links()}}
        </div>
    </div>
@endsection

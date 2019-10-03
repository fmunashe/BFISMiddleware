@extends('layouts.master')

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <h3 class="page-header"><i class="fa fa-file-text-o"></i>&nbsp;Batch Split Records For {{$header->initgPty->nm}} Coming Through {{$paymentInfo->dbtrAgt->finInstnId->bic}}</h3>
            <ol class="breadcrumb">
                <li><i class="fa fa-home"></i><a href="{{route('home')}}">   Home</a> </li>
                <li><i class="fa fa-folder-open-o"></i> Notifications</li>
                <li><i class="fa fa-file-text-o"></i> Records List </li>
            </ol>
        </div>
    </div>
    <div class="panel panel-default">
        <div class="panel-body">
            <h4>Payment and Transaction Header Information &nbsp;<a href="{{route('home')}}" class="btn btn-info btn-sm fa fa-arrow-circle-left pull-right">&nbsp;Back</a></h4>
            <table class="table table-condensed">
            <tr>
                <td>Batch Split Id</td>
                <td>{{$header->msgId}}</td>
                <td>Payment Information Id</td>
                <td>{{$paymentInfo->pmtInfId}}</td>
            </tr>
            <tr>
                <td>Transaction Date</td>
                <td>{{$header->creDtTm}}</td>
                <td>payment method</td>
                <td>{{$paymentInfo->pmtTpInf->ctgyPurp->cd." ".$paymentInfo->pmtMtd}}</td>
            </tr>
            <tr>
                <td>Total Transactions</td>
                <td>{{$header->nbOfTxs}}</td>
                <td>Debit Account</td>
                <td>{{$paymentInfo->dbtrAcct->id->iban}}</td>
            </tr>
            <tr>
                <td>Total Sum</td>
                <td>{{$header->ctrlSum}}</td>
                <td>Currency</td>
                <td>{{$paymentInfo->dbtrAcct->ccy}}</td>
            </tr>
            <tr>
                <td>Initiating Party</td>
                <td>{{$header->initgPty->nm}}</td>
                <td>Debiting Agent</td>
                <td>{{$paymentInfo->dbtrAgt->finInstnId->bic}}</td>
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
                </tr>
                </thead>
                <tbody>
                <tr>
                @foreach($body as $record)
                    <tr>
                        <td>{{$record->pmtId->endToEndId}}</td>
                        <td>{{$record->amt->instdAmt->value}}</td>
                        <td>{{$record->amt->instdAmt->ccy}}</td>
                        <td>{{$record->cdtrAgt->finInstnId->bic}}</td>
                        <td>{{$record->cdtr->nm}}</td>
                        <td>{{$record->cdtrAcct->id->iban}}</td>
                        <td>{{$record->rmtInf->strd->cdtrRefInf->ref}}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection

@extends('layouts.master')

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <h3 class="page-header"><i class="fa fa-file-text-o"></i>&nbsp; User Profile Picture Upload</h3>
            <ol class="breadcrumb">
                <li><i class="fa fa-home"></i><a href="{{route('home')}}">   Home</a> </li>
                <li><i class="fa fa-folder-open-o"></i> User Account</li>
                <li><i class="fa fa-file-text-o"></i>   Profile Upload form</li>
            </ol>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12 col-sm-12 col-xs-12">
            <div class="x_panel">
                <div class="x_title">
                    <h2>Profile Picture</h2>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content">

                    <form class="form-horizontal form-label-left" novalidate method="post" action="{{route('uploadProfile',['id'=>auth()->user()->id])}}" enctype="multipart/form-data">
                        {{csrf_field()}}
                        <input type="hidden" name="_method" value="PUT">
                        <span class="section">Account Info</span>
                        <div class="item form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="path">Profile Picture <span class="required">*</span>
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                                <input id="path" type="file" name="path" required="true" class="form-control col-md-7 col-xs-12 @error ('path') is-valid @enderror" value="{{ old('path') }}">
                            </div>
                            @error('path')
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

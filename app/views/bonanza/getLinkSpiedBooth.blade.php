@extends('layouts.master')
@section('content')
    @if(Session::has('success_message'))
        <br>
        <div class="alert alert-success" role="alert">
            <h4 class="alert-heading">Well done!</h4>
            <p>{{ Session::get('success_message') }}.</p>
        </div>
    @endif

    @if(Session::has('danger_message'))
        <br>
        <div class="alert alert-danger" role="alert">
            <h4 class="alert-heading">Failure | Please try again!</h4>
            <p>{{ Session::get('danger_message') }}.</p>
        </div>
    @endif

    <h1 class="mt-5">{{ $page_title }}</h1>
    {{ Form::open(array('route' => 'bonanza.postLinkSpiedBooth', 'method' => 'post', 'class' => 'was-validated')) }}
        <div class="form-group">
            <label for="formGroupExampleInput">Example: https://www.bonanza.com/booths/lolashopfeb</label>
            <input type="text" class="form-control" name="link_of_spied_booth" id="formGroupExampleInput" placeholder="Example: https://www.bonanza.com/booths/lolashopfeb"  id="validatedCustomFile" required>
            <div class="invalid-feedback">
                Please provide a valid booth's link.
            </div>
        </div>

    <div class="form-group">
        <label for="exampleFormControlSelect2">From Page (Min = 1)</label>
        <input class="form-control" type="number" name="from_page" min="1" value="1">
    </div>

    <div class="form-group">
        <label for="exampleFormControlSelect2">To Page (Min = 1)</label>
        <input class="form-control" type="number" name="to_page" min="1" value="1">
    </div>

        <button type="submit" class="btn btn-primary">Get All Item's Links In CSV</button>
    {{ Form::close() }}


@stop

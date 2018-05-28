@extends('layouts.master')
@section('content')

    <?php
        $arr = array(
            array(
                "mission" => "Bonanza - Get all links or few pages of booth on Bonanza",
                "link"  =>  route('bonanza.getLinkSpiedBooth'),
                "status" => "Completed",
            ),

            array(
                "mission" => "Bonanza - Get data and export to CSV",
                "link"  =>  route('bonanza.getDataAndExportToCSV'),
                "status" => "Completed",
            ),
        );
    ?>

    <div class="container" style="margin-top: 100px">
        <!-- Content here -->
        <h1 style="margin-bottom: 20px; text-align: center">Crawler Products</h1>
        <table class="table table-dark">
            <thead>
            <tr>
                <th scope="col">#</th>
                <th scope="col">Mission</th>
                <th scope="col">Link</th>
                <th scope="col">Status</th>
            </tr>
            </thead>
            <tbody>

            @foreach($arr as $key => $each)
                <tr>
                    <th scope="row">{{ $key + 1 }}</th>
                    <td>{{ $each['mission'] }}</td>
                    <td><a href="{{ $each['link'] }}" target="_blank">Click here</a></td>
                    <td>{{ $each['status'] }}</td>
                </tr>
            @endforeach


            </tbody>
        </table>
    </div>

@stop
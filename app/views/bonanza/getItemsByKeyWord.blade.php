@extends('layouts.master')
@section('content')

    <h1 class="mt-5">{{ $page_title }}</h1>
    {{ Form::open(array('route' => 'bonanza.postItemByKeyWord', 'method' => 'post')) }}
        <div class="form-group">
            <label for="formGroupExampleInput">Example: Bikini</label>
            <input type="text" class="form-control" name="keywords" id="formGroupExampleInput" placeholder="bikini"  required>
        </div>
        <div class="form-group">
            <label for="formGroupExampleInput">Entries Per Page : 10</label>
            <input type="text" class="form-control" name="entriesPerPage" id="formGroupExampleInput" placeholder="Entries Per Page"  required>
        </div>

        <div class="form-group">
            <label for="formGroupExampleInput">Page Number : 1</label>
            <input type="text" class="form-control" name="pageNumber" id="formGroupExampleInput" placeholder="Page number"  required>
        </div>
        <button type="submit" class="btn btn-primary">Get All Item with Keyword</button>
    {{ Form::close() }}
    @if(Session::has('$items'))
        <br>
        {{--<pre>{{ $items }}</pre>--}}
        <table>
            <thead>
            <tr>
                <th> Id</th>
                <th> Title</th>
                {{--<th> last name  </th>--}}
                {{--<th> email </th>--}}
                {{--<th> phone</th>--}}
                {{--<th> adddress </th>--}}
            </tr>
            </thead>
            <tbody>
            @foreach($items as $item)
                <tr>
{{--                    <td> {{$item->viewItemURL}} </td>--}}
{{--                    <td> {{$item->descriptionBrief}} </td>--}}
                    {{--<td> {{$item->last_name}} </td>--}}
                    {{--<td> {{$item->email}} </td>--}}
                    {{--<td> {{$item->phone}} </td>--}}
                    {{--<td> {{$item->address}} </td>--}}
                </tr>
            @endforeach
            </tbody>
        </table>

    @endif


@stop

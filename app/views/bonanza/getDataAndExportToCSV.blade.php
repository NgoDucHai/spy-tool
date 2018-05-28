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

    @if(Session::has('error_messages'))
        <br>
        <div class="alert alert-danger" role="alert">
            <h4 class="alert-heading">Failure | Please check all fields!</h4>
            <?php
                $error_message = Session::get('error_messages');
                foreach($error_message->all() as $message) {
                    echo "<p>$message</p>";
                }
                ?>

        </div>
    @endif

    <h1 class="mt-5">{{ $page_title }}</h1>
    {{ Form::open(array('route' => 'bonanza.postDataAndExportToCSV', 'files' => true, 'method' => 'post', 'class' => 'was-validated')) }}
        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="formGroupExampleInput">Input Store's Link (Example: https://www.bonanza.com/booths/lolashopfeb )</label>
                <input type="text" class="form-control" name="link_of_spied_booth" id="formGroupExampleInput" placeholder="Example: https://www.bonanza.com/booths/lolashopfeb">
            </div>

            <div class="form-group col-md-2">
                <label for="exampleFormControlSelect2"></label>
                <p class="text-center"><strong>OR</strong></p>
            </div>

            <div class="form-group col-md-4">
                <label for="exampleFormControlSelect2">Upload CSV</label>
                <div class="custom-file">
                    <input type="file" class="custom-file-input" name="excel_file">
                    <label class="custom-file-label" for="validatedCustomFile">Choose excel file...</label>
                </div>
            </div>

            <div class="form-group col-md-3">
                <label for="exampleFormControlSelect2">SKU</label>
                <input class="form-control" type="text" name="sku" required>
                <div class="invalid-feedback">
                    Please provide a SKU
                </div>
            </div>

            <div class="form-group col-md-3">
                <label for="exampleFormControlSelect2">From Page (Min = 1)</label>
                <input class="form-control" type="number" name="from_page" min="1"  value="1">
            </div>

            <div class="form-group col-md-3">
                <label for="exampleFormControlSelect2">To Page (Min = 1)</label>
                <input class="form-control" type="number" name="to_page" min="1"  value="1">
            </div>

            <div class="form-group col-md-3">
                <label for="exampleFormControlSelect2">Increase Price Up To</label>
                <input class="form-control" type="number" name="price_up_to" step="0.01" min="0.00" required>
                <div class="invalid-feedback">
                    Please provide a number for PRICE UP TO
                </div>
            </div>

            <div class="form-group col-md-3">
                <label for="exampleFormControlSelect2">Set Price Based On:</label>
                <select class="custom-select my-1 mr-sm-2" id="inlineFormCustomSelectPref" name="set_price_based_on">
                    <option value="1" selected>List Price</option>
                    <option value="0">Price on Per Trait of Items</option>
                </select>
            </div>

            <div class="form-group col-md-4">
                <label for="exampleFormControlSelect2">Shipping Cost (International Shipping 2 to 3 weeks)</label>
                <input class="form-control" type="number" name="shipping_cost" step="0.01" min="0.00" value="0.00" required>
                <div class="invalid-feedback">
                    Please provide a number for Shipping Cost
                </div>
            </div>

            <div class="form-group col-md-12">
                <label for="exampleFormControlTextarea1">Append Description</label>
                <textarea class="form-control" id="append_description" rows="10" name="append_description"></textarea>
            </div>

            <button type="submit" class="btn btn-primary">Get CSV</button>
        </div>
    {{ Form::close() }}



    <script>
        $('#append_description').summernote({
            placeholder: 'Please input the description will be appended to last',
            tabsize: 2,
            height: 300
        });
    </script>
@stop

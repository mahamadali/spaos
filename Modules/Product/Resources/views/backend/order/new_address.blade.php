@extends('backend.layouts.app')

@section('title')
    {{ $module_title }}
@endsection

@section('content')
    <br><br><br>
    <div class="card">
        <div class="card-body">
            <div class="address-form">
                <h4 class="form-title">Add New Address</h4>
                
            </div>
        </div>
    </div>
    <script>
        $(document).ready(function() {
            // Country change event
            $('#country').change(function() {
                var countryId = $(this).val();
                if (countryId) {
                    $('#state').prop('disabled', false);
                    $.ajax({
                        url: "{{ route('backend.orders.getStates') }}",
                        type: "GET",
                        data: {
                            country_id: countryId
                        },
                        success: function(data) {
                            $('#state').empty();
                            $('#state').append(
                                '<option value="" selected disabled>Select state</option>');
                            $.each(data, function(key, value) {
                                $('#state').append('<option value="' + key + '">' +
                                    value + '</option>');
                            });
                            $('#city').prop('disabled', true).empty().append(
                                '<option value="" selected disabled>Select city</option>');
                        }
                    });
                } else {
                    $('#state').prop('disabled', true).empty().append(
                        '<option value="" selected disabled>Select state</option>');
                    $('#city').prop('disabled', true).empty().append(
                        '<option value="" selected disabled>Select city</option>');
                }
            });

            // State change event
            $('#state').change(function() {
                var stateId = $(this).val();
                if (stateId) {
                    $('#city').prop('disabled', false);
                    $.ajax({
                        url: "{{ route('backend.orders.getCities') }}",
                        type: "GET",
                        data: {
                            state_id: stateId
                        },
                        success: function(data) {
                            $('#city').empty();
                            $('#city').append(
                                '<option value="" selected disabled>Select city</option>');
                            $.each(data, function(key, value) {
                                $('#city').append('<option value="' + key + '">' +
                                    value + '</option>');
                            });
                        }
                    });
                } else {
                    $('#city').prop('disabled', true).empty().append(
                        '<option value="" selected disabled>Select city</option>');
                }
            });
        });
    </script>
@endsection

@foreach($buttonTypes as $key => $value)
<button type="button" class="btn btn-secondary rounded btn-sm variable_button mt-2" id="variable_button" data-value="{{ '[[ '.$value->value.' ]]' }}">{{ $value->name }}</button>
@endforeach

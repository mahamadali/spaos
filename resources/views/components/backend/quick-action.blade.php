<form action="{{$url ?? ''}}" id="quick-action-form" class="form-disabled d-flex gap-3 align-items-stretch flex-wrap">
  @csrf
  {{$slot}}
  <input type="hidden" name="message_change-is_featured" value="{{ __('messages.change_is_featured_confirmation') }}">
  <input type="hidden" name="message_change-status" value="{{ __('messages.change_status_confirmation') }}">
  <input type="hidden" name="message_delete" value="{{ __('messages.delete_confirmation_action') }}">
  <button class="btn btn-gray" id="quick-action-apply">{{ __('messages.apply') }}</button>
</form>

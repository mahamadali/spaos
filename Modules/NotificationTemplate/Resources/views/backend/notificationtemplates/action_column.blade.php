<div class="d-flex gap-2 align-items-center">
  @hasPermission('edit_notification_template')
    <a href="{{route("backend.notification-templates.edit", $data->id)}}" class="btn btn-primary btn-sm" data-bs-toggle="tooltip" title="{{ __('messages.edit') }} "> <i class="fa-solid fa-pen-clip"></i></a>
  @endhasPermission


</div>

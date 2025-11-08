@section('title')
    {{ __($module_action) }} {{ __($module_title) }}
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card card-block card-stretch">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center p-3 flex-wrap gap-3">
                        <h5 class="font-weight-bold mb-0">{{ $pageTitle ?? __('messages.edit') }}</h5>
                        <a href="{{ route('backend.notification-templates.index') }}"
                            class="float-right btn btn-sm btn-primary"><i class="fa fa-angle-double-left"></i>
                            {{ __('messages.back') }}</a>
                    </div>
                    <div class="col-md-12">
         
            <form action="{{ route('backend.notification-templates.update', $data->id) }}" method="POST" id="notification-form">
                @method('PATCH') <!-- To specify PATCH method for update -->
                @csrf <!-- CSRF protection -->

                <!-- Hidden Fields -->
                <input type="hidden" name="id" value="{{ $data->id }}">
                <input type="hidden" name="type" value="{{ $data->type ?? '' }}">
                <input type="hidden" name="defaultNotificationTemplateMap[template_id]" value="{{ $data->id ?? '' }}">

            <div class="row align-items-center">
                <div class="col-lg-3 col-md-6">
                    <div class="form-group">
                        <label class="form-label">{{ __('messages.type') }} : <span class="text-danger">*</span></label>
                        <select name="type" class="form-select select2" id="type"
                            data-ajax--url="{{ route('backend.notificationtemplates.ajax-list', ['type' => 'constants_key', 'data_type' => 'notification_type']) }}"
                            data-ajax--cache="true" required disabled>
                            @if (isset($data->type))
                                <option value="{{ $data->type }}" selected>{{ $data->constant->name ?? '' }}</option>
                            @endif
                        </select>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="form-group">
                        <label class="form-label">{{ __('messages.to') }} : <span class="text-danger">*</span></label>
                        <select name="to[]" id="toSelect" class="form-select select2"
                            data-ajax--url="{{ route('backend.notificationtemplates.ajax-list', ['type' => 'constants_key', 'data_type' => 'notification_to']) }}"
                            data-ajax--cache="true" multiple required>
                            @if (isset($data) && $data->to != null)
                                @foreach (json_decode($data->to) as $to)
                                    <option value="{{ $to }}" selected>{{ $to }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="form-group">
                        @php
                            $toValues = json_decode($data->to, true) ?? [];
                        @endphp
                       
                        <label class="form-label" for="userTypeSelect">
                            {{ __('messages.user_type') }} <span class="text-danger">*</span>
                        </label>
                        <select name="defaultNotificationTemplateMap[user_type]" id="userTypeSelect" class="form-control select2js" required>
                            <!-- Options will go here, e.g., -->
                            <option value="">{{ __('messages.select_user_type') }}</option>
                            <!-- Dynamically populate the options if needed -->
                        </select>

                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                   

                    <div class="form-group">
                        <label class="form-label">{{ __('messages.status') }} </label>
                        <div class="form-check form-switch d-flex align-items-center justify-content-between gap-3 form-control">
                           <label class="form-label">{{ __('messages.status') }} </label>
                           <input class="form-check-input" name="status" type="checkbox" {{ (isset($data) && $data->status == 1) ? 'checked' : '' }}>
                        </div>
                        </div>
                </div>

                <div class="col-md-12 mt-3">
                    <div class="form-group">
                        <label class="form-label">{{ __('messages.parameters') }} :</label><br>
                        <div class="main_form form-control">
                            @if (isset($buttonTypes))
                                @include(
                                    'notificationtemplate::backend.notificationtemplates.perameters-buttons',
                                    ['buttonTypes' => $buttonTypes]
                                )
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-md-6 mt-3">
                    <label class="form-label">{{ __('messages.notification_template') }}</label>
                    <div class="form-group">
                        <label for="notificationSubject" class="form-label float-left">
                            {{ __('messages.subject') }} :
                        </label>
                        <input type="text" name="defaultNotificationTemplateMap[notification_subject]" id="notificationSubject" class="form-control" value="{{ old('defaultNotificationTemplateMap[notification_subject]', $data->defaultNotificationTemplateMap->notification_subject ?? '') }}">
                    </div>

                    <div class="form-group">
                        <label for="notificationTemplate" class="form-label float-left">
                            {{ __('messages.template') }} :
                        </label>
                        <input type="hidden" name="defaultNotificationTemplateMap[language]" value="en">
                        <textarea name="defaultNotificationTemplateMap[notification_template_detail]" id="mytextarea_mail" class="form-control textarea tinymce-template" rows="5">{{ old('defaultNotificationTemplateMap[notification_template_detail]', $data->defaultNotificationTemplateMap->notification_template_detail ?? '') }}</textarea>
                    </div>

                </div>
                <div class="col-md-6 mt-3">
                    <label class="form-label">
                        <h4>{{ __('messages.mail_template') }}</h4>
                    </label>
                 

                    <div class="form-group">
                        <label for="subject" class="form-label float-left">
                            {{ __('messages.subject') }} :
                        </label>
                        <input type="text" name="defaultNotificationTemplateMap[subject]" id="subject" class="form-control" value="{{ old('defaultNotificationTemplateMap[subject]', $data->defaultNotificationTemplateMap->subject ?? '') }}">

                        <!-- Hidden Input for Status -->
                        <input type="hidden" name="defaultNotificationTemplateMap[status]" value="1">
                    </div>

                    <div class="form-group">
                        <label for="templateDetail" class="form-label float-left">
                            {{ __('messages.template') }} :
                        </label>
                        <!-- Hidden Input for Language -->
                        <input type="hidden" name="defaultNotificationTemplateMap[language]" value="en">

                        <!-- Textarea for Template Detail -->
                        <textarea name="defaultNotificationTemplateMap[template_detail]" id="mytextarea" class="form-control textarea tinymce-template" rows="5">{{ old('defaultNotificationTemplateMap[template_detail]', $data->defaultNotificationTemplateMap->template_detail ?? '') }}</textarea>
                    </div>


                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 pt-2 mb-5">
                <button type="submit" class="btn btn-primary"><i class="far fa-save"></i> {{ __('messages.save') }}<i
                        class="md md-lock-open"></i></button>
                <a href="{{ route('backend.notification-templates.index') }}" class="btn btn-outline-primary"><i
                        class="fa-solid fa-angles-left"></i> {{ __('messages.close') }}<i class="md md-lock-open"></i></a>
            </div>
        </div>
                </div>
            </div>
        </div>
        

        
    </div>
@endsection

@push('after-scripts')
    <script>
         $(document).ready(function() {
            // Initialize TinyMCE

            (function($) {
                $(document).ready(function() {
                    tinymceEditor('.tinymce-templates', ' ', function(ed) {
                    }, 450)
                });

            })(jQuery);

            // Initialize Select2
            $('.select2js').select2({
                width: '100%'
            });
            $('.select2-tag').select2({
                width: '100%',
                tags: true,
                createTag: function(params) {
                    if (params.term.length > 2) {
                        return {
                            id: params.term,
                            text: params.term,
                            newTag: true
                        };
                    }
                    return null;
                }
            });


            // Handle change event for 'user_type' select
            $('select[name="defaultNotificationTemplateMap[user_type]"]').on('change', function() {
                var userType = $(this).val();
                var type = $('select[name="type"]').val();
                var editId = $('input[name="id"]').val(); 
                $.ajax({
                    url: "{{ route('backend.notificationtemplates.fetchnotification_data') }}",
                    method: "GET",
                    data: {
                        user_type: userType,
                        type: type,
                        id: editId 
                    },
                    success: function(response) {
                        if (response.success) {
                            var data = response.data;
                            $("input[name='defaultNotificationTemplateMap[subject]']").val(data
                                .subject);
                            tinymce.get('mytextarea').setContent(data.template_detail || '');
                            $("input[name='defaultNotificationTemplateMap[notification_message]']")
                                .val(data.notification_message);
                            $("input[name='defaultNotificationTemplateMap[notification_link]']")
                                .val(data.notification_link);

                            $("input[name='defaultNotificationTemplateMap[notification_subject]']").val(
                                data.notification_subject || '');
                            tinymce.get('mytextarea_mail').setContent(data
                                .notification_template_detail || '');
                        } else {
                            $("input[name='defaultNotificationTemplateMap[subject]']").val('');
                            tinymce.get('mytextarea').setContent('');
                            $("input[name='defaultNotificationTemplateMap[notification_message]']")
                                .val('');
                            $("input[name='defaultNotificationTemplateMap[notification_link]']")
                                .val('');

                            $("input[name='defaultNotificationTemplateMap[notification_subject]']").val(
                                '');
                            tinymce.get('mytextarea_mail').setContent('');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error(error);
                    }
                });
            });

            var toSelect = $('#toSelect');
            var userTypeSelect = $('#userTypeSelect');

            function updateUserTypeOptions(selectedValues) {
                userTypeSelect.empty();
                if (selectedValues) {
                    selectedValues.forEach(function(value) {
                        userTypeSelect.append(new Option(value, value));
                    });
                }
                userTypeSelect.trigger('change');
            }

            var initialSelectedValues = toSelect.val();
            updateUserTypeOptions(initialSelectedValues);

            toSelect.on('change', function() {
                var selectedValues = $(this).val();
                updateUserTypeOptions(selectedValues);
            });
        });
    </script>
@endpush

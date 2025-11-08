@extends ('backend.layouts.app')

<?php
$module_icon = "c-icon cil-list-rich";
?>

@section('title') {{ __('messages.log_viewer_dashboard') }} @endsection

@section('content')
<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-8">
                <h4 class="card-title mb-0">
                    <i class="{{$module_icon}}"></i> {{ __('messages.log') }} [{{ $log->date }}]
                    <small class="text-muted"> {{ __('messages.details') }} </small>
                </h4>
                <div class="small text-muted">
                    {{ __('messages.log_viewer_module') }}
                </div>
            </div>

            <div class="col-4">
                <div class="btn-toolbar float-end" role="toolbar" aria-label="{{ __('messages.toolbar_with_button_groups') }}">
                    <x-backend.buttons.return-back />
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col">

                <div class="row">
                    <div class="col-lg-2">
                        {{-- Log Menu --}}
                        <div class="card mb-4">
                            <div class="card-header"><i class="fa fa-fw fa-flag"></i> {{ __('messages.levels') }}</div>
                            <div class="list-group list-group-flush log-menu">
                                @foreach($log->menu() as $levelKey => $item)
                                    @if ($item['count'] === 0)
                                        <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-center disabled">
                                            <span class="level-name">{!! $item['icon'] !!} {{ $item['name'] }}</span>
                                            <span class="badge empty">{{ $item['count'] }}</span>
                                        </a>
                                    @else
                                        <a href="{{ $item['url'] }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center level-{{ $levelKey }}{{ $level === $levelKey ? ' active' : ''}}">
                                            <span class="level-name">{!! $item['icon'] !!} {{ $item['name'] }}</span>
                                            <span class="badge badge-level-{{ $levelKey }}">{{ $item['count'] }}</span>
                                        </a>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-10">
                        {{-- Log Details --}}
                        <div class="card mb-4">
                            <div class="card-header">
                                <strong>
                                    {{ __('messages.log_info') }}
                                </strong>
                                <div class="btn-toolbar float-end">
                                    <a href="{{ route('log-viewer::logs.download', [$log->date]) }}" class="btn btn-success">
                                        <i class="fas fa-download"></i>&nbsp;{{ __('messages.download') }}
                                    </a>
                                    <a href="#delete-log-modal" class="btn btn-danger ms-1" data-bs-toggle="modal">
                                         <i class="fa-solid fa-trash"></i>&nbsp;{{ __('messages.delete') }}
                                    </a>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-condensed mb-0">
                                    <tbody>
                                        <tr>
                                            <td>{{ __('messages.file_path') }} :</td>
                                            <td colspan="7">{{ $log->getPath() }}</td>
                                        </tr>
                                        <tr>
                                            <td>{{ __('messages.log_entries') }} : </td>
                                            <td>
                                                <span class="badge bg-primary">{{ $entries->total() }}</span>
                                            </td>
                                            <td>{{ __('messages.size') }} :</td>
                                            <td>
                                                <span class="badge bg-primary">{{ $log->size() }}</span>
                                            </td>
                                            <td>{{ __('messages.created_at') }} :</td>
                                            <td>
                                                <span class="badge bg-primary">{{ $log->createdAt() }}</span>
                                            </td>
                                            <td>{{ __('messages.updated_at') }} :</td>
                                            <td>
                                                <span class="badge bg-primary">{{ $log->updatedAt() }}</span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="card-footer">
                                {{-- Search --}}
                                <form action="{{ route('log-viewer::logs.search', [$log->date, $level]) }}" method="GET">
                                    <div class="form-group">
                                        <div class="input-group">
                                            <input id="query" name="query" class="form-control" value="{!! request('query') !!}" placeholder="{{ __('messages.type_here_to_search') }}">
                                            <div class="input-group-append">
                                                @if (request()->has('query'))
                                                    <a href="{{ route('log-viewer::logs.show', [$log->date]) }}" class="btn btn-secondary">
                                                        <i class="fa fa-fw fa-times"></i>
                                                    </a>
                                                @endif
                                                <button id="search-btn" class="btn btn-primary">
                                                    <span class="fa fa-fw fa-search"></span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        {{-- Log Entries --}}
                        <div class="card mb-4 card-accent-secondary">
                            @if ($entries->hasPages())
                                <div class="card-header">
                                    <span class="badge bg-info float-end">
                                        {{ __('messages.page') }} {!! $entries->currentPage() !!} {{ __('messages.of') }} {!! $entries->lastPage() !!}
                                    </span>
                                </div>
                            @endif

                            <div class="table-responsive">
                                <table id="entries" class="table mb-0">
                                    <thead>
                                        <tr>
                                            <th>{{ __('messages.env') }}</th>
                                            <th style="width: 120px;">{{ __('messages.level') }}</th>
                                            <th style="width: 65px;">{{ __('messages.time') }}</th>
                                            <th>{{ __('messages.header') }}</th>
                                            <th class="text-end">{{ __('messages.actions') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($entries as $key => $entry)
                                            <tr>
                                                <td>
                                                    <span class="badge badge-env">{{ $entry->env }}</span>
                                                </td>
                                                <td>
                                                    <span class="badge badge-level-{{ $entry->level }}">
                                                        {!! $entry->level() !!}
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-secondary">
                                                        {{ $entry->datetime->format('H:i:s') }}
                                                    </span>
                                                </td>
                                                <td>
                                                    {{ $entry->header }}
                                                </td>
                                                <td class="text-end">
                                                    @if ($entry->hasStack())
                                                        <a class="btn btn-sm btn-light" role="button" data-bs-toggle="collapse" href="#log-stack-{{ $key }}" aria-expanded="false" aria-controls="log-stack-{{ $key }}">
                                                            <i class="fa fa-toggle-on"></i> {{ __('messages.stack') }}
                                                        </a>
                                                    @endif
                                                </td>
                                            </tr>
                                            @if ($entry->hasStack())
                                                <tr>
                                                    <td colspan="5" class="stack py-0">
                                                        <div class="stack-content collapse" id="log-stack-{{ $key }}">
                                                            {!! $entry->stack() !!}
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endif
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center">
                                                    <span class="badge bg-secondary">{{ trans('log-viewer::general.empty-logs') }}</span>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {!! $entries->appends(compact('query'))->render() !!}
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>



{{-- DELETE MODAL --}}
<div id="delete-log-modal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <form id="delete-log-form" action="{{ route('log-viewer::logs.delete') }}" method="POST">
            <input type="hidden" name="_method" value="DELETE">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <input type="hidden" name="date" value="{{ $log->date }}">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('messages.delete_log_file') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('messages.close') }}"></button>
                </div>
                <div class="modal-body">
                    <p>{{ __('messages.delete_confirmation') }} <span class="badge bg-danger">{{ __('messages.delete') }}</span> {{ __('messages.this_log_file') }} <span class="badge text-bg-warning">{{ $log->date }}</span>?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary me-auto" data-bs-dismiss="modal">{{ __('messages.cancel') }}</button>
                    <button type="submit" class="btn btn-sm btn-danger" data-loading-text="{{ __('messages.loading') }}">{{ __('messages.delete_file') }}</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('after-scripts')
    <script>
        $(function () {
            var deleteLogModal = $('div#delete-log-modal'),
                deleteLogForm  = $('form#delete-log-form'),
                submitBtn      = deleteLogForm.find('button[type=submit]');

            deleteLogForm.on('submit', function(event) {
                event.preventDefault();
                submitBtn.button('loading');

                $.ajax({
                    url:      $(this).attr('action'),
                    type:     $(this).attr('method'),
                    dataType: 'json',
                    data:     $(this).serialize(),
                    success: function(data) {
                        submitBtn.button('reset');
                        if (data.result === 'success') {
                            deleteLogModal.modal('hide');
                            location.replace("{{ route('log-viewer::logs.list') }}");
                        }

                    },
                    error: function(xhr, textStatus, errorThrown) {

                        console.error(errorThrown);
                        submitBtn.button('reset');
                    }
                });

                return false;
            });

            @unless (empty(log_styler()->toHighlight()))
                @php
                    $htmlHighlight = version_compare(PHP_VERSION, '7.4.0') >= 0
                        ? join('|', log_styler()->toHighlight())
                        : join(log_styler()->toHighlight(), '|');
                @endphp

                $('.stack-content').each(function() {
                    var $this = $(this);
                    var html = $this.html().trim()
                        .replace(/({!! $htmlHighlight !!})/gm, '<strong>$1</strong>');

                    $this.html(html);
                });
            @endunless
        });
    </script>
@endpush

@push('after-styles')
@include('log-viewer::laravel-starter.partials.style')
@endpush

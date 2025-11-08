@hasPermission('menu_builder_sidebar')
<aside id="leftsidebar" class="sidebar">
    <div class="menu">
        <ul class="list">
            {{-- USER INFO BLOCK --}}
            <li>
                <div class="user-info">
                    <div class="image">
                        <a href="{{ route('backend.dashboard') }}">
                            <img src="{{ auth()->user()->profile_image_url }}" alt="User">
                        </a>
                    </div>

                    <div class="detail">
                        <h4>{{ auth()->user()->name }}</h4>
                        <small>{{ auth()->user()->roles()->first()->name ?? '' }}</small>
                    </div>

                    {{-- optional icons --}}
                    <a href="{{ route('backend.notifications.index') }}" title="Notifications"><i class="zmdi zmdi-notifications"></i></a>
                    <a href="{{ route('backend.settings') }}" title="Settings"><i class="zmdi zmdi-settings"></i></a>
                    <a href="{{ route('logout') }}" title="Sign out"><i class="zmdi zmdi-power"></i></a>
                </div>
            </li>

            <li class="header">{{ __('messages.main') }}</li>

            {{-- DYNAMIC MENU FROM LARAVEL MENU --}}
            @php
                $menu = new \App\Http\Middleware\GenerateMenus();
                $menu = $menu->handle('menu', 'vertical', 'ARRAY_MENU');
            @endphp
            @include(config('laravel-menu.views.bootstrap-items'), ['items' => $menu->roots()])

            {{-- optionally add extra progress bars --}}
            <li class="header">{{ __('messages.extra') }}</li>
            <li>
                <div class="progress-container progress-primary m-t-10">
                    <span class="progress-badge">{{ __('messages.traffic_this_month') }}</span>
                    <div class="progress">
                        <div class="progress-bar progress-bar-warning" role="progressbar" aria-valuenow="67"
                            aria-valuemin="0" aria-valuemax="100" style="width: 67%;">
                            <span class="progress-value">67%</span>
                        </div>
                    </div>
                </div>
            </li>
        </ul>
    </div>
</aside>
@endhasPermission
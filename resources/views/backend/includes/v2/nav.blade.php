<nav class="navbar">
    <div class="col-12">
        <div class="navbar-header">
            <a href="javascript:void(0);" class="bars"></a>
            <a class="navbar-brand" href="index.html"><img src="{{ asset(setting('logo')) }}" height="30"
                    alt="{{ app_name() }}"></a>
        </div>
        <ul class="nav navbar-nav navbar-left">
            <li><a href="javascript:void(0);" class="ls-toggle-btn" data-close="true"><i class="zmdi zmdi-swap"></i></a>
            </li>
            {{-- <li class="hidden-sm-down">
                    <div class="input-group">
                        <input type="text" class="form-control" placeholder="Search...">
                        <span class="input-group-addon">
                            <i class="zmdi zmdi-search"></i>
                        </span>
                    </div>
                </li> --}}
        </ul>
        <ul class="nav navbar-nav navbar-right">
            @php
                $notifications_count = optional(auth()->user())->unreadNotifications->count();
            @endphp
            <li class="dropdown">
                <a href="javascript:void(0);" class="dropdown-toggle notification_list" data-toggle="dropdown"
                    role="button"><i class="zmdi zmdi-notifications"></i>
                    <div class="notify">
                        @if ($notifications_count > 0)
                            <span class="heartbit"></span><span class="point"></span>
                            <span class="notification-alert">{{ $notifications_count }}</span>
                        @endif
                    </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-right slideDown">
                    <li class="header">{{ __('messages.all_notifications') }} ({{ $notifications_count }})</li>
                    <li class="body">
                        <div class="notification_data"></div>
                    </li>
                    <li class="footer">
                        @if ($notifications_count > 0)
                            <a href="{{ route('backend.notifications.markAllAsRead') }}"
                                class="text-primary notifyList">{{ __('messages.mark_all_as_read') }}</a>
                        @endif
                        <a href="{{ route('backend.notifications.index') }}">{{ __('messages.view_all') }}</a>
                    </li>
                </ul>
            </li>
            <li class="dropdown">
                <a href="javascript:void(0);" class="dropdown-toggle" data-toggle="dropdown" role="button">
                    <i class="zmdi zmdi-translate"></i>
                </a>

                <ul class="dropdown-menu dropdown-menu-right slideDown">
                    <li class="header">{{ __('messages.change_language') }}</li>
                    <li class="">
                        <ul class="menu list-unstyled">

                            @foreach (config('app.available_locales') as $locale => $lang)
                                <li>
                                    <a href="{{ route('language.switch', $locale) }}">
                                        <div
                                            class="icon-circle @if (app()->getLocale() == $locale) bg-green @else bg-grey @endif">
                                            <i class="zmdi zmdi-flag"></i>
                                        </div>

                                        <div class="menu-info">
                                            <h4 style="text-transform: uppercase;">
                                                {{ $lang }}
                                                @if (app()->getLocale() == $locale)
                                                    <span>(Active)</span>
                                                @endif
                                            </h4>
                                        </div>
                                    </a>
                                </li>
                            @endforeach

                        </ul>
                    </li>
                </ul>
            </li>
            <li class="dropdown">
                <a href="javascript:void(0);" class="dropdown-toggle" data-toggle="dropdown" role="button">
                    <i class="zmdi zmdi-brightness-6"></i>
                </a>

                <ul class="dropdown-menu dropdown-menu-right slideDown">
                    <li class="header">{{ __('messages.change_theme') }}</li>
                    <li class="">
                        <ul class="menu list-unstyled">

                            @php
                                $themes = [
                                    'light' => __('messages.Light_Mode'),
                                    'dark'  => __('messages.Dark_Mode'),
                                ];
                            @endphp

                            @foreach ($themes as $key => $label)
                                <li>
                                    <a href="Javascript:void(0);" class="change-mode d-flex" data-new_theme_mode="{{ $key }}">
                                        <div
                                            class="icon-circle">
                                            @if($key == 'light')
                                                <i class="zmdi zmdi-sun"></i>
                                            @else
                                                <i class="zmdi zmdi-star"></i>
                                            @endif
                                        </div>

                                        <div class="menu-info my-auto">
                                            <h4 style="text-transform: capitalize;">
                                                {{ $label }}
                                            </h4>
                                        </div>
                                    </a>
                                </li>
                            @endforeach

                        </ul>
                    </li>
                </ul>
            </li>
            <li>
                <a href="javascript:void(0);" class="fullscreen hidden-sm-down" data-provide="fullscreen"
                    data-close="true"><i class="zmdi zmdi-fullscreen"></i></a>
            </li>
            <li><a href="{{ route('logout') }}" class="mega-menu" data-close="true"><i class="zmdi zmdi-power"></i></a>
            </li>
            {{-- <li class=""><a href="javascript:void(0);" class="js-right-sidebar" data-close="true"><i
                        class="zmdi zmdi-settings zmdi-hc-spin"></i></a></li> --}}
        </ul>
    </div>
</nav>

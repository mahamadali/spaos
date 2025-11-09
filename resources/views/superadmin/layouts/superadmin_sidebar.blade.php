{{-- resources/views/superadmin/layouts/superadmin_sidebar.blade.php --}}

<li class="header">{{ __('dashboard.main') }}</li>

<li class="nav-item {{ request()->routeIs('backend.home') ? 'active' : '' }}">
    <a target="_self" class="nav-link {{ request()->routeIs('backend.home') ? 'active' : '' }}" href="{{ route('backend.home') }}">
        <i class="zmdi zmdi-view-dashboard" data-bs-toggle="tooltip" data-bs-placement="right" aria-label="Dashboard" data-bs-original-title="Dashboard"></i>
        <span class="item-name">{{ __('dashboard.title') }}</span>
    </a>
</li>

<li class="nav-item {{ request()->routeIs('backend.users.index') ? 'active' : '' }}">
    <a target="_self" class="nav-link {{ request()->routeIs('backend.users.index') ? 'active' : '' }}" href="{{ route('backend.users.index') }}">
        <i class="zmdi zmdi-accounts" data-bs-toggle="tooltip" data-bs-placement="right" aria-label="Users" data-bs-original-title="Vendors"></i>
        <span class="item-name">{{ __('messages.vendors') }}</span>
    </a>
</li>

@php
    $subscriptionsActive = request()->is('app/subscriptions*') && !request()->routeIs('backend.subscriptions.all_subscription');
@endphp

<li class="{{ $subscriptionsActive ? 'active open' : '' }}">
    <a href="javascript:void(0);" class="menu-toggle">
        <i class="zmdi zmdi-card"></i>
        <span>{{ __('sidebar.subscriptions') }}</span>
    </a>

    <ul class="ml-menu" style="{{ $subscriptionsActive ? 'display:block;' : '' }}">
        <li class="{{ request()->routeIs('backend.subscriptions.all_subscription') ? 'active' : '' }}">
            <a href="{{ route('backend.subscriptions.all_subscription') }}">
                {{ __('report.all_subscription') }}
            </a>
        </li>

        <li class="{{ request()->routeIs('backend.subscriptions.index') ? 'active' : '' }}">
            <a href="{{ route('backend.subscriptions.index') }}">
                {{ __('messages.active') }} {{ __('sidebar.subscriptions') }}
            </a>
        </li>

        <li class="{{ request()->routeIs('backend.subscriptions.pending') ? 'active' : '' }}">
            <a href="{{ route('backend.subscriptions.pending') }}">
                {{ __('order_report.pending') }} {{ __('sidebar.subscriptions') }}
            </a>
        </li>

        <li class="{{ request()->routeIs('backend.subscriptions.expired') ? 'active' : '' }}">
            <a href="{{ route('backend.subscriptions.expired') }}">
                {{ __('promotion.lbl_expired') }} {{ __('sidebar.subscriptions') }}
            </a>
        </li>
    </ul>
</li>

<li class="nav-item {{ request()->routeIs('backend.subscription.plans.index') ? 'active' : '' }}">
    <a target="_self" class="nav-link {{ request()->routeIs('backend.subscription.plans.index') ? 'active' : '' }}" href="{{ route('backend.subscription.plans.index') }}">
        <i class="zmdi zmdi-layers" data-bs-toggle="tooltip" data-bs-placement="right" aria-label="Plans" data-bs-original-title="Plans"></i>
        <span class="item-name">{{ __('sidebar.plans') }}</span>
    </a>
</li>

<li class="nav-item {{ request()->routeIs('backend.payment.index') ? 'active' : '' }}">
    <a target="_self" class="nav-link {{ request()->routeIs('backend.payment.index') ? 'active' : '' }}" href="{{ route('backend.payment.index') }}">
        <i class="zmdi zmdi-card" data-bs-toggle="tooltip" data-bs-placement="right" aria-label="Payments" data-bs-original-title="Payments"></i>
        <span class="item-name">{{ __('sidebar.payments') }}</span>
    </a>
</li>

<li class="nav-item {{ request()->routeIs('backend.promotions.index') ? 'active' : '' }}">
    <a target="_self" class="nav-link {{ request()->routeIs('backend.promotions.index') ? 'active' : '' }}" href="{{ route('backend.promotions.index') }}">
        <i class="zmdi zmdi-ticket-star" data-bs-toggle="tooltip" data-bs-placement="right" aria-label="Promotions" data-bs-original-title="Promotions"></i>
        <span class="item-name">{{ __('promotion.title') }}</span>
    </a>
</li>

<li class="header">{{ __('dashboard.others') }}</li>

<li class="nav-item {{ request()->routeIs('backend.pages.index') ? 'active' : '' }}">
    <a target="_self" class="nav-link {{ request()->routeIs('backend.pages.index') ? 'active' : '' }}" href="{{ route('backend.pages.index') }}">
        <i class="zmdi zmdi-file-text" data-bs-toggle="tooltip" data-bs-placement="right" aria-label="Pages" data-bs-original-title="Pages"></i>
        <span class="item-name">{{ __('page.title') }}</span>
    </a>
</li>

<li class="nav-item {{ request()->routeIs('backend.plan.tax.index') ? 'active' : '' }}">
    <a target="_self" class="nav-link {{ request()->routeIs('backend.plan.tax.index') ? 'active' : '' }}" href="{{ route('backend.plan.tax.index') }}">
        <i class="zmdi zmdi-money" data-bs-toggle="tooltip" data-bs-placement="right" aria-label="Tax" data-bs-original-title="Tax"></i>
        <span class="item-name">{{ __('report.lbl_taxes') }}</span>
    </a>
</li>

<li class="nav-item {{ request()->routeIs('backend.blog.index') ? 'active' : '' }}">
    <a target="_self" class="nav-link {{ request()->routeIs('backend.blog.index') ? 'active' : '' }}" href="{{ route('backend.blog.index') }}">
        <i class="zmdi zmdi-file" data-bs-toggle="tooltip" data-bs-placement="right" aria-label="Blog" data-bs-original-title="Blog"></i>
        <span class="item-name">{{ __('sidebar.lbl_blog') }}</span>
    </a>
</li>

<li class="nav-item {{ request()->routeIs('backend.faq.index') ? 'active' : '' }}">
    <a target="_self" class="nav-link {{ request()->routeIs('backend.faq.index') ? 'active' : '' }}" href="{{ route('backend.faq.index') }}">
        <i class="zmdi zmdi-help" data-bs-toggle="tooltip" data-bs-placement="right" aria-label="FAQ" data-bs-original-title="FAQ"></i>
        <span class="item-name">{{ __('sidebar.lbl_faq') }}</span>
    </a>
</li>

@php
    $notificationsActive = request()->is('app/notifications*');
@endphp

<li class="{{ $notificationsActive ? 'active open' : '' }}">
    <a href="javascript:void(0);" class="menu-toggle">
        <i class="zmdi zmdi-notifications"></i>
        <span>{{ __('notification.title') }}</span>
    </a>

    <ul class="ml-menu" style="{{ $notificationsActive ? 'display:block;' : '' }}">
        <li class="{{ request()->routeIs('backend.notifications.index') ? 'active' : '' }}">
            <a href="{{ route('backend.notifications.index') }}">
                {{ __('messages.list') }}
            </a>
        </li>

        <li class="{{ request()->routeIs('backend.notification-templates.index') ? 'active' : '' }}">
            <a href="{{ route('backend.notification-templates.index') }}">
                {{ __('messages.templates') }}
            </a>
        </li>
    </ul>
</li>

<li class="nav-item {{ request()->routeIs('backend.settings') ? 'active' : '' }}">
    <a target="_self" class="nav-link {{ request()->routeIs('backend.settings') ? 'active' : '' }}" href="{{ route('backend.settings') }}">
        <i class="zmdi zmdi-settings" data-bs-toggle="tooltip" data-bs-placement="right" aria-label="Settings" data-bs-original-title="Settings"></i>
        <span class="item-name">{{ __('sidebar.settings') }}</span>
    </a>
</li>

<li class="nav-item {{ request()->routeIs('backend.website-setting.index') ? 'active' : '' }}">
    <a target="_self" class="nav-link {{ request()->routeIs('backend.website-setting.index') ? 'active' : '' }}" href="{{ route('backend.website-setting.index') }}">
        <i class="zmdi zmdi-settings" data-bs-toggle="tooltip" data-bs-placement="right" aria-label="Landing Page Settings" data-bs-original-title="Landing Page Settings"></i>
        <span class="item-name">{{ __('sidebar.landing_page_settings') }}</span>
    </a>
</li>


<li class="nav-item static-item">
     <a class="nav-link static-item disabled">
         <span class="default-icon">{{ __('dashboard.main') }}</span>
     </a>
</li>

<li class="nav-item {{ request()->routeIs('backend.home') ? 'active' : '' }}">
    <a target="_self" class="nav-link {{ request()->routeIs('backend.home') ? 'active' : '' }}" href="{{ route('backend.home') }}">
        <i class="icon fa-solid fa-gauge" data-bs-toggle="tooltip" data-bs-placement="right" aria-label="Dashboard" data-bs-original-title="Dashboard"></i>
        <span class="item-name">{{ __('dashboard.title') }}</span>
    </a>
</li>

<li class="nav-item {{ request()->routeIs('backend.users.index') ? 'active' : '' }}">
    <a target="_self" class="nav-link {{ request()->routeIs('backend.users.index') ? 'active' : '' }}" href="{{ route('backend.users.index') }}">
        <i class="fa-solid fa-users" data-bs-toggle="tooltip" data-bs-placement="right" aria-label="Users" data-bs-original-title="Vendors"></i>
        <span class="item-name">{{ __('messages.vendors') }}</span>
    </a>
</li>

<li class="nav-item {{ request()->is('app/subscriptions*')  && !request()->routeIs('backend.subscriptions.all_subscription')  ? 'active' : '' }}">
    <a href="#subscriptions" data-bs-parent="#sidebar-menu" class="nav-link {{ request()->is('app/subscriptions*')  && !request()->routeIs('backend.subscriptions.all_subscription') ? 'active' : '' }}" data-bs-toggle="collapse" role="button" aria-expanded="{{ request()->is('app/subscriptions*')  && !request()->routeIs('backend.subscriptions.all_subscription')? 'true' : 'false' }}" aria-controls="subscriptions">
        <i class="icon fa-solid fa-crown" data-bs-toggle="tooltip" data-bs-placement="right" aria-label="Subscriptions" data-bs-original-title="Subscriptions"></i>
        <i class="sidenav-mini-icon"> S </i>
        <span class="item-name">{{ __('sidebar.subscriptions') }}</span>
        <i class="right-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" class="icon-18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
        </i>
    </a>

   


    <ul class="sub-nav collapse {{ request()->is('app/subscriptions*') && !request()->routeIs('backend.subscriptions.all_subscription')  ? 'show' : '' }}" id="subscriptions" data-bs-parent="#sidebar-menu">
      
      <li class="nav-item {{ request()->routeIs('backend.subscriptions.all_subscription') ? 'active' : '' }}">
        <a target="_self" class="nav-link {{ request()->routeIs('backend.subscriptions.all_subscription') ? 'active' : '' }}" href="{{ route('backend.subscriptions.all_subscription') }}">
          <i class="fa-solid fa-circle" style="font-size: .625rem" data-bs-toggle="tooltip" data-bs-placement="right" aria-label="Tax" data-bs-original-title="Tax"></i>
          <span class="item-name">{{ __('report.all_subscription') }}</span>
       </a>
      </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('backend.subscriptions.index') ? 'active' : '' }}" href="{{ route('backend.subscriptions.index') }}">
                <i class="fa-solid fa-circle" style="font-size: .625rem"></i>
                <span class="item-name">{{ __('messages.active') }} {{ __('sidebar.subscriptions') }}</span>
            </a>
        </li>

       
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('backend.subscriptions.pending') ? 'active' : '' }}" href="{{ route('backend.subscriptions.pending') }}">
                <i class="fa-solid fa-circle" style="font-size: .625rem"></i>
                <span class="item-name">{{ __('order_report.pending') }} {{ __('sidebar.subscriptions') }}</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('backend.subscriptions.expired') ? 'active' : '' }}" href="{{ route('backend.subscriptions.expired') }}">
                <i class="fa-solid fa-circle" style="font-size: .625rem"></i>
                <span class="item-name">{{ __('promotion.lbl_expired') }} {{ __('sidebar.subscriptions') }}</span>
            </a>
        </li>
    </ul>
</li>

<li class="nav-item {{ request()->routeIs('backend.subscription.plans.index') ? 'active' : '' }}">
    <a target="_self" class="nav-link {{ request()->routeIs('backend.subscription.plans.index') ? 'active' : '' }}" href="{{ route('backend.subscription.plans.index') }}">
        <i class="icon fa-solid fa-layer-group" data-bs-toggle="tooltip" data-bs-placement="right" aria-label="Plans" data-bs-original-title="Plans"></i>
        <span class="item-name">{{ __('sidebar.plans') }}</span>
    </a>
</li>

<li class="nav-item {{ request()->routeIs('backend.payment.index') ? 'active' : '' }}">
    <a target="_self" class="nav-link {{ request()->routeIs('backend.payment.index') ? 'active' : '' }}" href="{{ route('backend.payment.index') }}">
        <i class="icon fa-solid fa-credit-card" data-bs-toggle="tooltip" data-bs-placement="right" aria-label="Payments" data-bs-original-title="Payments"></i>
        <span class="item-name">{{ __('sidebar.payments') }}</span>
    </a>
</li>

<li class="nav-item {{ request()->routeIs('backend.promotions.index') ? 'active' : '' }}">
    <a target="_self" class="nav-link {{ request()->routeIs('backend.promotions.index') ? 'active' : '' }}" href="{{ route('backend.promotions.index') }}">
        <i class="icon fa-solid fa-ticket" data-bs-toggle="tooltip" data-bs-placement="right" aria-label="Promotions" data-bs-original-title="Promotions"></i>
        <span class="item-name">{{ __('promotion.title') }}</span>
    </a>
</li>

<li class="nav-item static-item">
     <a class="nav-link static-item disabled">
         <span class="default-icon">{{ __('dashboard.others') }}</span>
     </a>
</li>

<li class="nav-item {{ request()->routeIs('backend.pages.index') ? 'active' : '' }}">
    <a target="_self" class="nav-link {{ request()->routeIs('backend.pages.index') ? 'active' : '' }}" href="{{ route('backend.pages.index') }}">
        <i class="icon fa-solid fa-file" data-bs-toggle="tooltip" data-bs-placement="right" aria-label="Pages" data-bs-original-title="Pages"></i><span class="item-name">{{ __('page.title') }}</span><i class="icon "></i>
    </a>
</li>

<li class="nav-item {{ request()->routeIs('backend.plan.tax.index') ? 'active' : '' }}">
    <a target="_self" class="nav-link {{ request()->routeIs('backend.plan.tax.index') ? 'active' : '' }}" href="{{ route('backend.plan.tax.index') }}">
        <i class="icon fa-solid fa-money-bill-trend-up" data-bs-toggle="tooltip" data-bs-placement="right" aria-label="Tax" data-bs-original-title="Tax"></i>
        <span class="item-name">{{ __('report.lbl_taxes') }}</span>
    </a>
</li>
<li class="nav-item {{ request()->routeIs('backend.blog.index') ? 'active' : '' }}">
    <a target="_self" class="nav-link {{ request()->routeIs('backend.blog.index') ? 'active' : '' }}" href="{{ route('backend.blog.index') }}">
        <i class="icon fa-solid fa-blog" data-bs-toggle="tooltip" data-bs-placement="right" aria-label="Blog" data-bs-original-title="Blog"></i><span class="item-name">{{ __('sidebar.lbl_blog') }}</span><i class="icon"></i>
    </a>
</li>

<li class="nav-item {{ request()->routeIs('backend.faq.index') ? 'active' : '' }}">
    <a target="_self" class="nav-link {{ request()->routeIs('backend.faq.index') ? 'active' : '' }}" href="{{ route('backend.faq.index') }}">
        <i class="icon fa-solid fa-question-circle" data-bs-toggle="tooltip" data-bs-placement="right" aria-label="FAQ" data-bs-original-title="FAQ"></i><span class="item-name">{{ __('sidebar.lbl_faq') }}</span><i class="icon"></i>
    </a>
</li>
<li class="nav-item  {{ request()->is('app/notifications*') ? 'active' : '' }}">
    <a href="#notifications" data-bs-parent="#sidebar-menu" class="nav-link collapsed {{ request()->is('app/notifications*') ? 'active' : '' }}" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="collapseExample">
        <i class="icon fa-solid fa-bell" data-bs-toggle="tooltip" data-bs-placement="right" aria-label="Notifications" data-bs-original-title="Notifications"></i><i class="sidenav-mini-icon"> N </i><span class="item-name">{{ __('notification.title') }}</span>
        <i class="right-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" class="icon-18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
        </i>
    </a>
    <ul class="sub-nav collapse {{ request()->is('app/notifications*') ? 'show' : '' }}" id="notifications" data-bs-parent="#sidebar-menu" style="">
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('backend.notifications.index') ? 'active' : '' }}" href="{{ route('backend.notifications.index')}}">
                <i class="fa-solid fa-circle" style="font-size: .625rem"></i><span class="item-name">{{ __('messages.list') }}</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('backend.notification-templates.index') ? 'active' : '' }}" href="{{ route('backend.notification-templates.index')}}">
                <i class="fa-solid fa-circle" style="font-size: .625rem"></i><span class="item-name">{{ __('messages.templates') }}</span>
            </a>
        </li>
    </ul>
</li>

<li class="nav-item {{ request()->routeIs('backend.settings') ? 'active' : '' }}">
    <a target="_self" class="nav-link {{ request()->routeIs('backend.settings') ? 'active' : '' }}" href="{{ route('backend.settings') }}">
        <i class="icon fa-solid fa-gear" data-bs-toggle="tooltip" data-bs-placement="right" aria-label="Settings" data-bs-original-title="Settings"></i>
        <span class="item-name">{{ __('sidebar.settings')}}</span>
    </a>
</li>

<li class="nav-item {{ request()->routeIs('backend.website-setting.index') ? 'active' : '' }}">
    <a target="_self" class="nav-link {{ request()->routeIs('backend.website-setting.index') ? 'active' : '' }}" href="{{ route('backend.website-setting.index') }}">
        <i class="icon fa-solid fa-cogs" data-bs-toggle="tooltip" data-bs-placement="right" aria-label="Landing Page Settings" data-bs-original-title="Landing Page Settings"></i><span class="item-name">{{ __('sidebar.landing_page_settings') }}</span><i class="icon"></i>
    </a>
</li>


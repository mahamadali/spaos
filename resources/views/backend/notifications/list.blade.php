<li class="body">
    <ul class="menu list-unstyled">
        @forelse($notifications->sortByDesc('created_at')->take(5) as $n)

        @php
            $g      = $n->data['data']['notification_group'] ?? '';
            $type   = $n->data['data']['notification_type'] ?? '';
            $link   = '#';

            if($g=='booking'){
                $link = route('backend.bookings.index', ['booking_id'=>$n->data['data']['id']]);
                $icon = 'zmdi-calendar';
                $bg   = 'bg-blue';
            }elseif($g=='subscription'){
                $link = route('backend.subscriptions.index');
                $icon = 'zmdi-accounts';
                $bg   = 'bg-purple';
            }else{
                $link = route('backend.orders.show', ['id'=>$n->data['data']['id']]);
                $icon = 'zmdi-shopping-cart';
                $bg   = 'bg-amber';
            }
        @endphp

        <li class="{{ $n->read_at ? '' : 'notify-list-bg' }}">
            <a href="{{ $link }}">
                <div class="icon-circle {{ $bg }}">
                    <i class="zmdi {{ $icon }}"></i>
                </div>

                <div class="menu-info">
                    <h4>{!! $n->data['subject'] !!}</h4>
                    <p>
                        <i class="zmdi zmdi-time"></i>
                        {{ $n->created_at->diffForHumans() }}
                    </p>
                </div>
            </a>
        </li>

        @empty
        <li>
            <a href="javascript:void(0)">
                <div class="menu-info">
                    <h4>{{ __('messages.no_notification') }}</h4>
                </div>
            </a>
        </li>
        @endforelse
        </ul>

</li>
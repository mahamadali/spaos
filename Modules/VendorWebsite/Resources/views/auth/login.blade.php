@extends('vendorwebsite::layouts.guest')

@section('content')

    <section class="auth-page"
        style="background-image: url('{{ asset('img/vendorwebsite/sign-in-bg.png') }}'); backgound-repat: no-repeat; background-size: cover;">
        <div class="containe h-100">
            <div class="row align-items-center justify-content-center h-100">
                <div class="col-xl-4 col-lg-6 col-md-8 my-5">
                    <div class="py-5 px-3">
                        <div class="register-bg register-background-padding">
                            <div class="text-center mb-5">
                                <a class="navbar-brand text-primary" href="{{ route('vendor.index') }}">
                                    <div class="logo-main">
                                        <div class="logo-mini d-none">
                                            <img src="{{ getVendorSetting('mini_logo') ? asset(getVendorSetting('mini_logo')) : asset('img/logo/mini_logo.png') }}"
                                                height="50" alt="{{ app_name() }}">
                                        </div>
                                        <div class="logo-normal">
                                            <img src="{{ getVendorSetting('logo') ? asset(getVendorSetting('logo')) : asset('img/logo/logo.png') }}"
                                                height="50" alt="{{ app_name() }}">
                                        </div>
                                        <div class="logo-dark">
                                            <img src="{{ getVendorSetting('dark_logo') ? asset(getVendorSetting('dark_logo')) : asset('img/logo/dark_logo.png') }}"
                                                height="50" alt="{{ app_name() }}">
                                        </div>
                                    </div>
                                </a>
                                <h5 class="mb-1 register-title">{{ __('vendorwebsite.welcome_back') }}</h5>
                                {{-- <p class="font-size-14 mb-5">{{ __('vendorwebsite.you_have_been_missed_for_long_time') }} --}}
                                </p>

                            </div>

                            @if (isset($errors) && $errors->any())
                                <div class="alert alert-danger">
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                            <form id="login-form" method="POST" action="{{ route('vendor.login', ['vendor_slug' => request()->route('vendor_slug')]) }}" class="needs-validation"
                                novalidate>
                                @csrf
                                <div id="login_error_message" class="alert alert-danger d-none mb-3"></div>
                                <input type="hidden" name="branch_id" id="branch_id" value="">
                                <div class="row gy-4">
                                    <div class="col-lg-12">
                                        <div class="form-group">
                                            <label for="email"
                                                class="form-label fw-medium">{{ __('vendorwebsite.email') }}<span
                                                    class="text-danger">*</span></label>
                                            <div class="input-group custom-input-group">
                                                <input type="email" name="email" value="olivia.davis@gmail.com"
                                                    id="email" class="form-control" placeholder="demo@gmail.com"
                                                    required />
                                                <span class="input-group-text"><i class="ph ph-envelope-simple"></i></span>
                                            </div>
                                            <div class="invalid-feedback">
                                                {{ __('vendorwebsite.please_enter_a_valid_email_address') }}</div>
                                        </div>
                                    </div>
                                    <div class="col-lg-12">
                                        <div class="form-group">
                                            <label for="password"
                                                class="form-label fw-medium">{{ __('vendorwebsite.password') }}<span
                                                    class="text-danger">*</span></label>
                                            <div class="input-group custom-input-group">
                                                <input type="password" value="12345678" name="password" id="password"
                                                    class="form-control" placeholder="eg #123@Abc" required />
                                                <span class="input-group-text" id="togglePassword">
                                                    <i class="ph ph-eye-slash" id="toggleIcon"></i>
                                                </span>
                                            </div>
                                            <div class="invalid-feedback">
                                                {{ __('vendorwebsite.please_enter_your_password') }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between flex-wrap column-gap-3 row-gap-1 mt-2">
                                    <div>
                                        <label for="remember_me" class="d-inline-flex align-items-center gap-2">
                                            <input type="checkbox" class="form-check-input m-0" name="remember_me">
                                            <span class="font-size-14">{{ __('vendorwebsite.remember_me') }}</span>
                                        </label>
                                    </div>
                                    <a href="{{ route('password.request') }}"
                                        class="fw-semibold font-size-14 fst-italic">{{ __('vendorwebsite.forgot_password') }}</a>
                                </div>
                                <div class="d-flex justify-content-between gap-3 mt-5 auth-btn">
                                    <button type="submit" id="login-button"
                                        class="btn btn-secondary flex-grow-1">{{ __('vendorwebsite.sign_in') }}</button>
                                    {{-- <a class="btn px-3 bg-gray-800">
                                    <img src="{{asset('img/vendorwebsite/google-icon.png')}}" alt="icon" class="img-fluid">
                                </a> --}}
                                    {{-- @if (setting('is_google_login') == 1) --}}
                                        <a href="{{ route('auth.google') }}" id="google-login" class="btn px-3 google-btn"
                                            onclick="handleGoogleLogin(event)">
                                            <svg width="18" height="18" viewBox="0 0 18 18" fill="none"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <path fill-rule="evenodd" clip-rule="evenodd"
                                                    d="M3.87845 8.99871C3.87845 8.41412 3.97554 7.85367 4.14883 7.32799L1.11563 5.01172C0.524469 6.21199 0.191406 7.56444 0.191406 8.99871C0.191406 10.4318 0.52406 11.7834 1.1144 12.9828L4.14597 10.6621C3.97431 10.1389 3.87845 9.58044 3.87845 8.99871Z"
                                                    fill="#FBBC05" />
                                                <path fill-rule="evenodd" clip-rule="evenodd"
                                                    d="M9.20477 3.68181C10.4747 3.68181 11.6218 4.13181 12.5231 4.86818L15.145 2.25C13.5473 0.85909 11.4989 0 9.20477 0C5.64308 0 2.58202 2.03686 1.11621 5.01299L4.14942 7.32927C4.84832 5.20772 6.84055 3.68181 9.20477 3.68181Z"
                                                    fill="#EB4335" />
                                                <path fill-rule="evenodd" clip-rule="evenodd"
                                                    d="M9.20477 14.3174C6.84055 14.3174 4.84832 12.7915 4.14942 10.6699L1.11621 12.9858C2.58202 15.9623 5.64308 17.9992 9.20477 17.9992C11.4031 17.9992 13.5018 17.2186 15.077 15.7561L12.1978 13.5303C11.3854 14.0421 10.3625 14.3174 9.20477 14.3174Z"
                                                    fill="#34A853" />
                                                <path fill-rule="evenodd" clip-rule="evenodd"
                                                    d="M17.8082 9.0016C17.8082 8.46978 17.7262 7.89705 17.6033 7.36523H9.20508V10.8425H14.0392C13.7975 12.028 13.1396 12.9395 12.1981 13.5327L15.0773 15.7585C16.732 14.2228 17.8082 11.9352 17.8082 9.0016Z"
                                                    fill="#4285F4" />
                                            </svg>
                                        </a>
                                    {{-- @endif --}}
                                </div>
                                <div class="d-flex justify-content-center flex-wrap gap-1 mt-3">
                                    <span class="font-size-14 text-body">{{ __('vendorwebsite.not_a_member') }}</span>
                                    <a href="{{ route('signup') }}"
                                        class="text-primary font-size-14 fw-medium text-decoration-underline">{{ __('vendorwebsite.sign_up') }}</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>
    @if ($branches->count() > 1)
        <!-- Modal -->
        <div class="modal fade" id="selectBranchModal" data-bs-backdrop="static" data-bs-keyboard="false"
            tabindex="-1" aria-labelledby="selectBranchModal" aria-hidden="true">
            <div class="modal-dialog modal-dialog-scrollable modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5">{{ __('vendorwebsite.select_branch') }}</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 row-cols-xl-4 gy-4">
                            @foreach ($branches as $branch)
                                @php
                                    $timezone = getVendorsetting('default_time_zone') ?? 'UTC'; // Change this to your local timezone if needed
                                    $today = \Carbon\Carbon::now($timezone)->format('l');
                                    $now = \Carbon\Carbon::now($timezone);
                                    $hours = \Modules\BussinessHour\Models\BussinessHour::where(
                                        'branch_id',
                                        $branch->id,
                                    )
                                        ->whereRaw('LOWER(day) = ?', [strtolower($today)])
                                        ->first();
                                    $isOpen = false;
                                    $debug = [];
                                    if ($hours && $hours->is_holiday != 1 && $hours->start_time && $hours->end_time) {
                                        $start = \Carbon\Carbon::parse($hours->start_time, $timezone);
                                        $end = \Carbon\Carbon::parse($hours->end_time, $timezone);
                                        $isOpen = $now->between($start, $end);
                                        // Check breaks
                                        if ($isOpen && !empty($hours->breaks)) {
                                            $breaks = is_array($hours->breaks)
                                                ? $hours->breaks
                                                : json_decode($hours->breaks, true);
                                            foreach ($breaks as $break) {
                                                if (!empty($break['start']) && !empty($break['end'])) {
                                                    $breakStart = \Carbon\Carbon::parse($break['start'], $timezone);
                                                    $breakEnd = \Carbon\Carbon::parse($break['end'], $timezone);
                                                    if ($now->between($breakStart, $breakEnd)) {
                                                        $isOpen = false;
                                                        break;
                                                    }
                                                }
                                            }
                                        }
                                    }
                                    $debug = [
                                        'now' => $now,
                                        'start' => isset($start) ? $start : null,
                                        'end' => isset($end) ? $end : null,
                                        'is_holiday' => $hours->is_holiday ?? null,
                                        'breaks' => $hours->breaks ?? null,
                                        'isOpen' => $isOpen,
                                    ];
                                @endphp

                                <div class="col">
                                    <div class="branch-card rounded position-relative overflow-hidden{{ $loop->first ? ' selected' : '' }}"
                                        data-branch-id="{{ $branch->id }}">
                                        <div class="branch-image position-relative">
                                            <span
                                                class="badge {{ $isOpen ? 'bg-success' : 'bg-danger' }} text-white font-size-14 text-uppercase position-absolute top-0 end-0">
                                                {{ $isOpen ? __('vendorwebsite.open') : __('vendorwebsite.closed') }}
                                            </span>
                                            @php
                                                $branchImage = $branch->media->pluck('original_url')->first();
                                            @endphp
                                            <img src="{{ $branchImage ? $branchImage : asset('img/vendorwebsite/branch-image.jpg') }}"
                                                class="card-img-top" alt="{{ $branch->name }}">
                                        </div>
                                        <div class="branch-info-box">
                                            <div class="d-flex flex-wrap align-items-center gap-1 mb-2">
                                                <h5 class="mb-0 fw-medium line-count-1"> <a
                                                        href="{{ route('branch-detail', $branch->id) }}">{{ $branch->name }}
                                                    </a></h5>
                                                <span
                                                    class="badge bg-purple text-body border rounded-pill text-uppercase">{{ $branch->type }}</span>
                                            </div>
                                            <span class="d-flex gap-2">
                                                <i class="ph ph-map-pin align-middle"></i>
                                                <span class="font-size-14">
                                                    @if ($branch->address)
                                                        {{ $branch->address->address_line_1 }}
                                                        {{ $branch->address->address_line_2 }}
                                                    @else
                                                        N/A
                                                    @endif
                                                </span>
                                            </span>
                                        </div>
                                        <span class="select-icon">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24" fill="none">
                                                <rect width="24" height="24" rx="12" fill="currentColor">
                                                </rect>
                                                <g>
                                                    <path d="M7.375 12.75L10 15.375L16 9.375" stroke="white"
                                                        stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                                    </path>
                                                </g>
                                                <defs>
                                                    <clipPath>
                                                        <rect width="12" height="12" fill="white"
                                                            transform="translate(5.5 6)"></rect>
                                                    </clipPath>
                                                </defs>
                                            </svg>
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary"
                            id="select-branch-btn">{{ __('vendorwebsite.next') }}</button>
                    </div>
                </div>
            </div>
    @endif
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- <script src="{{ asset('js/auth.min.js') }}" defer></script> -->
    <script>
        let currentAuthType = 'regular'; // 'regular' or 'google'
        let googleAuthUrl = "{{ route('auth.google') }}";

        function handleGoogleLogin(event) {
            event.preventDefault();
            currentAuthType = 'google';
            var branchModal = new bootstrap.Modal(document.getElementById('selectBranchModal'));
            branchModal.show();
        }

        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.branch-card').forEach(function(card) {
                card.addEventListener('click', function() {
                    document.querySelectorAll('.branch-card').forEach(function(c) {
                        c.classList.remove('selected');
                    });
                    this.classList.add('selected');
                });
            });
        });
        document.addEventListener('DOMContentLoaded', function() {
            const passwordInput = document.getElementById('password');
            const toggleSpan = document.getElementById('togglePassword');
            const toggleIcon = document.getElementById('toggleIcon');

            if (passwordInput && toggleSpan && toggleIcon) {
                toggleSpan.addEventListener('click', function() {
                    const isHidden = passwordInput.type === 'password';
                    passwordInput.type = isHidden ? 'text' : 'password';
                    toggleIcon.classList.toggle('ph-eye-slash', !isHidden);
                    toggleIcon.classList.toggle('ph-eye', isHidden);
                });
            }
        });
        // LOGIN FORM AJAX SUBMIT LOGIC
        document.addEventListener('DOMContentLoaded', function() {
            const loginForm = document.getElementById('login-form');
            const loginError = document.getElementById('login_error_message');
            const loginButton = document.getElementById('login-button');
            if (loginForm) {
                loginForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    loginError.classList.add('d-none');
                    loginError.textContent = '';
                    loginButton.disabled = true;
                    loginButton.textContent = '{{ __('vendorwebsite.signing_in') }}...';
                    const formData = new FormData(loginForm);
                    fetch("{{ route('vendor.login', ['vendor_slug' => request()->route('vendor_slug')]) }}", {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                    .getAttribute('content'),
                                'Accept': 'application/json'
                            },
                            body: formData
                        })
                        .then(async res => {
                            let data;
                            try {
                                data = await res.json();
                            } catch (e) {
                                data = {};
                            }

                            if (res.ok && data && data.success) {

                                // Credentials are correct, show branch modal immediately
                                currentAuthType = 'regular';

                                // Get fresh CSRF token after login
                                fetch("{{ route('csrf.token') }}", {
                                        method: 'GET',
                                        credentials: 'same-origin'
                                    })
                                    .then(response => response.json())
                                    .then(data => {
                                        // Update CSRF token
                                        document.querySelector('meta[name="csrf-token"]')
                                            .setAttribute('content', data.token);
                                    })
                                    .catch(err => {

                                    });

                                var modalElement = document.getElementById('selectBranchModal');

                                if (data.user_branch_id != null) {

                                    window.location.href = "{{ route('vendor.index') }}";
                                }
                                if (modalElement && data.user_branch_id == null) {
                                    var branchModal = new bootstrap.Modal(modalElement);
                                    branchModal.show();
                                } else {

                                    window.location.href = "{{ route('vendor.index') }}";
                                }
                            } else {

                                // Credentials are wrong, show error
                                let msg = (data && data.message) ? data.message :
                                    'These credentials do not match our records.';
                                loginError.textContent = msg;
                                loginError.classList.remove('d-none');
                            }
                        })
                        .catch(err => {
                            loginError.textContent = 'An error occurred. Please try again.';
                            loginError.classList.remove('d-none');
                        })
                        .finally(() => {

                            loginButton.disabled = false;
                            loginButton.textContent = '{{ __('vendorwebsite.sign_in') }}';
                        });
                });
            }
            // Branch selection and form submit after successful login
            document.body.addEventListener('click', function(e) {
                if (e.target && e.target.id === 'select-branch-btn') {
                    const selectedBranch = document.querySelector('.branch-card.selected');
                    if (selectedBranch) {
                        document.getElementById('branch_id').value = selectedBranch.getAttribute(
                            'data-branch-id');
                        const branchId = selectedBranch.getAttribute('data-branch-id');
                        // Store branch address before submitting login form

                        fetch("{{ route('branch.select') }}", {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                        .getAttribute('content'),
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json'
                                },
                                credentials: 'same-origin',
                                body: JSON.stringify({
                                    branch_id: branchId
                                })
                            })
                            .then(res => {

                                return res.json();
                            })
                            .then(data => {

                                // Redirect to home page after branch selection
                                if (currentAuthType === 'google') {
                                    const googleUrl = new URL(googleAuthUrl);
                                    googleUrl.searchParams.set('branch_id', branchId);
                                    window.location.href = googleUrl.toString();
                                } else {
                                    // For regular login, redirect to home
                                    window.location.href = "{{ route('vendor.index') }}";
                                }
                            })
                            .catch(err => {
                                console.error('Branch selection error:', err);
                                alert('Failed to store branch address. Please try again.');
                            });
                    } else {
                        alert('Please select a branch before proceeding.');
                    }
                }
            });
        });
        document.addEventListener('DOMContentLoaded', function() {
            // Prefill email and password if stored
            if (localStorage.getItem('rememberMeChecked') === 'true') {
                document.getElementById('email').value = localStorage.getItem('rememberMeEmail') || '';
                document.getElementById('password').value = localStorage.getItem('rememberMePassword') || '';
                document.querySelector('input[name="remember_me"]').checked = true;
            }
            // On form submit, store or clear values
            document.getElementById('login-form').addEventListener('submit', function() {
                const rememberMe = document.querySelector('input[name="remember_me"]').checked;
                if (rememberMe) {
                    localStorage.setItem('rememberMeChecked', 'true');
                    localStorage.setItem('rememberMeEmail', document.getElementById('email').value);
                    localStorage.setItem('rememberMePassword', document.getElementById('password').value);
                } else {
                    localStorage.removeItem('rememberMeChecked');
                    localStorage.removeItem('rememberMeEmail');
                    localStorage.removeItem('rememberMePassword');
                }
            });
        });
    </script>

@endsection

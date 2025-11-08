<form id="payout-form" method="POST" action="{{ route('backend.earnings.store') }}">
    @csrf

    <div class="offcanvas offcanvas-end" tabindex="-1" id="form-offcanvas" aria-labelledby="form-offcanvasLabel">

        {{-- Header --}}
        <div class="offcanvas-header border-bottom">
            <h5 class="offcanvas-title" id="form-offcanvasLabel">
                {{ __('earning.pay_out_to') }}
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>

        <div class="offcanvas-body">

            {{-- User info --}}
            <div class="border-bottom">
                <div class="d-flex align-items-center gap-3 mb-2">
                    <img src="{{ asset('images/default-avatar.png') }}" alt="avatar" class="img-fluid avatar avatar-60 rounded-pill" />
                    <div class="flex-grow-1">
                        <div class="gap-2">
                            <strong id="user_full_name"></strong>
                            <p class="m-0"><small id="user_email"></small></p>
                            <p class="m-0"><small id="user_mobile"></small></p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Form fields --}}
            <div class="row">

                {{-- Select Payment Method --}}
                <div class="col-12 py-2">
                    <div class="form-group">
                        <label class="form-label">{{ __('earning.lbl_select_method') }} <span class="text-danger">*</span></label>
                        <select id="payment_method" name="payment_method" class="form-select select2">
                            <option value="">{{ __('earning.select_method') }}</option>
                            {{-- options will be loaded dynamically --}}
                        </select>
                        <span class="text-danger payment_method_error"></span>
                    </div>
                </div>

                {{-- Description --}}
                <div class="col-12">
                    <div class="form-group">
                        <label class="form-label" for="description">{{ __('earning.lbl_description') }} <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="description" name="description" placeholder="{{ __('earning.enter_decription') }}">{{ old('description') }}</textarea>
                        <span class="text-danger description_error"></span>
                    </div>
                </div>

                {{-- Summary --}}
                <div class="col-12 py-1">
                    <div class="d-flex justify-content-between align-items-center">
                        <span>{{ __('earning.commission_earn') }}</span>
                        <strong id="commission_earn">0</strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span>{{ __('earning.tip_earn') }}</span>
                        <strong id="tip_earn">0</strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center border-top py-3 mt-3">
                        <span class="flex-grow-1">{{ __('earning.total_pay') }}</span>
                        <h6><strong id="amount">0</strong></h6>
                    </div>
                </div>

            </div>
        </div>

        {{-- Footer --}}
        <div class="offcanvas-footer border-top p-3 d-flex justify-content-end gap-2">
            <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-floppy-disk"></i> {{ __('messages.save') }}
            </button>
            <button type="button" class="btn btn-outline-primary d-block" data-bs-dismiss="offcanvas">
                <i class="fa-solid fa-angles-left"></i> {{ __('messages.close') }}
            </button>
        </div>
    </div>
</form>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('payout-form');

    // Initialize Select2
    const $paymentSelect = $('#payment_method');
    $paymentSelect.select2({
        width: '100%',
        placeholder: "Select an option",
        allowClear: true
    });

    // Fetch payment methods and populate select
    fetch('{{ url("app/earnings/get_search_data?type=earning_payment_method") }}', {
        headers: { 'Accept': 'application/json' }
    })
    .then(res => res.json())
    .then(data => {
        if (data.results) {
            data.results.forEach(method => {
                const option = new Option(method.text.charAt(0).toUpperCase() + method.text.slice(1), method.id, false, false);
                $paymentSelect.append(option);
            });
            $paymentSelect.trigger('change');
        }
    })
    .catch(err => console.error('Error fetching payment methods:', err));

    // Helper: show error in <span class="text-danger ..._error">
    function showError(field, message) {
        const span = form.querySelector('.' + field + '_error');
        if (span) span.textContent = message;
        const input = form.querySelector('#' + field);
        if (input) input.classList.add('is-invalid');
    }

    // Clear all errors
    function clearErrors() {
        form.querySelectorAll('.text-danger').forEach(span => span.textContent = '');
        form.querySelectorAll('.is-invalid').forEach(input => input.classList.remove('is-invalid'));
    }

    // New payout button
    const newBtn = document.getElementById('newPayoutBtn');
    if (newBtn) {
        newBtn.addEventListener('click', function () {
            form.reset();
            clearErrors();
            $paymentSelect.val(null).trigger('change');
            form.querySelector('#user_full_name').textContent = '';
            form.querySelector('#user_email').textContent = '';
            form.querySelector('#user_mobile').textContent = '';
            form.querySelector('#commission_earn').textContent = 0;
            form.querySelector('#tip_earn').textContent = 0;
            form.querySelector('#amount').textContent = 0;
            form.action = "{{ route('backend.earnings.store') }}";
            form.querySelector('[name="_method"]')?.remove();
        });
    }

    // Submit form
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        clearErrors();

        let valid = true;
        const payment_method = form.querySelector('#payment_method').value.trim();
        const description = form.querySelector('#description').value.trim();

        if (!payment_method) { showError('payment_method', 'Payment method is required'); valid = false; }
        if (!description) { showError('description', 'Description is required'); valid = false; }

        if (!valid) return;

        const formData = new FormData(form);
        fetch(form.action, {
            method: form.method,
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            clearErrors();
            if (data.status) {
                window.successSnackbar(data.message);
                bootstrap.Offcanvas.getInstance(document.getElementById('form-offcanvas')).hide();
                if (typeof renderedDataTable !== 'undefined') renderedDataTable.ajax.reload(null, false);
            } else {
                window.errorSnackbar(data.message);
                for (const field in data.all_message) {
                    showError(field, data.all_message[field][0]);
                }
            }
        })
        .catch(err => console.error(err));
    });

    // Edit payout buttons
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('[data-payout-id]');
        if (!btn) return;
        const payoutId = btn.getAttribute('data-payout-id');
        if (!payoutId) return;

        clearErrors();
        form.reset();
        $paymentSelect.val(null).trigger('change');

        fetch(`{{ url('app/earnings') }}/${payoutId}/edit`, { headers: { 'Accept': 'application/json' } })
        .then(res => res.json())
        .then(data => {
            if (data.status) {
                form.querySelector('#user_full_name').textContent = data.data.full_name || '';
                form.querySelector('#user_email').textContent = data.data.email || '';
                form.querySelector('#user_mobile').textContent = data.data.mobile || '';
                form.querySelector('img').src = data.data.profile_image || '{{ asset("images/default-avatar.png") }}';
                form.querySelector('#description').value = data.data.description || '';
                $paymentSelect.val(data.data.payment_method).trigger('change');
                form.querySelector('#commission_earn').textContent = data.data.commission_earn || 0;
                form.querySelector('#tip_earn').textContent = data.data.tip_earn || 0;
                form.querySelector('#amount').textContent = data.data.amount || 0;

                form.action = `{{ url('app/earnings') }}/${payoutId}`;
                form.querySelector('[name="_method"]')?.remove();
                const methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                methodInput.value = 'PUT';
                form.appendChild(methodInput);

                new bootstrap.Offcanvas(document.getElementById('form-offcanvas')).show();
            }
        })
        .catch(err => console.error(err));
    });

    // Reset form when offcanvas closes
    document.getElementById('form-offcanvas').addEventListener('hidden.bs.offcanvas', function () {
        form.reset();
        clearErrors();
        $paymentSelect.val(null).trigger('change');
        form.querySelector('#user_full_name').textContent = '';
        form.querySelector('#user_email').textContent = '';
        form.querySelector('#user_mobile').textContent = '';
        form.querySelector('#commission_earn').textContent = 0;
        form.querySelector('#tip_earn').textContent = 0;
        form.querySelector('#amount').textContent = 0;
        form.action = "{{ route('backend.earnings.store') }}";
        form.querySelector('[name="_method"]')?.remove();
    });
});
</script>

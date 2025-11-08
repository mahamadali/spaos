<!-- Assign Branch Form Offcanvas -->
<form id="assign-branch-form" method="POST" action="javascript:void(0);">
    @csrf
    <div class="offcanvas offcanvas-end" tabindex="-1" id="service-branch-assign-form" aria-labelledby="form-offcanvasLabel">
        <div class="offcanvas-header border-bottom">
            <h6 class="m-0 h5">
                {{ __('service.singular_title') }} : <span id="service-name"></span>
            </h6>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>

        <div class="offcanvas-body">
            <div class="form-group">
                <div class="d-grid">
                    <div class="d-flex flex-column">
                        <div class="form-group">
                            <label for="branches_ids">{{ __('branch.select_branch') }}</label>
                            <select class="form-control select2" name="branches_ids[]" id="branches_ids" multiple>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" data-name="{{ $branch->name }}">
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="list-group list-group-flush mt-3" id="selected-branches-list">
                        <!-- Selected branches will be dynamically added here -->
                    </div>
                </div>
            </div>
        </div>

        <div class="offcanvas-footer">
            <div class="d-grid d-md-flex gap-3 p-3">
                <button type="button" class="btn btn-primary d-block" id="submit-assign-btn">
                    <i class="fa-solid fa-floppy-disk"></i>
                    {{ __('messages.update') }}
                </button>
                <button type="button" class="btn btn-outline-primary d-block" data-bs-dismiss="offcanvas">
                    <i class="fa-solid fa-angles-left"></i>
                    {{ __('messages.close') }}
                </button>
            </div>
        </div>
    </div>
</form>

<script>
let selectedBranches = [];
let currentService = null;
const CURRENCY_SYMBOL = '{{ config("app.currency_symbol", "$") }}';

// Initialize the assign branch form
function initAssignBranchForm() {
    const branchesSelect = document.getElementById('branches_ids');
    const selectedBranchesList = document.getElementById('selected-branches-list');

    // Initialize Select2 for branches with duplicate prevention
    if ($(branchesSelect).hasClass('select2-hidden-accessible')) {
        $(branchesSelect).select2('destroy');
    }
    
    // Remove any existing Select2 containers
    $(branchesSelect).siblings('.select2-container').remove();
    
    // Initialize Select2
    $(branchesSelect).select2({
        placeholder: '{{ __("branch.select_branch") }}',
        allowClear: true,
        width: '100%'
    });
    
    // Hide original select to prevent double display
    $(branchesSelect).css({ display: 'none' });

    // Handle branch selection
    $(branchesSelect).on('change', function() {
        const selectedValues = $(this).val();
        const selectedOptions = $(this).find('option:selected');

        // Clear the list
        selectedBranchesList.innerHTML = '';
        selectedBranches = [];

        // Add selected branches to the list
        selectedOptions.each(function(index) {
            const branchId = $(this).val();
            const branchName = $(this).data('name');

            // Get default values from current service
            const defaultPrice = currentService ? currentService.default_price : '';
            const defaultDuration = currentService ? currentService.duration_min : '';

            const branchData = {
                branch_id: branchId,
                name: branchName,
                service_id: currentService ? currentService.id : null,
                service_price: defaultPrice,
                duration_min: defaultDuration
            };

            selectedBranches.push(branchData);
            addBranchToList(branchData, index + 1);
        });

        // Log for debugging
        console.log('Current service data:', currentService);
        console.log('Selected branches:', selectedBranches);
    });
}

// Add branch to the list with input fields
function addBranchToList(branch, index) {
    const selectedBranchesList = document.getElementById('selected-branches-list');

    const branchItem = document.createElement('div');
    branchItem.className = 'list-group-item';
    branchItem.innerHTML = `
        <div class="d-flex justify-between align-items-center flex-grow-1 gap-2 mt-2">
            <span>${index} - </span>
            <div class="flex-grow-1">${branch.name}</div>
            <button type="button" onclick="removeBranch('${branch.branch_id}')" class="btn btn-sm text-danger">
                <i class="fa-regular fa-trash-can"></i>
            </button>
        </div>
        <div class="row mb-2">
            <div class="d-flex justify-content-end align-items-center gap-2 col-6">
                <i class="fa-regular fa-clock"></i>
                <input type="number" name="branches[${branch.branch_id}][duration_min]"
                       value="${branch.duration_min || ''}" class="form-control"
                       placeholder="{{ __('service.lbl_duration_min') }}" required />
            </div>
            <div class="d-flex justify-content-end align-items-center gap-2 col-6">
                ${CURRENCY_SYMBOL}
                <input type="text" name="branches[${branch.branch_id}][service_price]"
                       value="${branch.service_price || ''}" class="form-control"
                       placeholder="{{ __('service.lbl_default_price') }}" required />
            </div>
        </div>
    `;

    selectedBranchesList.appendChild(branchItem);
}

// Remove branch from the list
function removeBranch(branchId) {
    selectedBranches = selectedBranches.filter(branch => branch.branch_id !== branchId);

    // Update the select2
    const branchesSelect = document.getElementById('branches_ids');
    const currentValues = $(branchesSelect).val() || [];
    const newValues = currentValues.filter(id => id !== branchId);
    $(branchesSelect).val(newValues).trigger('change');
}

// Load service data and existing branch assignments
function loadServiceData(serviceId) {
    if (!serviceId) return;

    // Fetch service details
    fetch(`{{ route('backend.services.get_service_data', ':id') }}`.replace(':id', serviceId))
        .then(response => response.json())
        .then(data => {
            if (data.status && data.data) {
                currentService = data.data;
                document.getElementById('service-name').textContent = currentService.name;

                // Fetch existing branch assignments
                fetch(`{{ route('backend.services.assign_branch_list', ':id') }}`.replace(':id', serviceId))
                    .then(response => response.json())
                    .then(assignData => {
                        if (assignData.status && assignData.data) {
                            selectedBranches = assignData.data;

                            // Update the select2 with existing assignments
                            const branchesSelect = document.getElementById('branches_ids');
                            const branchIds = selectedBranches.map(branch => branch.branch_id);
                            $(branchesSelect).val(branchIds).trigger('change');

                            // Refresh the branch list with current service data
                            refreshBranchList();
                        } else {
                            // No existing branches, clear the list
                            selectedBranches = [];
                            const selectedBranchesList = document.getElementById('selected-branches-list');
                            selectedBranchesList.innerHTML = '';
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching branch assignments:', error);
                    });
            }
        })
        .catch(error => {
            console.error('Error fetching service data:', error);
        });
}

// Refresh branch list with current service data
function refreshBranchList() {
    if (!currentService) return;

    const selectedBranchesList = document.getElementById('selected-branches-list');
    selectedBranchesList.innerHTML = '';

    selectedBranches.forEach((branch, index) => {
        // Update branch data with current service defaults if not already set
        if (!branch.service_price && currentService.default_price) {
            branch.service_price = currentService.default_price;
        }
        if (!branch.duration_min && currentService.duration_min) {
            branch.duration_min = currentService.duration_min;
        }
        addBranchToList(branch, index + 1);
    });
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initAssignBranchForm();
    
    // Prevent any form submission
    const form = document.getElementById('assign-branch-form');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            return false;
        }, true);
    }

    // Handle submit button click
    const submitBtn = document.getElementById('submit-assign-btn');
    if (submitBtn) {
        submitBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            
            const submitButton = this;
            const originalText = submitButton.innerHTML;
            const formElement = document.getElementById('assign-branch-form');
            
            if (!formElement) {
                console.error('Form element not found');
                return false;
            }
            
            // Get the action URL from form attribute (set dynamically in openAssignBranchOffcanvas)
            let actionUrl = formElement.getAttribute('action') || formElement.action;
            
            // If action is javascript:void(0) or empty, try to get it from currentService
            if (!actionUrl || actionUrl === 'javascript:void(0);' || actionUrl === '' || actionUrl === 'javascript:void(0)') {
                if (currentService && currentService.id) {
                    actionUrl = `{{ route('backend.services.assign_branch_update', ':id') }}`.replace(':id', currentService.id);
                } else {
                    console.error('Action URL not found. Cannot submit form.');
                    alert('Service ID not found. Please refresh and try again.');
                    submitButton.disabled = false;
                    submitButton.innerHTML = '<i class="fa-solid fa-floppy-disk"></i> {{ __("messages.update") }}';
                    return false;
                }
            }
            
            // If still not valid, return
            if (!actionUrl || actionUrl === 'javascript:void(0);' || actionUrl === 'javascript:void(0)') {
                console.error('Invalid action URL:', actionUrl);
                alert('Invalid form action. Please refresh and try again.');
                submitButton.disabled = false;
                submitButton.innerHTML = '<i class="fa-solid fa-floppy-disk"></i> {{ __("messages.update") }}';
                return false;
            }

            // Show loading state
            submitButton.disabled = true;
            const originalHTML = submitButton.innerHTML;
            submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> {{ __("messages.updating") }}';

            // Prepare data in the format expected by the controller
            const branches = [];
            selectedBranches.forEach(branch => {
                const durationInput = document.querySelector(`input[name="branches[${branch.branch_id}][duration_min]"]`);
                const priceInput = document.querySelector(`input[name="branches[${branch.branch_id}][service_price]"]`);

                if (durationInput && priceInput) {
                    branches.push({
                        branch_id: branch.branch_id,
                        service_price: priceInput.value,
                        duration_min: durationInput.value
                    });
                }
            });

            // Alert if no branches selected
            if (!branches.length) {
                alert('Please select at least one branch.');
                submitButton.disabled = false;
                submitButton.innerHTML = '<i class="fa-solid fa-floppy-disk"></i> {{ __("messages.update") }}';
                return false;
            }

            const formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            formData.append('branches', JSON.stringify(branches));

            // Use fetch API with proper error handling to prevent navigation
            fetch(actionUrl, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                credentials: 'same-origin'
            })
            .then(response => {
                // Always redirect immediately - don't wait for response processing
                // Close the offcanvas first
                const offcanvasInstance = bootstrap.Offcanvas.getInstance(document.getElementById('service-branch-assign-form'));
                if (offcanvasInstance) {
                    offcanvasInstance.hide();
                }
                
                // Redirect immediately without waiting for response parsing
                window.location.href = "{{ route('backend.services.index') }}";
                
                // Process response in background (optional - for error handling if redirect fails)
                return response.json().catch(() => ({}));
            })
            .then(data => {
                // This runs after redirect, so ignore it
                // Only here for cleanup in case redirect somehow fails
                if (data && !data.status) {
                    console.error('Update failed:', data.message);
                }
            })
            .catch(error => {
                // Even on error, redirect to prevent showing JSON
                console.error('Error updating branches:', error);
                window.location.href = "{{ route('backend.services.index') }}";
            });
            
            return false;
        });
    }
});

// Function to open assign branch offcanvas (called from datatable)
function openAssignBranchOffcanvas(serviceId) {
    // Reset form
    document.getElementById('assign-branch-form').reset();
    document.getElementById('selected-branches-list').innerHTML = '';
    selectedBranches = [];
    currentService = null;

    // Update form action
    document.getElementById('assign-branch-form').action = `{{ route('backend.services.assign_branch_update', ':id') }}`.replace(':id', serviceId);

    // Open offcanvas first
    const offcanvas = new bootstrap.Offcanvas(document.getElementById('service-branch-assign-form'));
    offcanvas.show();

    // Load service data after offcanvas is shown
    loadServiceData(serviceId);
}

// Handle assign branch button clicks from datatable
document.addEventListener('click', function(e) {
    if (e.target.closest('[data-assign-event="branch_assign"]')) {
        const button = e.target.closest('[data-assign-event="branch_assign"]');
        const serviceId = button.getAttribute('data-assign-module');
        openAssignBranchOffcanvas(serviceId);
    }
});
</script>

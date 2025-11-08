<form id="staff-assign-form" onsubmit="return false;">
  <div class="offcanvas offcanvas-end" tabindex="-1" id="staff-assign-offcanvas" aria-labelledby="form-offcanvasLabel" data-bs-backdrop="true" data-bs-scroll="true">
    <div class="offcanvas-header">
      <h4 id="branch-assign-name"></h4>
      <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
      <div class="d-flex flex-column">
        <div class="mb-4">
          <select id="employees_ids" class="form-control select2" multiple data-placeholder="{{ __('branch.select_staff') }}" style="width: 100%;">
          </select>
        </div>
        <div id="selected-employees-list">
          <!-- Selected employees will be displayed here -->
        </div>
      </div>
    </div>
    <div class="offcanvas-footer">
      <div class="d-grid d-md-flex gap-3 p-3">
        <button class="btn btn-primary d-block" id="update-assign-btn">
          <i class="fa-solid fa-floppy-disk"></i>
          {{ __('messages.update') }}
        </button>
        <button class="btn btn-outline-primary d-block" type="button" data-bs-dismiss="offcanvas">
          <i class="fa-solid fa-angles-left"></i>
          {{ __('messages.close') }}
        </button>
      </div>
    </div>
  </div>
</form>

@push('after-scripts')
<script>
(function(){
  const ADMIN = '{{ url('app') }}';
  const api = {
    get: (id) => `${ADMIN}/branch/assign/${id}`,
    post: (id) => `${ADMIN}/branch/assign/${id}`,
    employees: `${ADMIN}/employees/employee_list`
  };

  let els = {};
  let branchId = null;
  let selectedEmployees = [];
  let allEmployees = [];
  let initialAssignIds = [];
  let select2Initialized = false;

  function initializeElements() {
    els = {
      select: document.getElementById('employees_ids'),
      employeesList: document.getElementById('selected-employees-list'),
      branchName: document.getElementById('branch-assign-name'),
      updateBtn: document.getElementById('update-assign-btn'),
      offcanvas: document.getElementById('staff-assign-offcanvas')
    };
  }

  function loadEmployees() {
    fetch(api.employees, {
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(res => res.json())
    .then(data => {
      allEmployees = data || [];
      populateSelect();
    })
    .catch(error => {
      console.error('Error loading employees:', error);
      fetch(`${ADMIN}/employees`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      })
      .then(res => res.json())
      .then(data => {
        allEmployees = data || [];
        populateSelect();
      })
      .catch(err => {
        console.error('Fallback also failed:', err);
      });
    });
  }

  function populateSelect() {
    if (!els.select) return;
    
    // Destroy existing Select2 instance if any to prevent duplicates
    if (window.$ && $.fn.select2 && $(els.select).hasClass('select2-hidden-accessible')) {
      try {
        $(els.select).select2('destroy');
        select2Initialized = false;
      } catch (e) {
        // Ignore errors if not initialized
      }
    }
    
    // Remove all duplicate Select2 containers
    const $existingContainers = $(els.select).siblings('.select2-container');
    if ($existingContainers.length > 0) {
      $existingContainers.remove();
    }
    
    els.select.innerHTML = '';
    
    // Don't add placeholder option - Select2 will handle placeholder via data-placeholder attribute
    
    const availableIds = [];
    allEmployees.forEach(emp => {
      const empId = emp.id || emp.employee_id;
      const empName = emp.name || emp.full_name || emp.employee_name || 'Unknown';
      
      const option = document.createElement('option');
      option.value = empId;
      option.textContent = empName;
      els.select.appendChild(option);
      
      availableIds.push(empId);
    });
    
    if (initialAssignIds.length > 0) {
      initialAssignIds.forEach(assignedId => {
        const assignedIdStr = assignedId.toString();
        if (!availableIds.some(id => id.toString() === assignedIdStr)) {
          const assignedEmp = selectedEmployees.find(emp => (emp.employee_id || emp.id) == assignedId);
          if (assignedEmp) {
            const empName = assignedEmp.name || assignedEmp.full_name || assignedEmp.employee_name || 'Unknown';
            
            const option = document.createElement('option');
            option.value = assignedId;
            option.textContent = empName;
            els.select.appendChild(option);
            
            availableIds.push(assignedIdStr);
          }
        }
      });
    }

    // Initialize Select2 only once
    if (window.$ && $.fn.select2 && !select2Initialized) {
      try {
        // Remove any remaining duplicate containers
        const $containers = $(els.select).siblings('.select2-container');
        $containers.remove();
        
        // Initialize Select2
        $(els.select).select2({
          placeholder: "{{ __('branch.select_staff') }}",
          allowClear: true,
          width: '100%',
          dropdownParent: $(els.select).closest('.offcanvas-body') || $('body'),
          templateResult: function(data) {
            // Don't show placeholder as a selectable option
            if (!data.id || data.id === '') {
              return null;
            }
            return data.text;
          },
          templateSelection: function(data) {
            // Don't show placeholder in selected items
            if (!data.id || data.id === '') {
              return null;
            }
            return data.text;
          }
        });
        
        $(els.select).css({ 
          position: 'absolute', 
          width: '1px', 
          height: '1px', 
          opacity: 0, 
          pointerEvents: 'none' 
        });
        
        select2Initialized = true;
        updateSelect2Values();
      } catch (error) {
        console.error('Select2 initialization failed:', error);
        select2Initialized = false;
      }
    } else if (select2Initialized) {
      updateSelect2Values();
    }
  }

  function renderSelectedEmployees() {
    if (!els.employeesList) return;
    
    els.employeesList.innerHTML = '';
    
    if (selectedEmployees.length === 0) {
      els.employeesList.innerHTML = '<p class="text-muted text-center">No employees assigned</p>';
      return;
    }
    
    selectedEmployees.forEach(emp => {
      const item = document.createElement('div');
      item.className = 'list-group-item d-flex justify-content-between align-items-center mb-2 border-0 rounded';
      
      const empName = emp.name || emp.full_name || emp.employee_name || 'Unknown';
      const empAvatar = emp.avatar || emp.profile_image || '{{ default_user_avatar() }}';
      const empId = emp.employee_id || emp.id;
      
      item.innerHTML = `
        <div class="d-flex justify-between flex-grow-1 gap-2 my-2">
          <img src="${empAvatar}" class="avatar avatar-40 img-fluid rounded-pill" alt="user" onerror="this.src='{{ default_user_avatar() }}'" />
          <div class="flex-grow-1 mt-2">${empName}</div>
        </div>
        <button type="button" class="btn btn-sm text-danger remove-employee" data-id="${empId}">
          <i class="fa-regular fa-trash-can"></i>
        </button>
      `;
      els.employeesList.appendChild(item);
    });

    els.employeesList.querySelectorAll('.remove-employee').forEach(btn => {
      btn.addEventListener('click', function() {
        const empId = this.getAttribute('data-id');
        removeEmployee(empId);
      });
    });
  }

  function loadBranchAssignments(id) {
    if (!id) {
      console.error('❌ loadBranchAssignments called without ID');
      return;
    }
    
    branchId = id;
    console.log('✅ Branch ID set to:', branchId);
    selectedEmployees = [];
    initialAssignIds = [];
    
    // Fetch branch name immediately when modal opens
    fetchBranchName(id);
    
    if (els.branchName) els.branchName.textContent = '';
    
    fetch(api.get(id), {
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(res => res.json())
    .then(json => {
      if (json && json.status && json.data) {
        selectedEmployees = json.data;
        // Use branch_name from employee data if available (more reliable)
        if (selectedEmployees.length > 0 && selectedEmployees[0].branch_name) {
          const branchName = selectedEmployees[0].branch_name;
          if (els.branchName && branchName) {
            els.branchName.textContent = branchName;
          }
        }
        
        initialAssignIds = selectedEmployees.map(emp => emp.employee_id || emp.id);
        renderSelectedEmployees();
        populateSelect();
        updateSelect2Values();
      }
    })
    .catch(error => {
      console.error('Error loading branch assignments:', error);
    });
  }

  function updateSelect2Values() {
    if (window.$ && $.fn.select2 && select2Initialized && allEmployees.length > 0) {
      const availableIds = Array.from(els.select.options).map(opt => opt.value).filter(id => id !== '' && id !== null);
      const validIds = initialAssignIds.filter(id => id !== '' && id !== null && availableIds.includes(id.toString()));
      
      $(els.select).val(null).trigger('change');
      
      if (validIds.length > 0) {
        // Filter out any empty/null values before setting
        const cleanIds = validIds.filter(id => id !== '' && id !== null);
        if (cleanIds.length > 0) {
          $(els.select).val(cleanIds).trigger('change');
          setTimeout(() => {
            $(els.select).trigger('change.select2');
          }, 50);
        }
      }
    } else {
      setTimeout(() => {
        updateSelect2Values();
      }, 100);
    }
  }

  function fetchBranchName(branchId) {
    if (!branchId || !els.branchName) return;
    
    // Use the edit endpoint which returns proper JSON structure
    fetch(`${ADMIN}/branch/${branchId}/edit`, {
      headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    })
    .then(res => {
      if (!res.ok) {
        throw new Error(`HTTP ${res.status}: Failed to fetch branch`);
      }
      return res.json();
    })
    .then(response => {
      // The edit endpoint returns { status: true, data: { name: ... } }
      let branchName = null;
      if (response && response.status === true && response.data) {
        branchName = response.data.name;
      } else if (response && response.data && response.data.name) {
        branchName = response.data.name;
      } else if (response && response.name) {
        branchName = response.name;
      }
      
      if (branchName) {
        els.branchName.textContent = branchName;
        console.log('✅ Branch name loaded:', branchName);
      } else {
        els.branchName.textContent = 'Branch Not Found';
        console.warn('Branch name not found in response:', response);
      }
    })
    .catch(error => {
      console.error('Error fetching branch name:', error);
      if (els.branchName) {
        els.branchName.textContent = 'Error Loading Branch';
      }
    });
  }

  function removeEmployee(empId) {
    selectedEmployees = selectedEmployees.filter(emp => {
      const empIdToCompare = emp.employee_id || emp.id;
      return empIdToCompare.toString() !== empId.toString();
    });
    
    renderSelectedEmployees();
    
    // DON'T update initialAssignIds here - keep the original for comparison
    // initialAssignIds = selectedEmployees.map(emp => emp.employee_id || emp.id);
    
    if (window.$ && $.fn.select2 && select2Initialized) {
      const currentSelect2Values = $(els.select).val() || [];
      const updatedSelect2Values = currentSelect2Values.filter(id => id.toString() !== empId.toString());
      $(els.select).val(updatedSelect2Values).trigger('change');
    }
    
    if (window.successSnackbar) {
      window.successSnackbar('{{ __('messages.employee_removed') }}');
    }
  }

  function setupEventListeners() {
    if (els.select && window.$ && $.fn.select2) {
      // Remove existing event listeners to prevent duplicates
      $(els.select).off('change.select2-init');
      $(els.select).off('select2:unselect');
      
      $(els.select).on('change.select2-init', function() {
        const selectedIds = $(this).val() || [];
        // Filter out any empty/null values
        const cleanIds = selectedIds.filter(id => id !== '' && id !== null && id !== undefined);
        
        const newEmployees = allEmployees.filter(emp => {
          const empId = emp.id || emp.employee_id;
          return cleanIds.includes(empId.toString()) && 
                 !selectedEmployees.some(sel => (sel.employee_id || sel.id) == empId);
        });
        
        selectedEmployees = [...selectedEmployees, ...newEmployees];
        renderSelectedEmployees();
        
        // Remove empty values from Select2 if they somehow got selected
        if (cleanIds.length !== selectedIds.length) {
          $(this).val(cleanIds).trigger('change');
        }
      });
      
      // Handle Select2 tag removal (X button on tags)
      $(els.select).on('select2:unselect.select2-init', function(e) {
        const removedId = e.params.data.id;
        removeEmployee(removedId);
      });
    }

    if (els.updateBtn) {
      // Remove old listener before adding new one to prevent duplicates
      els.updateBtn.replaceWith(els.updateBtn.cloneNode(true));
      els.updateBtn = document.getElementById('update-assign-btn');
      
      els.updateBtn.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const currentIds = selectedEmployees.map(emp => emp.employee_id || emp.id);
        const hasUpdates = JSON.stringify(currentIds.sort()) !== JSON.stringify(initialAssignIds.sort());
        
        console.log('🔄 Update button clicked');
        console.log('📊 Current IDs:', currentIds);
        console.log('📊 Initial IDs:', initialAssignIds);
        console.log('📊 Has updates:', hasUpdates);
        
        if (hasUpdates) {
          // Check if Swal is available
          if (typeof Swal === 'undefined') {
            console.error('❌ Swal is not defined');
            // Fallback to native confirm
            if (confirm('{{ __('messages.do_you_want_make_sure') }}')) {
              updateAssignments();
            }
            return;
          }
          
          console.log('✅ Showing confirmation dialog');
          Swal.fire({
            title: '{{ __('messages.do_you_want_make_sure') }}',
            // text: '{{ __('messages.confirm_update_message') }}',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#858282',
            confirmButtonText: '{{ __('messages.yes_do_it') }}',
            cancelButtonText: '{{ __('messages.cancel') }}',
            allowOutsideClick: false,
            allowEscapeKey: true
          }).then((result) => {
            console.log('📥 Swal result:', result);
            if (result.isConfirmed) {
              console.log('✅ User confirmed, calling updateAssignments()');
              updateAssignments();
            } else {
              console.log('❌ User cancelled');
            }
          }).catch((error) => {
            console.error('❌ Swal error:', error);
            // Fallback to native confirm if Swal fails
            if (confirm('{{ __('messages.do_you_want_make_sure') }}')) {
              updateAssignments();
            }
          });
        } else {
          console.log('ℹ️ No updates detected, closing offcanvas');
          const instance = bootstrap.Offcanvas.getInstance(els.offcanvas);
          if (instance) instance.hide();
        }
      });
    }

    if (els.offcanvas) {
      // Remove old listener to prevent duplicates (use namespace)
      const oldHandler = els.offcanvas._hiddenHandler;
      if (oldHandler) {
        els.offcanvas.removeEventListener('hidden.bs.offcanvas', oldHandler);
      }
      
      // Create new handler
      const hiddenHandler = () => {
        // Only reset if not currently updating (give update process time to complete)
        setTimeout(() => {
          console.log('🧹 Cleaning up after offcanvas hidden');
          // Only cleanup if branchId hasn't been set to a new value
          if (branchId) {
            branchId = null;
          }
          selectedEmployees = [];
          initialAssignIds = [];
          if (els.employeesList) els.employeesList.innerHTML = '';
          if (els.branchName) els.branchName.textContent = '';
          
          // Destroy Select2 and reset flag when offcanvas is hidden
          if (window.$ && $.fn.select2 && select2Initialized && els.select) {
            try {
              $(els.select).select2('destroy');
            } catch (e) {
              // Ignore errors
            }
            select2Initialized = false;
          }
          
          // Remove any remaining Select2 containers
          if (els.select) {
            $(els.select).siblings('.select2-container').remove();
          }
        }, 1500); // Wait 1.5 seconds before cleanup to allow update to complete
      };
      
      // Store handler reference for removal
      els.offcanvas._hiddenHandler = hiddenHandler;
      els.offcanvas.addEventListener('hidden.bs.offcanvas', hiddenHandler);
    }
  }

  function updateAssignments() {
    // Double-check branchId is set
    if (!branchId || branchId === null || branchId === undefined || branchId === '') {
      console.error('❌ Branch ID is missing. Current value:', branchId);
      console.error('📊 Available variables:', { branchId, selectedEmployees, initialAssignIds });
      if (window.errorSnackbar) window.errorSnackbar('Branch ID is missing. Please close and reopen the dialog.');
      return;
    }
    
    const data = { users: selectedEmployees.map(emp => emp.employee_id || emp.id) };
    
    // Get CSRF token from meta tag or form
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
                      document.querySelector('input[name="_token"]')?.value || 
                      '{{ csrf_token() }}';
    
    console.log('🔄 Updating assignments for branch:', branchId);
    console.log('📦 Data being sent:', data);
    
    // Disable the update button to prevent double-clicks
    if (!els.updateBtn) {
      console.error('❌ Update button not found');
      return;
    }
    
    const originalText = els.updateBtn.innerHTML;
    els.updateBtn.disabled = true;
    els.updateBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Updating...';
    
    fetch(api.post(branchId), {
      method: 'POST',
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': csrfToken,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify(data)
    })
    .then(res => {
      console.log('📥 Response status:', res.status);
      if (!res.ok) {
        // Try to get error message from response
        return res.text().then(text => {
          try {
            const json = JSON.parse(text);
            throw new Error(json.message || `HTTP ${res.status}: ${res.statusText}`);
          } catch (e) {
            if (e instanceof Error && e.message.includes('HTTP')) {
              throw e;
            }
            throw new Error(`HTTP ${res.status}: ${res.statusText}`);
          }
        });
      }
      return res.json();
    })
    .then(json => {
      console.log('✅ Response received:', json);
      if (json && json.status) {
        // Update initialAssignIds to match current selection after successful update
        initialAssignIds = selectedEmployees.map(emp => emp.employee_id || emp.id);
        
        if (window.successSnackbar) {
          window.successSnackbar(json.message || 'Assignments updated successfully');
        }
        
        const instance = bootstrap.Offcanvas.getInstance(els.offcanvas);
        if (instance) instance.hide();
        
        // Reload the page to reflect changes
        setTimeout(() => {
          window.location.reload();
        }, 500);
      } else {
        console.error('❌ Update failed:', json);
        if (window.errorSnackbar) {
          window.errorSnackbar(json?.message || 'Failed to update assignments');
        }
        // Re-enable button on error
        els.updateBtn.disabled = false;
        els.updateBtn.innerHTML = originalText;
      }
    })
    .catch(error => {
      console.error('❌ Error updating assignments:', error);
      if (window.errorSnackbar) {
        window.errorSnackbar(error.message || 'Network error occurred');
      }
      // Re-enable button on error
      els.updateBtn.disabled = false;
      els.updateBtn.innerHTML = originalText;
    });
  }

  // Track if handlers are already attached to prevent duplicates
  let staffAssignHandlerAttached = false;
  
  // Only attach the staff_assign listener once
  if (!staffAssignHandlerAttached) {
    staffAssignHandlerAttached = true;
    
    document.addEventListener('staff_assign', (e) => {
      const id = e?.detail?.form_id;
      if (!id) {
        console.error('❌ Staff assign event received without ID');
        return;
      }
      
      // Prevent duplicate event handling if already processing
      if (els.offcanvas) {
        const existingInstance = bootstrap.Offcanvas.getInstance(els.offcanvas);
        if (existingInstance && existingInstance._isShown && branchId === id) {
          console.log('⚠️ Offcanvas already open for this branch, ignoring duplicate event');
          return;
        }
      }
      
      console.log('🔄 Staff assign event received, ID:', id);
      
      // Set branchId immediately to prevent race conditions
      branchId = id;
      console.log('✅ Branch ID set immediately from event:', branchId);
      
      initializeElements();
      
      // Reset Select2 initialization flag to prevent duplicates
      select2Initialized = false;
      
      // Clean up any existing Select2 instances before opening
      if (els.select && window.$ && $.fn.select2) {
        try {
          if ($(els.select).hasClass('select2-hidden-accessible')) {
            $(els.select).select2('destroy');
          }
          $(els.select).siblings('.select2-container').remove();
        } catch (e) {
          // Ignore errors
        }
      }
      
      // Cleanup is handled by Bootstrap events
      
      setupEventListeners();
      loadEmployees();
      loadBranchAssignments(id);
      
      if (els.offcanvas) {
        try {
          console.log('🔄 Opening Assign Staff offcanvas...');
          
          // Check if offcanvas is already open
          const existingInstance = bootstrap.Offcanvas.getInstance(els.offcanvas);
          if (existingInstance && existingInstance._isShown) {
            console.log('⚠️ Offcanvas already open, hiding first...');
            existingInstance.hide();
            // Wait a bit before opening again
            setTimeout(() => {
              openOffcanvas();
            }, 400);
            return;
          }
          
          openOffcanvas();
        
      } catch (error) {
        console.error('❌ Offcanvas error:', error);
      }
    } else {
      console.log('⚠️ Offcanvas element not found');
    }
    
    function openOffcanvas() {
      // Create instance with backdrop: true to allow closing on backdrop click
      const instance = bootstrap.Offcanvas.getOrCreateInstance(els.offcanvas, { 
        backdrop: true,  // Allow closing on backdrop click
        scroll: true,
        keyboard: true
      });
      
      console.log('✅ Offcanvas instance created/get:', instance);
      
      // Only add hide listener once (check if it exists)
      if (!els.offcanvas._hideHandlerAttached) {
        // Prevent double-closing issue with debouncing
        let lastCloseTime = 0;
        let closeDebounceTimeout = null;
        
        const hideHandler = function preventDoubleClose(e) {
          const now = Date.now();
          
          // If a close was triggered very recently (within 200ms), prevent duplicate close
          // This prevents double-click or rapid-click issues while allowing normal closes
          if (lastCloseTime > 0 && (now - lastCloseTime < 200)) {
            e.preventDefault();
            e.stopPropagation();
            return false;
          }
          
          // Record the close time
          lastCloseTime = now;
          
          // Clear any existing timeout
          if (closeDebounceTimeout) {
            clearTimeout(closeDebounceTimeout);
          }
          
          // Reset after delay to allow normal closes after rapid clicks
          closeDebounceTimeout = setTimeout(() => {
            lastCloseTime = 0;
            closeDebounceTimeout = null;
          }, 500);
        };
        
        els.offcanvas.addEventListener('hide.bs.offcanvas', hideHandler);
        els.offcanvas._hideHandlerAttached = true;
      }
      
      // Show offcanvas
      instance.show();
      console.log('✅ Offcanvas shown');
    }
    });
  } // End of staffAssignHandlerAttached check

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
      initializeElements();
      setupEventListeners();
    });
  } else {
    initializeElements();
    setupEventListeners();
  }
})();
</script>
@endpush
// Service Form JavaScript
document.addEventListener('DOMContentLoaded', function() {

  const offcanvasEl = document.getElementById('form-offcanvas');

    if (offcanvasEl) {
        offcanvasEl.addEventListener('show.bs.offcanvas', function() {
            console.log('🔄 Service offcanvas is opening...');
        });
        
        offcanvasEl.addEventListener('shown.bs.offcanvas', function () {
            console.log('✅ Service offcanvas is now shown');
            // Initialize Select2 when offcanvas is shown - increased delay to ensure DOM is ready
            setTimeout(function() {
                console.log('⏱️ Timeout triggered, checking if elements exist...');
                const categoryEl = document.getElementById('category_id');
                const subCategoryEl = document.getElementById('sub_category_id');
                console.log('🔍 Elements check after timeout:');
                console.log('  - category_id element:', categoryEl ? 'Found' : 'Not found');
                console.log('  - sub_category_id element:', subCategoryEl ? 'Found' : 'Not found');
                
                if (typeof window.initializeServiceSelect2 === 'function') {
                    console.log('🔧 Calling initializeServiceSelect2...');
                    window.initializeServiceSelect2();
                } else {
                    console.warn('⚠️ initializeServiceSelect2 function not found');
                }
            }, 300);
            
            // Handle description counter
            const descriptionField = document.getElementById('description');
            const counter = document.getElementById('description-counter');
            if (descriptionField && counter) {
                const maxLength = descriptionField.getAttribute('maxlength') || 250;

                function updateCounter() {
                    const currentLength = descriptionField.value.length;
                    counter.textContent = `${currentLength}/${maxLength}`;
                }

                descriptionField.addEventListener('input', updateCounter);
                updateCounter();
            }
        });
    }

  // Handle edit button clicks
  console.log('service-form.js loaded');
  $(document).off('click', '[data-service-id]');
  $(document).on('click', '.edit-service-btn', function(e) {
      e.preventDefault();
      const serviceId = this.getAttribute('data-service-id') || this.getAttribute('data-service-id');
      if (!serviceId) return;
      loadEditForm(serviceId);
  });
    // Handle form submissions
    document.addEventListener('submit', function(e) {
        if (e.target.id === 'service-form' || e.target.id === 'service-edit-form') {
            handleFormSubmission(e);
        }
    });
});

// Store original blank HTML from page load
const offcanvas = document.querySelector('#form-offcanvas');
const blankOffcanvasHTML = offcanvas.innerHTML;
let lastCrudId = null;

document.addEventListener('crud_change_id', function (e) {
    const formId = e.detail.form_id;
    // Prevent repeated triggers for same ID
    if (formId === lastCrudId) return;
    lastCrudId = formId;

  if (formId === 0 || formId === "0") {
      // ==== NEW SERVICE ====
      offcanvas.innerHTML = blankOffcanvasHTML;
      // Initialize Select2 after form loads
      setTimeout(function() {
          if (typeof initializeServiceSelect2 === 'function') {
              initializeServiceSelect2();
          }
      }, 100);
      // Do NOT call show() here; the global offcanvas handler already shows it
  }
});


function loadEditForm(serviceId) {
    console.log('loadEditForm', serviceId);
    lastCrudId = serviceId;
    if (!offcanvas.querySelector('.offcanvas-body')) {
        offcanvas.innerHTML = `
            <div class="offcanvas-header">
                <h5 class="offcanvas-title">Loading...</h5>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
            </div>
            <div class="offcanvas-body text-center">
                <div class="spinner-border" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        `;
    } else {
        offcanvas.querySelector('.offcanvas-body').innerHTML = `
            <div class="text-center">
                <div class="spinner-border" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        `;
    }

    const editUrlTemplate = window.ServiceRoutes && window.ServiceRoutes.editForm ? window.ServiceRoutes.editForm : `/app/services/edit-form/__ID__`;
    const editRequestUrl = editUrlTemplate.replace('__ID__', serviceId);
    fetch(editRequestUrl)
        .then(response => response.text())
        .then(html => {
            offcanvas.innerHTML = html;
            
            // Extract and execute scripts from the loaded HTML
            const scripts = offcanvas.querySelectorAll('script');
            scripts.forEach(oldScript => {
                const newScript = document.createElement('script');
                Array.from(oldScript.attributes).forEach(attr => {
                    newScript.setAttribute(attr.name, attr.value);
                });
                newScript.appendChild(document.createTextNode(oldScript.innerHTML));
                oldScript.parentNode.replaceChild(newScript, oldScript);
            });
            
            // Clean up any stray backdrops before showing
            document.querySelectorAll('.offcanvas-backdrop').forEach(el => el.remove());
            const bsOffcanvas = bootstrap.Offcanvas.getOrCreateInstance(offcanvas);
            bsOffcanvas.show();
            // Initialize Select2 after form loads
            setTimeout(function() {
                if (typeof initializeServiceSelect2 === 'function') {
                    initializeServiceSelect2();
                }
            }, 100);
            console.log('Offcanvas opened with edit form.');
        })
        .catch(error => {
            console.error('Error loading edit form:', error);
            if (offcanvas.querySelector('.offcanvas-body')) {
                offcanvas.querySelector('.offcanvas-body').innerHTML = `
                    <div class="alert alert-danger">Error loading form. Please try again.</div>
                `;
            }
        });
}


// Handle form submission
function handleFormSubmission(e) {
    const form = e.target;
    const submitBtn = form.querySelector('button[type="submit"]');
    const spinner = submitBtn.querySelector('.spinner-border');

    submitBtn.disabled = true;
    if (spinner) {
        spinner.classList.remove('d-none');
    }

}

// Global functions for image preview
window.previewImage = function(event) {
    const reader = new FileReader();
    reader.onload = function(){
        const preview = event.target.closest('.form-group').querySelector('img');
        preview.src = reader.result;
        const removeBtn = event.target.closest('.form-group').querySelector('.btn-danger');
        if (removeBtn) {
            removeBtn.style.display = 'inline-block';
        }
    };
    reader.readAsDataURL(event.target.files[0]);
};

window.removeImage = function() {
    const formGroup = event.target.closest('.form-group');
    const preview = formGroup.querySelector('img');
    const fileInput = formGroup.querySelector('input[type="file"]');
    const removeBtn = event.target;

    preview.src = '{{ default_feature_image() }}';
    fileInput.value = '';
    removeBtn.style.display = 'none';
};

// Helper function to safely destroy Select2 if it exists
function safeDestroySelect2(selector) {
    const $el = $(selector);
    if ($el.length && $el.data('select2')) {
        try {
            $el.select2('destroy');
        } catch(e) {
            // Ignore errors if Select2 is not properly initialized
        }
    }
}

// Initialize Select2 for category dropdowns (used by service-form.js)
window.initializeServiceSelect2 = function() {
    console.log('🔧 Initializing Select2 for service form dropdowns...');
    
    const $categoryId = $('#category_id');
    const $subCategoryId = $('#sub_category_id');
    
    console.log('🔍 Checking elements:');
    console.log('  - #category_id exists:', $categoryId.length > 0);
    console.log('  - #category_id has Select2:', $categoryId.hasClass('select2-hidden-accessible'));
    console.log('  - #sub_category_id exists:', $subCategoryId.length > 0);
    console.log('  - #sub_category_id has Select2:', $subCategoryId.hasClass('select2-hidden-accessible'));
    
    // Remove any duplicate Select2 containers first
    $categoryId.siblings('.select2-container').remove();
    $subCategoryId.siblings('.select2-container').remove();
    
    // Safely destroy existing Select2 instances first to avoid conflicts
    safeDestroySelect2('#category_id');
    safeDestroySelect2('#sub_category_id');
    safeDestroySelect2('#edit_category_id');
    safeDestroySelect2('#edit_sub_category_id');
    
    // Initialize Select2 for add form category dropdown
    // Always destroy and reinitialize to ensure proper dropdownParent when offcanvas is shown
    if ($categoryId.length) {
        console.log('✅ Reinitializing Select2 for #category_id');
        try {
            // Force destroy if it exists
            if ($categoryId.hasClass('select2-hidden-accessible') || $categoryId.data('select2')) {
                console.log('🔄 Destroying existing Select2 instance for #category_id');
                try {
                    $categoryId.select2('destroy');
                } catch(e) {
                    // If destroy fails, remove data manually
                    $categoryId.removeData('select2');
                    $categoryId.removeClass('select2-hidden-accessible');
                }
                // Remove any leftover containers
                $categoryId.siblings('.select2-container').remove();
            }
            
            // Reinitialize with correct dropdownParent
            $categoryId.select2({
                placeholder: "Select Category",
                allowClear: false,
                width: '100%',
                minimumResultsForSearch: Infinity,
                dropdownParent: $('#form-offcanvas')
            }).on('select2:open', function() {
                console.log('📂 Category dropdown opened');
                // Remove any duplicate containers after opening
                $categoryId.siblings('.select2-container').not(':first').remove();
            }).on('select2:close', function() {
                console.log('📂 Category dropdown closed');
            }).on('change', function() {
                console.log('📂 Category changed:', $(this).val());
            });
            
            // Ensure original select is hidden
            $categoryId.addClass('select2-hidden-accessible');
            // Remove any duplicate containers after initialization
            setTimeout(function() {
                $categoryId.siblings('.select2-container').not(':first').remove();
            }, 100);
            
            console.log('✅ Select2 initialized successfully for #category_id');
        } catch(e) {
            console.error('❌ Error initializing Select2 for #category_id:', e);
        }
    } else {
        console.log('⚠️ #category_id element not found in DOM');
    }
    
    // Initialize Select2 for add form subcategory dropdown
    // Always destroy and reinitialize to ensure proper dropdownParent when offcanvas is shown
    if ($subCategoryId.length) {
        console.log('✅ Reinitializing Select2 for #sub_category_id');
        try {
            // Force destroy if it exists
            if ($subCategoryId.hasClass('select2-hidden-accessible') || $subCategoryId.data('select2')) {
                console.log('🔄 Destroying existing Select2 instance for #sub_category_id');
                try {
                    $subCategoryId.select2('destroy');
                } catch(e) {
                    // If destroy fails, remove data manually
                    $subCategoryId.removeData('select2');
                    $subCategoryId.removeClass('select2-hidden-accessible');
                }
                // Remove any leftover containers
                $subCategoryId.siblings('.select2-container').remove();
            }
            
            // Reinitialize with correct dropdownParent
            $subCategoryId.select2({
                placeholder: "Select Subcategory",
                allowClear: false,
                width: '100%',
                minimumResultsForSearch: Infinity,
                dropdownParent: $('#form-offcanvas')
            }).on('select2:open', function() {
                console.log('📂 Subcategory dropdown opened');
                // Remove any duplicate containers after opening
                $subCategoryId.siblings('.select2-container').not(':first').remove();
            }).on('select2:close', function() {
                console.log('📂 Subcategory dropdown closed');
            }).on('change', function() {
                console.log('📂 Subcategory changed:', $(this).val());
            });
            
            // Ensure original select is hidden
            $subCategoryId.addClass('select2-hidden-accessible');
            // Remove any duplicate containers after initialization
            setTimeout(function() {
                $subCategoryId.siblings('.select2-container').not(':first').remove();
            }, 100);
            
            console.log('✅ Select2 initialized successfully for #sub_category_id');
        } catch(e) {
            console.error('❌ Error initializing Select2 for #sub_category_id:', e);
        }
    } else {
        console.log('⚠️ #sub_category_id element not found in DOM');
    }
    
    // Also initialize edit form dropdowns if they exist
    const editOffcanvas = $('#form-offcanvas');
    if ($('#edit_category_id').length && !$('#edit_category_id').hasClass('select2-hidden-accessible')) {
        $('#edit_category_id').select2({
            placeholder: "Select Category",
            allowClear: false,
            width: '100%',
            minimumResultsForSearch: Infinity,
            dropdownParent: editOffcanvas.length ? editOffcanvas : $('body')
        });
    }
    if ($('#edit_sub_category_id').length && !$('#edit_sub_category_id').hasClass('select2-hidden-accessible')) {
        $('#edit_sub_category_id').select2({
            placeholder: "Select Subcategory",
            allowClear: false,
            width: '100%',
            minimumResultsForSearch: Infinity,
            dropdownParent: editOffcanvas.length ? editOffcanvas : $('body')
        });
    }
};

// Category change handler
window.changeCategory = function(selectElement) {
    console.log('🔄 Category change handler triggered', selectElement);
    const categoryId = selectElement.value;
    console.log('📂 Selected category ID:', categoryId);
    
    const formGroup = selectElement.closest('.form-group');
    const subCategoryGroup = formGroup.parentNode.querySelector('#sub-category-group') ||
                           formGroup.parentNode.querySelector('#edit-sub-category-group');
    const subCategorySelect = formGroup.parentNode.querySelector('#sub_category_id') ||
                            formGroup.parentNode.querySelector('#edit_sub_category_id');

    if (!subCategoryGroup || !subCategorySelect) {
        console.warn('⚠️ Subcategory group or select not found');
        return;
    }

    const $subCategorySelect = $(subCategorySelect);

    if (categoryId) {
        console.log('📡 Fetching subcategories for category:', categoryId);
        // Fetch subcategories for the selected category
        const subcatBase = (window.ServiceRoutes && window.ServiceRoutes.getSubcategories) ? window.ServiceRoutes.getSubcategories : '/app/services/get-subcategories';
        const subcatUrl = `${subcatBase}?category_id=${categoryId}`;
        console.log('📡 Fetch URL:', subcatUrl);
        
        fetch(subcatUrl)
            .then(response => response.json())
            .then(data => {
                console.log('✅ Subcategories fetched:', data);
                $subCategorySelect.empty();
                $subCategorySelect.append('<option value="">Select Subcategory</option>');

                if (data.length > 0) {
                    data.forEach(subcategory => {
                        $subCategorySelect.append(`<option value="${subcategory.id}">${subcategory.name}</option>`);
                    });
                    subCategoryGroup.style.display = 'block';
                    console.log('✅ Subcategory dropdown populated with', data.length, 'options');
                } else {
                    subCategoryGroup.style.display = 'none';
                    console.log('ℹ️ No subcategories found for this category');
                }
                // Update Select2
                $subCategorySelect.trigger('change');
            })
            .catch(error => {
                console.error('❌ Error fetching subcategories:', error);
                subCategoryGroup.style.display = 'none';
            });
    } else {
        console.log('ℹ️ No category selected, hiding subcategory dropdown');
        subCategoryGroup.style.display = 'none';
    }
};
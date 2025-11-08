document.addEventListener("DOMContentLoaded", function () {

  const offcanvasEl = document.getElementById("categoryOffcanvas");
  if (!offcanvasEl) {
    console.error("Category offcanvas element not found");
    return;
  }
  
  const offcanvas = new bootstrap.Offcanvas(offcanvasEl);
  const form = document.getElementById("category-form");
  if (!form) {
    console.error("Category form element not found");
    return;
  }
  
  // The form wraps the offcanvas, so formEl is the same as form
  const formEl = form;
  
  const nameInput = document.getElementById("name");
  const parentInput = document.getElementById("parent_id");
  const baseUrl = document.querySelector('meta[name="baseUrl"]')?.getAttribute('content');
  if (!baseUrl) {
    console.error("Base URL meta tag not found");
    return;
  }
  
  const titleEl = offcanvasEl.querySelector('.offcanvas-title'); // <h5>
  if (!titleEl) {
    console.error("Category offcanvas title element not found");
    return;
  }
  
  const parentSelect = document.getElementById("parent_id");
  const statusInput = document.querySelector('.category-status');

// Initialize Select2 for parent category dropdown
function initializeSelect2() {
  if ($('#parent_id').length && !$('#parent_id').hasClass('select2-hidden-accessible')) {
    $('#parent_id').select2({
      placeholder: "Select Category",
      allowClear: false, // This disables the clear "x" icon
      width: '100%',
      minimumResultsForSearch: Infinity // This disables the search box
    }).on('change', function() {
      // Clear error when category is selected
      const parentVal = $(this).val();
      const element = $(this)[0]; // Get the actual DOM element
      if (parentVal && element) {
        clearError(element);
      }
    });
  }
}

// Initialize on page load
initializeSelect2();

  offcanvasEl.addEventListener('show.bs.offcanvas', function (event) {
    const triggerButton = event.relatedTarget;

    if (triggerButton && triggerButton.matches('[data-bs-target="#categoryOffcanvas"]')) {
      // Safety check: ensure formEl exists
      if (!formEl) {
        console.error("Category form element not found in offcanvas show event");
        return;
      }

      const type = triggerButton.dataset.type;
      if (titleEl) {
        titleEl.textContent = type === "subcategory" ? "New Subcategory" : "New Category";
      }

      formEl.reset();
      const nameInput = formEl.querySelector('#name');
      if (nameInput) nameInput.value = '';

      const statusInput = formEl.querySelector('#status');
      if (statusInput) statusInput.checked = true;

      if (formEl) {
        formEl.querySelectorAll('.fields-error').forEach(el => {
          el.textContent = '';
          el.classList.add('d-none');
        });
      }

      const imgPreview = formEl.querySelector('.upload-image-box img');
      const removeBtn = form ? form.querySelector('.upload-image-box button.btn-danger') : null;

      if (imgPreview) {
          imgPreview.src = imgPreview.dataset.defaultSrc;
          if (removeBtn) {
            removeBtn.style.display = 'none';
          }
      }

      const methodInput = formEl.querySelector('input[name="_method"]');
      if (methodInput) methodInput.remove();

      if (formEl) {
        formEl.querySelectorAll('select').forEach(select => {
          select.value = '';
          if ($(select).hasClass('select2')) {
            $(select).val('').trigger('change');
          }
        });
      }

      // Re-initialize Select2 after form reset
      setTimeout(() => {
        initializeSelect2();
      }, 100);

      formEl.action = `${baseUrl}/app/categories`;
      // Also update the main form variable for consistency
      if (form) {
        form.action = formEl.action;
      }
    }
  });

  document.addEventListener("click", function (e) {
    const categorybtn = e.target.closest("[data-category-id]");
    const subcategorybtn = e.target.closest("[data-subcategory-id]");

    if (!categorybtn && !subcategorybtn) return;

    // Safety check: ensure formEl exists
    if (!formEl) {
      console.error("Category form element not found when trying to edit");
      return;
    }

    let categoryId, type;
    if (categorybtn) {
      categoryId = categorybtn.dataset.categoryId;
      type = "category";
    } else {
      categoryId = subcategorybtn.dataset.subcategoryId;
      type = "subcategory";
    }

    // Safety check: ensure formEl exists before using it
    if (!formEl) {
      console.error("Category form element not found when trying to edit");
      return;
    }

    if (formEl) {
      formEl.querySelectorAll('.fields-error').forEach(el => {
        el.textContent = '';
        el.classList.add('d-none');
      });
    }

    fetch(`${baseUrl}/app/categories/${categoryId}/edit`, {
      headers: { "X-Requested-With": "XMLHttpRequest" }
    })
      .then(res => res.json())
      .then(response => {
        if (!formEl) {
          console.error("Category form element not found in fetch response handler");
          return;
        }

        if (response.status && response.data) {
          if (titleEl) {
            titleEl.textContent = type === "subcategory" ? "Edit Subcategory" : "Edit Category";
          }

          if (nameInput) {
            nameInput.value = response.data.name || "";
          }

          if (statusInput) {
            statusInput.checked = Number(response.data.status) === 1;
          }

          if(type === "subcategory" && parentSelect) {
            parentSelect.value = response.data.parent_id || "";
              $(parentSelect).trigger('change'); // for Select2 UI
          }

          formEl.action = `${baseUrl}/app/categories/${categoryId}`;

          // Ensure _method field exists and is set to PUT for updates
          let methodInput = formEl.querySelector('input[name="_method"]');
          if (!methodInput) {
            methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            formEl.appendChild(methodInput);
          }
          methodInput.value = 'PUT';
          
          // Also update the main form variable for consistency
          if (form) {
            form.action = formEl.action;
          }

          const featureImageUrl = response.data.feature_image;
          const imgPreview = form ? form.querySelector('.upload-image-box img') : null;
          const removeBtn = form ? form.querySelector('.upload-image-box button.btn-danger') : null;

          if (imgPreview) {
            if (featureImageUrl) {
              imgPreview.src = featureImageUrl;
              if (removeBtn) {
                if (!featureImageUrl.includes('default.png')) {
                  removeBtn.style.display = 'inline-block';
                } else {
                  removeBtn.style.display = 'none';
                }
              }
            } else {
              imgPreview.src = imgPreview.dataset.defaultSrc; // fallback to default
              if (removeBtn) {
                removeBtn.style.display = 'none';
              }
            }
          }

          if (offcanvas) {
            offcanvas.show();
          }
        } else {
          console.error("Unexpected response:", response);
        }
      })
      .catch(err => console.error("Error fetching category:", err));
  });

  // Clear errors when user starts entering valid data
  if (nameInput) {
    nameInput.addEventListener('input', function() {
      const nameVal = this.value.trim();
      if (nameVal) {
        clearError(this);
      }
    });

    nameInput.addEventListener('blur', function() {
      const specialCharsRegex = /[!@#$%^&*(),.?":{}|<>\-_;'\/+=\[\]\\]/;
      const numberRegex = /\d/;
      const nameVal = this.value.trim();
      
      if (!nameVal) {
        showError(this, "Name is a required field");
      } else if (specialCharsRegex.test(nameVal) || numberRegex.test(nameVal)) {
        showError(this, "Only strings are allowed");
      } else {
        clearError(this);
      }
    });
  }

  if (parentInput) {
    parentInput.addEventListener('change', function() {
      const parentVal = this.value.trim();
      if (parentVal) {
        clearError(this);
      }
    });

    // Also handle Select2 change event
    if ($(parentInput).length && $(parentInput).hasClass('select2-hidden-accessible')) {
      $(parentInput).on('change', function() {
        const parentVal = $(this).val();
        if (parentVal) {
          clearError(this[0]);
        }
      });
    }
  }

  if (form) {
    form.addEventListener("submit", function (e) {
      let isValid = true;
      if (form) {
        form.querySelectorAll(".form-group .form-control, .form-group .form-select")
          .forEach(input => clearError(input));
      }

      const specialCharsRegex = /[!@#$%^&*(),.?":{}|<>\-_;'\/+=\[\]\\]/;
      const numberRegex = /\d/;

      if (nameInput) {
        const nameVal = nameInput.value.trim();
        if (!nameVal) {
          showError(nameInput, "Name is a required field");
          isValid = false;
        } else if (specialCharsRegex.test(nameVal) || numberRegex.test(nameVal)) {
          showError(nameInput, "Only strings are allowed");
          isValid = false;
        }
      }

      if (parentInput) {
        const parentVal = parentInput.value.trim();
        if (!parentVal) {
          showError(parentInput, "Category is a required field");
          isValid = false;
        }
      }

      if (!isValid) {
        e.preventDefault();
      }
    });
  }

  function showError(input, message) {
    let errorEl = input.closest(".form-group").querySelector(".fields-error");
    if (errorEl) {
      errorEl.textContent = message;
      errorEl.classList.remove("d-none");
    }
  }

  function clearError(input) {
    const formGroup = input.closest(".form-group");
    if (!formGroup) return;
    const errorEl = formGroup.querySelector(".fields-error");
    if (errorEl) {
      errorEl.textContent = "";
      errorEl.classList.add("d-none");
    }
  }
});

window.previewImage = function(event) {
  const reader = new FileReader();
  reader.onload = function() {
    const formGroup = event.target.closest('.form-group');
    const preview = formGroup?.querySelector('img');
    if (preview) {
      preview.src = reader.result;
    }
    const removeBtn = formGroup?.querySelector('.btn-danger');
    if (removeBtn) {
      removeBtn.style.display = 'inline-block';
    }
  };
  reader.readAsDataURL(event.target.files[0]);
};

window.removeImage = function(event) {
  const formGroup = event.target.closest('.form-group');
  const preview = formGroup?.querySelector('img');
  const fileInput = formGroup?.querySelector('input[type="file"]');
  const removeBtn = event.target;

  if (preview) {
    preview.src = preview.dataset.defaultSrc; // from data attribute
  }
  if (fileInput) {
    fileInput.value = '';
  }
  if (removeBtn) {
    removeBtn.style.display = 'none';
  }
};


<form id="employeeChangePasswordForm">
  <div class="offcanvas offcanvas-end" id="Employee_change_password" aria-labelledby="form-offcanvasLabel">

    {{-- Form Header --}}
    <div class="offcanvas-header">
      <h5 class="offcanvas-title">{{ $createTitle ?? '' }}</h5>
      <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>

    <div class="offcanvas-body">
      <div class="row">
        <div class="col-12">
          <div class="form-group">
                    <input type="hidden" name="employee_id" id="employee_id" value="0">

                <div class="form-group mb-3">
                    <label class="form-label">{{ __('messages.old_password') }} <span class="text-danger">*</span></label>
                    <div class="position-relative">
                        <input type="password" class="form-control pe-5" id="cp_old_password" name="old_password" placeholder="{{ __('customer.password') }}">
                        <i class="fa-solid fa-eye-slash toggle-password position-absolute top-50 end-0 translate-middle-y me-3"
                        data-target="cp_old_password" style="cursor: pointer;"></i>
                    </div>
                    <span class="text-danger old_password_error"></span>
                </div>
                <div class="form-group">
                    <label class="form-label">
                      {{ __('messages.new_password') }}<span class="text-danger"> *</span>
                    </label>
                    <div class="position-relative">
                    <input   type="password"    class="form-control pe-5"    id="cp_password"    name="password"    placeholder="{{ __('customer.password') }}"/>
                    <i class="fa-solid fa-eye-slash toggle-password position-absolute top-50 end-0 translate-middle-y me-3"
                    data-target="cp_password" style="cursor: pointer;"></i>
                    </div>
                    <span class="text-danger password_error"></span>
                </div>

                <div class="form-group mt-3">
                    <label class="form-label">
                       {{ __('employee.lbl_confirm_password') }}<span class="text-danger"> *</span>
                    </label>
                     <div class="position-relative">
                        <input    type="password"    class="form-control pe-5"    id="cp_confirm_password"    name="confirm_password"    placeholder="{{ __('customer.confirm_password') }}"/>
                        <i class="fa-solid fa-eye-slash toggle-password position-absolute top-50 end-0 translate-middle-y me-3"
                        data-target="cp_confirm_password" style="cursor: pointer;"></i>
                    </div>
                    <span class="text-danger confirm_password_error"></span>
                </div>
          </div>
        </div>
      </div>
    </div>

    <div class="offcanvas-footer border-top">
      <div class="d-grid d-md-flex gap-3 p-3">
        <button type="submit" class="btn btn-primary d-block">
          <i class="fa-solid fa-floppy-disk"></i> {{ __('messages.save') }}
        </button>
        <button type="button" class="btn btn-outline-primary d-block" data-bs-dismiss="offcanvas" id="closeOffcanvasBtn">
          <i class="fa-solid fa-angles-left"></i> {{ __('messages.close') }}
        </button>
      </div>
    </div>
  </div>
</form>
<script>
document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("employeeChangePasswordForm");

    document.addEventListener("click", function (e) {
        const btn = e.target.closest("[data-assign-event='employee_assign']");
        if (!btn) return;

        const userId = btn.getAttribute("data-assign-module") || 0;
        document.getElementById("employee_id").value = userId;
    });


    function clearErrors() {
        document.querySelectorAll(".old_password_error, .password_error, .confirm_password_error")
            .forEach(el => el.innerText = "");
    }


    function validateForm() {
        let isValid = true;
        clearErrors();

        const old_password = document.getElementById("cp_old_password").value.trim();
        const password = document.getElementById("cp_password").value.trim();
        const confirm_password = document.getElementById("cp_confirm_password").value.trim();

        if (!old_password) {
            document.querySelector(".old_password_error").innerText = "Old Password is required field";
            isValid = false;
        }

        if (!password) {
            document.querySelector(".password_error").innerText = "Password is required field";
            isValid = false;
        } else if (old_password == password) {
            document.querySelector(".password_error").innerText = "New password cannot be the same as the old password.";
            isValid = false;
        } else if (password.length < 8) {
            document.querySelector(".password_error").innerText = "Password must be at least 8 characters";
            isValid = false;
        }

        if (!confirm_password) {
            document.querySelector(".confirm_password_error").innerText = "Confirm Password is required field";
            isValid = false;
        } else if (password !== confirm_password) {
            document.querySelector(".confirm_password_error").innerText = "Passwords must match";
            isValid = false;
        }

        return isValid;
    }


    function resetForm() {
        form.reset();
        clearErrors();
    }

    form.addEventListener("submit", function (e) {
        e.preventDefault();
        if (!validateForm()) return;

        const formData = new FormData(form);

        fetch("{{ route('backend.employees.change_password') }}", {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: formData
        })
        .then(res => res.json())
        .then(res => {
            if (res.status) {
                window.successSnackbar(res.message);
                if (typeof renderedDataTable !== "undefined") {
                    renderedDataTable.ajax.reload(null, false);
                }
                bootstrap.Offcanvas.getInstance('#Employee_change_password').hide();
                resetForm();
                window.currentId = 0;
            } else {
                window.errorSnackbar(res.message);
                if (res.all_message) {
                    Object.keys(res.all_message).forEach(key => {
                        let el = document.querySelector("." + key + "_error");
                        if (el) el.innerText = res.all_message[key][0];
                    });
                }
            }
        })
        .catch(() => {
            window.errorSnackbar("Something went wrong!");
        });
    });

    // Close button reset
    document.getElementById("closeOffcanvasBtn").addEventListener("click", function () {
        resetForm();
    });

    // Toggle show/hide password
    document.querySelectorAll(".toggle-password").forEach(icon => {
    icon.addEventListener("click", function () {
        const targetId = this.getAttribute("data-target");
        const input = document.getElementById(targetId);

        if (input.type === "password") {
            input.type = "text";
            this.classList.remove("fa-eye-slash");
            this.classList.add("fa-eye");
        } else {
            input.type = "password";
            this.classList.remove("fa-eye");
            this.classList.add("fa-eye-slash");
        }
        });
    });
});
</script>

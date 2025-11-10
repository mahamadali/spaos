 @extends('vendorwebsite::layouts.master')

 @section('content')
     {{--
<x-profile_section /> --}}
     <x-breadcrumb />
     <div class="section-spacing-inner-pages">
         <div class="container">
             <div class="profile-box row gy-3">
                 <div class="col-lg-4 col-md-6">
                     <div class="profile-image-section position-relative">
                         <div class="img-container">
                             <img src="{{ asset(user_avatar()) }}" id="profileImagePreview" alt="Profile Image"
                                 class="profile-image rounded-2 object-fit-cover">
                         </div>
                         <div
                             class="image-actions mt-3 position-absolute d-flex flex-nowrap align-items-center justify-content-center">
                             <label for="profile_image"
                                 class="change-btn btn border-0 d-inline-flex align-items-center justify-content-center text-nowrap">
                                 <i class="ph ph-pencil-simple-line font-size-18 icon-color"></i>
                                 {{-- <span class="btn btn-link">{{__("vendorwebsite.change_image")}}</span> --}}
                             </label>
                             <input type="file" id="profile_image" class="d-none" accept="image/*"
                                 onchange="previewSelectedImage(event)">
                             {{-- <button
                                class="border-0 delete-btn btn d-inline-flex flex-column align-items-center justify-content-center"><i
                                    class="ph ph-trash font-size-18 icon-color"></i></button> --}}

                             @if (user_avatar() !== asset('img/vendorwebsite/user_image.png'))
                                 <button
                                     class="border-0 delete-btn btn d-inline-flex flex-column align-items-center justify-content-center">
                                     <i class="ph ph-trash font-size-18 icon-color"></i>
                                 </button>
                             @endif
                         </div>
                     </div>
                 </div>
                 <div class="col-lg-8 col-md-6">
                     <form class="profile-form">
                         <div class="row gy-4">
                             <!-- First Name -->
                             <div class="col-lg-6">
                                 <label for="first_name" class="form-label">{{ __('vendorwebsite.first_name') }}</label>
                                 <div class="input-group custom-input-group position-relative">
                                     <input type="text" id="first_name" name="first_name"
                                         class="form-control font-size-14" value="{{ $user->first_name ?? '' }}" />
                                     <span class="input-group-text"><i class="ph ph-user"></i></span>
                                 </div>
                                 <div class="invalid-feedback" id="first_name_error">
                                     {{ __('vendorwebsite.first_name_field_is_required') }}</div>
                             </div>


                             <!-- Last Name -->
                             <div class="col-lg-6">
                                 <label for="last_name" class="form-label">{{ __('vendorwebsite.last_name') }}</label>
                                 <div class="input-group custom-input-group position-relative">
                                     <input type="text" id="last_name" name="last_name" class="form-control font-size-14"
                                         value="{{ $user->last_name ?? '' }}" />
                                     <span class="input-group-text"><i class="ph ph-user"></i></span>
                                 </div>
                                 <div class="invalid-feedback" id="last_name_error">
                                     {{ __('vendorwebsite.last_name_field_is_required') }}</div>
                             </div>

                             <!-- Email -->
                             <div class="col-lg-6">
                                 <label for="email" class="form-label">{{ __('vendorwebsite.email') }}</label>
                                 <div class="input-group custom-input-group position-relative">
                                     <input type="email" id="email" name="email" class="form-control font-size-14"
                                         value="{{ $user->email ?? '' }}" />
                                     <span class="input-group-text"><i class="ph ph-envelope"></i></span>
                                 </div>
                                 <div class="invalid-feedback" id="email_error">
                                     {{ __('vendorwebsite.email_field_is_required') }}</div>
                             </div>
                             <!-- Contact Number -->
                             <div class="col-lg-6">
                                 <label for="contact_number"
                                     class="form-label">{{ __('vendorwebsite.contact_number') }}</label>
                                 <div class="input-group custom-input-group position-relative">
                                     <input type="tel" id="mobileInput" name="mobile" class="form-control font-size-14"
                                         value="{{ $user->mobile ?? '' }}">
                                     <span class="input-group-text"><i class="ph ph-phone"></i></span>
                                 </div>
                                 <div class="invalid-feedback" id="mobile_error">
                                     {{ __('vendorwebsite.contact_number_is_required') }}</div>
                             </div>


                             <!-- Gender -->
                             <div class="col-12">
                                 <label class="form-label">{{ __('vendorwebsite.gender') }}</label>
                                 <div class="d-flex align-items-center gap-3">
                                     <div class="form-check">
                                         <input class="form-check-input" type="radio" name="gender" id="female"
                                             value="female" {{ $user->gender == 'female' ? 'checked' : '' }}>
                                         <label class="form-check-label"
                                             for="female">{{ __('vendorwebsite.female') }}</label>
                                     </div>
                                     <div class="form-check">
                                         <input class="form-check-input" type="radio" name="gender" id="male"
                                             value="male" {{ $user->gender == 'male' ? 'checked' : '' }}>
                                         <label class="form-check-label"
                                             for="male">{{ __('vendorwebsite.male') }}</label>
                                     </div>
                                     <div class="form-check">
                                         <input class="form-check-input" type="radio" name="gender" id="other"
                                             value="other" {{ $user->gender == 'other' ? 'checked' : '' }}>
                                         <label class="form-check-label"
                                             for="other">{{ __('vendorwebsite.other') }}</label>
                                     </div>
                                 </div>
                                 <div class="invalid-feedback" id="gender_error">
                                     {{ __('vendorwebsite.gender_is_required') }}</div>
                             </div>
                         </div>
                         <div class="form-actions mt-4 d-flex justify-content-end gap-3">
                             {{-- <button type="button" class="btn btn-primary">{{__("vendorwebsite.cancel")}}</button> --}}
                             <button type="submit" id="updateProfileBtn"
                                 class="btn btn-secondary">{{ __('vendorwebsite.save') }}</button>
                         </div>
                     </form>
                 </div>
             </div>
         </div>
     </div>
     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/css/intlTelInput.css">
     <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/js/intlTelInput.min.js"></script>
     <script>
         let isDefaultImage = false;

         function previewSelectedImage(event) {
             const file = event.target.files[0];
             if (file) {
                 const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
                 if (!validTypes.includes(file.type)) {
                     alert('Please select a valid image file (JPEG, JPG, PNG, GIF, WEBP)');
                     event.target.value = '';
                     return;
                 }

                 const maxSize = 5 * 1024 * 1024;
                 if (file.size > maxSize) {
                     alert('File size must be less than 5MB');
                     event.target.value = '';
                     return;
                 }

                 const reader = new FileReader();
                 reader.onload = function(e) {
                     document.getElementById('profileImagePreview').src = e.target.result;
                     isDefaultImage = false;
                 };
                 reader.readAsDataURL(file);
             }
         }

         // IntlTelInput setup
         var mobileInput = document.querySelector("#mobileInput");
         var iti = window.intlTelInput(mobileInput, {
             initialCountry: "gh",
             separateDialCode: true,
             utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/js/utils.js"
         });

         mobileInput.setAttribute("placeholder", "501 234 567");

         // Add digit-only validation for mobile input
         mobileInput.addEventListener('input', function(e) {
             var value = this.value;
             // Remove any non-digit characters except + (for country code)
             var cleanedValue = value.replace(/[^\d+]/g, '');

             // If the cleaned value is different from the original, update the input
             if (cleanedValue !== value) {
                 this.value = cleanedValue;
             }
         });

         // Prevent paste of non-digit characters
         mobileInput.addEventListener('paste', function(e) {
             e.preventDefault();
             var pastedText = (e.clipboardData || window.clipboardData).getData('text');
             var cleanedText = pastedText.replace(/[^\d+]/g, '');
             this.value = cleanedText;
         });

         // Handle delete profile image
         const deleteBtn = document.querySelector('.delete-btn');
         if (deleteBtn) {
             deleteBtn.addEventListener('click', function(e) {
                 e.preventDefault();
                 document.getElementById('profileImagePreview').src = '{{ asset(default_user_avatar()) }}';
                 document.getElementById('profile_image').value = '';
                 isDefaultImage = true;
             });
         }

         // Handle form submission
         document.getElementById('updateProfileBtn').addEventListener('click', function(e) {
             e.preventDefault();

             // Reset validation
             $('.invalid-feedback').hide();
             $('input').removeClass('is-invalid');

             let valid = true;

             // Validate required fields
             const fieldsToValidate = [{
                     name: 'first_name',
                     errorElement: '#first_name_error'
                 },
                 {
                     name: 'last_name',
                     errorElement: '#last_name_error'
                 }
             ];

             fieldsToValidate.forEach(field => {
                 const value = $(`input[name="${field.name}"]`).val().trim();
                 if (!value) {
                     $(field.errorElement).show();
                     $(`input[name="${field.name}"]`).addClass('is-invalid');
                     valid = false;
                 }
             });

             // Validate email
             const emailInput = $('input[name="email"]');
             const emailValue = emailInput.val().trim();
             const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

             if (!emailValue) {
                 $('#email_error').text('Email field is required').show();
                 emailInput.addClass('is-invalid');
                 valid = false;
             } else if (!emailRegex.test(emailValue)) {
                 $('#email_error').text('Please enter a valid email address').show();
                 emailInput.addClass('is-invalid');
                 valid = false;
             }

             // Validate mobile number
             const mobileValue = mobileInput.value.trim();
             if (!mobileValue) {
                 $('#mobileInput').addClass('is-invalid');
                 $('#mobile_error').show();
                 valid = false;
             }

             // Validate gender
             const genderSelected = $('input[name="gender"]:checked').length > 0;
             if (!genderSelected) {
                 $('#gender_error').show();
                 valid = false;
             }

             if (!valid) {
                 return;
             }

             const form = document.querySelector('.profile-form');
             const formData = new FormData(form);

             // Append full international number
             const number = iti.getNumber();
             formData.set('mobile', number);

             // Handle profile image
             if (isDefaultImage) {
                 formData.append('delete_profile_image', '1');
             } else {
                 const image = document.getElementById('profile_image').files[0];
                 if (image) {
                     formData.append('profile_image', image);
                 }
             }

             // UI feedback
             const btn = this;
             btn.disabled = true;
             btn.textContent = '{{ __('vendorwebsite.updating') }}';

             fetch("{{ route('profile.update') }}", {
                     method: 'POST',
                     body: formData,
                     credentials: 'include',
                     headers: {
                         'Accept': 'application/json',
                         'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                             'content')
                     }
                 })
                 .then(async (res) => {
                     const data = await res.json();
                     if (!res.ok) throw data;

                     if (data.success) {

                         toastr.success('Profile updated successfully');

                         if (data.data) {
                             const user = data.data;
                             form.first_name.value = user.first_name || '';
                             form.last_name.value = user.last_name || '';
                             form.email.value = user.email || '';
                             form.mobile.value = user.mobile || '';

                             if (user.gender) {
                                 form.querySelector(`input[name="gender"][value="${user.gender}"]`).checked =
                                     true;
                             }

                             if (user.profile_image) {
                                 document.getElementById('profileImagePreview').src = user.profile_image;
                                 isDefaultImage = false;
                             }
                         }
                     } else {
                         throw data;
                     }
                 })
                 .catch((err) => {
                     console.error(err);
                     window.errorSnackbar(err.message || 'Something went wrong');
                 })
                 .finally(() => {
                     btn.disabled = false;
                     btn.textContent = '{{ __('vendorwebsite.save') }}';
                 });
         });
     </script>
 @endsection

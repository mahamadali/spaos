<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="light">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <title> {{ config('app.name', 'Laravel') }}</title>

    <meta name="description" content="{{ $description ?? '' }}">
    <meta name="keywords" content="{{ $keywords ?? '' }}">
    <meta name="author" content="{{ $author ?? '' }}">
    <meta name="baseUrl" content="{{url('/')}}" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lexend+Deca:wght@100..900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Fuzzy+Bubbles:wght@400;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('modules/frontend/style.css') }}">

    <link rel="stylesheet" href="{{ asset('phosphor-icons/regular/style.css') }}">
    <link rel="stylesheet" href="{{ asset('phosphor-icons/fill/style.css') }}">
    <link rel="shortcut icon" href="{{ asset(setting('favicon')) }}">
    <link rel="icon" type="image/ico" href="{{ asset(setting('favicon')) }}" />

    @yield('styles')


</head>

<body>

    <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ mix('modules/frontend/script.js') }}"></script>

    @yield('content')





    <script>




                document.addEventListener('DOMContentLoaded', function() {
                   const form = document.getElementById('form-submit');
                   const submitButton = document.getElementById('submit-button');
                   let formSubmitted = false;
        if(form){
           const requiredFields = form.querySelectorAll('[required]');

               requiredFields.forEach(field => {
                   field.addEventListener('input', () => validateField(field));
                   field.addEventListener('change', () => validateField(field));
               });
                   form.addEventListener('submit', function(event) {

                       if (formSubmitted) {
                           event.preventDefault();
                           return;
                       }

                       const isValid = validateForm();

                       if (!isValid) {
                           event.preventDefault();
                           submitButton.disabled = false;
                           formSubmitted = false; // Reset the flag
                           return;
                       }

                       submitButton.disabled = true;
                       submitButton.innerText = 'Save';
                       formSubmitted = true;
                   });
               }
               function validateForm() {
               const requiredFields = form.querySelectorAll('[required]');
               let isValid = true;


               requiredFields.forEach(field => {
                   if (!validateField(field)) {
                       isValid = false;
                   }
               });

               const emailInput = form.querySelector('input[type="email"]');
               if (emailInput && emailInput.required && emailInput.value.trim() && !isValidEmail(emailInput.value)) {
                   isValid = false;
                   showValidationError(emailInput, 'Enter a valid Email Address.');
               }
               const mobileInput = form.querySelector('input[name="mobile"]',);
               if (mobileInput && mobileInput.required && mobileInput.value.trim() && !validatePhoneNumber(mobileInput.value)) {
                   isValid = false;
                   showValidationError(mobileInput, 'Enter a valid contact number.');
               }

               const helplineInput = form.querySelector('input[name="helpline_number"]');
               if (helplineInput && helplineInput.required && helplineInput.value.trim() && !validatePhoneNumber(helplineInput.value)) {
                   isValid = false;
                   showValidationError(helplineInput, 'Enter a valid helpline number.');
               }

               const inquiryemailInput = form.querySelector('input[name="inquriy_email"]');
               if (inquiryemailInput && inquiryemailInput.required && inquiryemailInput.value.trim() && !isValidEmail(inquiryemailInput.value)) {
                   isValid = false;
                   showValidationError(inquiryemailInput, 'Enter a valid Inquiry email address.');
               }
               return isValid;
           }
           function showValidationError(input, message) {
                   const errorFeedback = input.nextElementSibling;
                   if (errorFeedback && errorFeedback.classList.contains('invalid-feedback')) {
                       errorFeedback.textContent = message;
                       input.classList.add('is-invalid');
                   }
               }
           function validateField(field) {
               const fieldId = field.id; // Use id for error message display

               const fieldError = document.getElementById(`${fieldId}_error`);

               let isValid = true;

               if (!field.value.trim()) {
                   if (fieldError) {
                       fieldError.style.display = 'block';
                   }
                   isValid = false;
               } else {
                   if (fieldError) {
                       fieldError.style.display = 'none';
                   }
               }

               return isValid;
           }

           function isValidEmail(email) {
               // Simple regex for email validation
               const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
               return emailRegex.test(email);
           }
           function validatePhoneNumber(phoneNumber) {
           const phonePattern = /^[\d\s\-,+]+$/;  // Allows digits, spaces, hyphens, commas, and plus signs
           return phonePattern.test(phoneNumber);
        }

               });
           </script>


        <script>
        (function () {
           'use strict';
           const forms = document.querySelectorAll('.requires-validation');
           Array.from(forms).forEach(function (form) {
               form.addEventListener('submit', function (event) {
                   // Check if TinyMCE is defined before calling triggerSave
                   if (typeof tinymce !== 'undefined') {
                       tinymce.triggerSave();
                   }

                   // Check form validity
                   let isValid = form.checkValidity();

                   if (!isValid) {
                       event.preventDefault();
                       event.stopPropagation();
                   }

                   form.classList.add('was-validated');
               }, false);
           });

        })();




document.addEventListener('DOMContentLoaded', function() {
    const input = document.querySelector("#phone");
    if (input) {
        const iti = intlTelInput(input, {
            initialCountry: "gh",
            separateDialCode: true,
            utilsScript: "/node_modules/intl-tel-input/build/js/utils.js",
        });

        input.setAttribute("placeholder", "501 234 567");

        // Validation
        input.addEventListener('input', function() {
            if (iti.isValidNumber()) {
                input.classList.remove('is-invalid');
                document.getElementById('phone_error').style.display = 'none';
            } else {
                input.classList.add('is-invalid');
                document.getElementById('phone_error').style.display = 'block';
                document.getElementById('phone_error').textContent = 'Please enter a valid phone number';
            }
        });
    }
});

        </script>
</body>

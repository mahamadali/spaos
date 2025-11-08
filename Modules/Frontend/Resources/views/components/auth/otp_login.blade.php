@extends('frontend::layouts.auth')

@section('content')
    <section class="otp-section"
        style="background-image: url({{ asset('/img/frontend/register-page-pattern.png') }}); background-repeat: no-repeat; background-size: cover;">
        <div class="container h-100">
            <div class="row align-items-center justify-content-center h-100">
                <div class="col-lg-8">
                    <div class="py-5 px-3">
                        <div class="text-center">
                            <img src="{{ setting('logo') ? asset(setting('logo')) : asset('/img/frontend/frezka-logo.png') }}"
                                class="img-fluid mb-5 auth-logo" alt="logo" />
                            <div class="otp-wrapper">
                                <div class="mb-5">
                                    <h5 class="mb-3">{{ __('messages.otp_verification') }}</h5>
                                    <p class="mb-0 font-size-14">
                                        {{ __('messages.Check_your_email_and_enter_the_code_you_received.') }}</p>
                                </div>
                                <form action="{{ route('verify-otp') }}" method="POST" id="otpForm">
                                    @csrf
                                    <input type="hidden" name="user_email" value="{{ $email }}">

                                    <div class="d-flex align-items-center justify-content-center gap-md-4 gap-2">
                                        @for ($i = 0; $i < 6; $i++)
                                            <input type="text" name="otp[]"
                                                class="form-control rounded iq-otp-input text-center" maxlength="1"
                                                required />
                                        @endfor
                                    </div>
                                    <small class="text-danger d-block mt-2 d-none" id="errorMessage"></small>

                                    <p class="mb-3 mt-5 font-size-18 fw-medium text-secondary" id="timer">00:30</p>

                                    <div class="d-flex align-items-center justify-content-center flex-wrap gap-1 mt-3 px-3">
                                        <p class="mb-0 font-size-14">{{ __('messages.didn’t_get_the_OTP?') }}</p>
                                        <a href="#" class="text-primary font-size-14 fw-medium" id="resendOtp"
                                            style="pointer-events: none; opacity: 0.5;">{{ __('messages.resend_OTP') }}</a>
                                    </div>

                                    <small id="resendMessage"
                                        class="text-success d-none mt-2">{{ __('messages.OTP_has_been_resent!') }}</small>

                                    <div class="mt-5 pt-3">
                                        <button type="submit"
                                            class="btn btn-secondary">{{ __('messages.verify') }}</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const inputs = document.querySelectorAll(".iq-otp-input");
            const form = document.getElementById("otpForm");
            const resendOtpButton = document.getElementById("resendOtp");
            const resendMessage = document.getElementById("resendMessage");
            const timerElement = document.getElementById("timer");
            const errorMessage = document.getElementById("errorMessage");
            let timeLeft = 30;
            let timerInterval;

            function startTimer() {
                clearInterval(timerInterval);
                timeLeft = 30;
                resendOtpButton.style.pointerEvents = "none";
                resendOtpButton.style.opacity = "0.5";
                timerElement.style.display = "block";
                resendMessage.classList.add("d-none");

                timerInterval = setInterval(() => {
                    const minutes = String(Math.floor(timeLeft / 60)).padStart(2, "0");
                    const seconds = String(timeLeft % 60).padStart(2, "0");
                    timerElement.textContent = `${minutes}:${seconds}`;

                    if (timeLeft === 0) {
                        clearInterval(timerInterval);
                        resendOtpButton.style.pointerEvents = "auto";
                        resendOtpButton.style.opacity = "1";
                        timerElement.style.display = "none";
                    } else {
                        timeLeft -= 1;
                    }
                }, 1000);
            }

            startTimer();

            resendOtpButton.addEventListener("click", async (e) => {
                e.preventDefault();
                resendOtpButton.style.pointerEvents = "none";
                resendOtpButton.style.opacity = "0.5";

                try {
                    const response = await fetch("{{ route('resend-otp') }}", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')
                                .content,
                        },
                        body: JSON.stringify({
                            email: form.querySelector("input[name='user_email']").value
                        }),
                    });

                    if (response.ok) {
                        clearOtpFields();
                        resendMessage.classList.remove("d-none");
                        startTimer();
                    } else {
                        throw new Error("Failed to resend OTP. Please try again.");
                    }
                } catch (error) {

                    resendOtpButton.style.pointerEvents = "auto";
                    resendOtpButton.style.opacity = "1";
                }
            });

            function clearOtpFields() {
                inputs.forEach(input => input.value = "");
                errorMessage.classList.add("d-none");
            }

            inputs.forEach((input, index) => {
                input.addEventListener("input", (e) => {
                    e.target.value = e.target.value.replace(/[^0-9]/g, '');
                    if (e.target.value.length === 1 && index < inputs.length - 1) {
                        inputs[index + 1].focus();
                    }
                });

                input.addEventListener("keydown", (e) => {
                    if (e.key === "Backspace" && e.target.value === "" && index > 0) {
                        inputs[index - 1].focus();
                    }
                });
            });

            form.addEventListener("submit", async (e) => {
                e.preventDefault();
                const submitButton = form.querySelector("button[type='submit']");
                submitButton.disabled = true;

                const otp = Array.from(inputs).map(input => input.value).join("");
                const email = form.querySelector("input[name='user_email']").value;

                if (otp.length !== 6) {
                    showError("Please enter a valid 6-digit OTP.");
                    submitButton.disabled = false;
                    return;
                }

                try {
                    const response = await fetch(form.action, {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')
                                .content,
                        },
                        body: JSON.stringify({
                            otp,
                            email
                        }),
                    });

                    const responseData = await response.json();

                    if (response.ok) {
                        if (responseData.isNotFreePlan) {
                            window.location.href = "{{ url('/') }}";
                        } else {
                            window.location.href = "{{ url('/app') }}";
                        }
                    } else {
                        clearOtpFields();
                        // const errorData = await response.json();
                        showError(responseData.message || "Invalid OTP. Please try again.");
                    }
                } catch (error) {
                    showError("An error occurred. Please try again later.");
                } finally {
                    submitButton.disabled = false;
                }
            });

            function showError(message) {
                errorMessage.textContent = message;
                errorMessage.classList.remove("d-none");
            }
        });
    </script>
@endsection

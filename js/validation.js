/**
 * StudentHub — Form Validation Script (js/validation.js)
 * Clean, beginner-friendly frontend validation for the registration form.
 * Includes real-time error feedback and a visual password strength meter.
 */

document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("registration-form");
    if (!form) return;

    // 1. Form input fields
    const fullNameInput        = document.getElementById("full-name");
    const emailInput           = document.getElementById("email");
    const mobileInput          = document.getElementById("mobile");
    const courseSelect         = document.getElementById("course");
    const yearSelect           = document.getElementById("year");
    const genderRadios         = document.querySelectorAll('input[name="gender"]');
    const genderFieldset       = document.getElementById("gender-fieldset");
    const passwordInput        = document.getElementById("password");
    const confirmPasswordInput = document.getElementById("confirm-password");
    const termsCheckbox        = document.getElementById("terms");

    // 2. Error message containers
    const fullNameError        = document.getElementById("fullname-error");
    const emailError           = document.getElementById("email-error");
    const mobileError          = document.getElementById("mobile-error");
    const courseError          = document.getElementById("course-error");
    const yearError            = document.getElementById("year-error");
    const genderError          = document.getElementById("gender-error");
    const passwordError        = document.getElementById("password-error");
    const confirmPasswordError = document.getElementById("confirm-password-error");
    const termsError           = document.getElementById("terms-error");

    // 3. Password meter elements
    const meterFill    = document.getElementById("password-meter-fill");
    const strengthText = document.getElementById("password-strength-text");

    // Helper: Display error message and red border on input
    function showError(input, errorSpan, message) {
        if (input) {
            input.classList.add("input-error");
            input.classList.remove("input-valid");
        }
        if (errorSpan) {
            errorSpan.textContent = message;
            errorSpan.classList.add("visible");
        }
    }

    // Helper: Clear error message and apply green border on input
    function clearError(input, errorSpan) {
        if (input) {
            input.classList.remove("input-error");
            input.classList.add("input-valid");
        }
        if (errorSpan) {
            errorSpan.textContent = "";
            errorSpan.classList.remove("visible");
        }
    }

    // --- Field Validation Functions ---

    // 1. Full Name: Required, letters & spaces only, 3 to 50 chars
    function validateFullName() {
        const val = fullNameInput.value.trim();
        const regex = /^[a-zA-Z\s]{3,50}$/;

        if (val === "") {
            showError(fullNameInput, fullNameError, "Full name is required.");
            return false;
        } else if (!regex.test(val)) {
            showError(fullNameInput, fullNameError, "Name must be 3–50 characters (letters and spaces only).");
            return false;
        }
        clearError(fullNameInput, fullNameError);
        return true;
    }

    // 2. Email: Required, standard email pattern
    function validateEmail() {
        const val = emailInput.value.trim();
        const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        if (val === "") {
            showError(emailInput, emailError, "College email address is required.");
            return false;
        } else if (!regex.test(val)) {
            showError(emailInput, emailError, "Please enter a valid email address (e.g., student@college.edu).");
            return false;
        }
        clearError(emailInput, emailError);
        return true;
    }

    // 3. Mobile: Required, exactly 10 digits
    function validateMobile() {
        const val = mobileInput.value.trim();
        const regex = /^[0-9]{10}$/;

        if (val === "") {
            showError(mobileInput, mobileError, "Mobile number is required.");
            return false;
        } else if (!regex.test(val)) {
            showError(mobileInput, mobileError, "Mobile number must be exactly 10 digits.");
            return false;
        }
        clearError(mobileInput, mobileError);
        return true;
    }

    // 4. Course: Must select an option from dropdown
    function validateCourse() {
        if (courseSelect.value === "") {
            showError(courseSelect, courseError, "Please select your course / degree.");
            return false;
        }
        clearError(courseSelect, courseError);
        return true;
    }

    // 5. Year: Must select an option from dropdown
    function validateYear() {
        if (yearSelect.value === "") {
            showError(yearSelect, yearError, "Please select your year of study.");
            return false;
        }
        clearError(yearSelect, yearError);
        return true;
    }

    // 6. Gender: Must select one radio button
    function validateGender() {
        let isSelected = false;
        genderRadios.forEach(function (radio) {
            if (radio.checked) isSelected = true;
        });

        if (!isSelected) {
            showError(genderFieldset, genderError, "Please select your gender.");
            return false;
        }
        clearError(genderFieldset, genderError);
        return true;
    }

    // 7. Password: At least 6 characters with at least 1 number
    function validatePassword() {
        const val = passwordInput.value;

        if (val === "") {
            showError(passwordInput, passwordError, "Password is required.");
            return false;
        } else if (val.length < 6) {
            showError(passwordInput, passwordError, "Password must be at least 6 characters long.");
            return false;
        } else if (!/\d/.test(val)) {
            showError(passwordInput, passwordError, "Password must contain at least one number.");
            return false;
        }
        clearError(passwordInput, passwordError);
        return true;
    }

    // 8. Confirm Password: Must match password exactly
    function validateConfirmPassword() {
        const val = confirmPasswordInput.value;
        const passVal = passwordInput.value;

        if (val === "") {
            showError(confirmPasswordInput, confirmPasswordError, "Please confirm your password.");
            return false;
        } else if (val !== passVal) {
            showError(confirmPasswordInput, confirmPasswordError, "Passwords do not match.");
            return false;
        }
        clearError(confirmPasswordInput, confirmPasswordError);
        return true;
    }

    // 9. Terms: Checkbox must be checked
    function validateTerms() {
        if (!termsCheckbox.checked) {
            showError(termsCheckbox, termsError, "You must agree to the terms before registering.");
            return false;
        }
        clearError(termsCheckbox, termsError);
        return true;
    }

    // 10. Password Strength Meter Calculation
    function updatePasswordStrength(password) {
        if (!meterFill || !strengthText) return;

        meterFill.className = "password-meter-fill";
        strengthText.className = "strength-text";

        if (!password) {
            meterFill.style.width = "0%";
            strengthText.textContent = "None";
            return;
        }

        // Score criteria: length, uppercase, numbers, special characters
        let score = 0;
        if (password.length >= 6) score++;
        if (password.length >= 10) score++;
        if (/[A-Z]/.test(password) && /[a-z]/.test(password)) score++;
        if (/\d/.test(password)) score++;
        if (/[^a-zA-Z0-9]/.test(password)) score++;

        if (score <= 2) {
            meterFill.classList.add("weak");
            strengthText.classList.add("weak");
            strengthText.textContent = "Weak";
        } else if (score <= 4) {
            meterFill.classList.add("medium");
            strengthText.classList.add("medium");
            strengthText.textContent = "Medium";
        } else {
            meterFill.classList.add("strong");
            strengthText.classList.add("strong");
            strengthText.textContent = "Strong";
        }
    }

    // --- Attach Real-Time Event Listeners ---
    if (fullNameInput) {
        fullNameInput.addEventListener("input", validateFullName);
        fullNameInput.addEventListener("blur", validateFullName);
    }

    if (emailInput) {
        emailInput.addEventListener("input", validateEmail);
        emailInput.addEventListener("blur", validateEmail);
    }

    if (mobileInput) {
        mobileInput.addEventListener("input", validateMobile);
        mobileInput.addEventListener("blur", validateMobile);
    }

    if (courseSelect) {
        courseSelect.addEventListener("change", validateCourse);
    }

    if (yearSelect) {
        yearSelect.addEventListener("change", validateYear);
    }

    genderRadios.forEach(function (radio) {
        radio.addEventListener("change", validateGender);
    });

    if (passwordInput) {
        passwordInput.addEventListener("input", function () {
            updatePasswordStrength(this.value);
            validatePassword();
            if (confirmPasswordInput.value !== "") {
                validateConfirmPassword();
            }
        });
        passwordInput.addEventListener("blur", validatePassword);
    }

    if (confirmPasswordInput) {
        confirmPasswordInput.addEventListener("input", validateConfirmPassword);
        confirmPasswordInput.addEventListener("blur", validateConfirmPassword);
    }

    if (termsCheckbox) {
        termsCheckbox.addEventListener("change", validateTerms);
    }

    // Reset button handler: clear all borders and error messages
    form.addEventListener("reset", function () {
        setTimeout(function () {
            const inputs = form.querySelectorAll("input, select, fieldset");
            inputs.forEach(el => el.classList.remove("input-error", "input-valid"));

            const errors = form.querySelectorAll(".error-msg");
            errors.forEach(el => {
                el.textContent = "";
                el.classList.remove("visible");
            });

            if (meterFill && strengthText) {
                meterFill.className = "password-meter-fill";
                meterFill.style.width = "0%";
                strengthText.className = "strength-text";
                strengthText.textContent = "None";
            }
        }, 10);
    });

    // Form Submit Handler
    form.addEventListener("submit", function (e) {
        // Run all 9 validations
        const isNameValid    = validateFullName();
        const isEmailValid   = validateEmail();
        const isMobileValid  = validateMobile();
        const isCourseValid  = validateCourse();
        const isYearValid    = validateYear();
        const isGenderValid  = validateGender();
        const isPassValid    = validatePassword();
        const isConfirmValid = validateConfirmPassword();
        const isTermsValid   = validateTerms();

        const isFormValid = isNameValid && isEmailValid && isMobileValid &&
            isCourseValid && isYearValid && isGenderValid &&
            isPassValid && isConfirmValid && isTermsValid;

        // If any validation failed, stop submission and focus first invalid field
        if (!isFormValid) {
            e.preventDefault();
            const firstInvalid = form.querySelector(".input-error");
            if (firstInvalid && typeof firstInvalid.focus === "function") {
                firstInvalid.focus();
            }
            return;
        }

        // If form action is '#' (frontend demo), prevent page reload
        const action = form.getAttribute("action");
        if (!action || action === "#") {
            e.preventDefault();
            const successBanner = document.getElementById("form-success-banner");
            if (successBanner) {
                successBanner.classList.add("visible");
                successBanner.scrollIntoView({ behavior: "smooth", block: "nearest" });
            }
        }
    });
});

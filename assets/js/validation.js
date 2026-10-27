// Form Validation Functions

// Validate email format
function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

// Validate Philippine phone number (required, exactly 11 digits)
function validatePhone(phone) {
    const re = /^09\d{9}$/;
    return re.test(phone);
}

// Validate password strength (min 8 characters)
function validatePassword(password) {
    return password.length >= 8;
}

// Validate username (alphanumeric, 3-20 characters)
function validateUsername(username) {
    const re = /^[a-zA-Z0-9_]{3,20}$/;
    return re.test(username);
}

// Validate full name (letters, spaces, 2-50 characters)
function validateFullName(name) {
    const re = /^[a-zA-Z\s]{2,50}$/;
    return re.test(name);
}

// Validate price (positive number)
function validatePrice(price) {
    const num = parseFloat(price);
    return !isNaN(num) && num > 0;
}

// Validate quantity (positive integer)
function validateQuantity(quantity) {
    const num = parseInt(quantity);
    return !isNaN(num) && num > 0 && num === parseFloat(quantity);
}

// Validate rating (1-5)
function validateRating(rating) {
    const num = parseInt(rating);
    return !isNaN(num) && num >= 1 && num <= 5;
}

// Show error message
function showError(input, message) {
    const formGroup = input.closest('.form-group');
    let errorElement = formGroup.querySelector('.error-message');

    if (!errorElement) {
        errorElement = document.createElement('div');
        errorElement.className = 'error-message';
        errorElement.style.color = '#8b4513';
        errorElement.style.fontSize = '0.85rem';
        errorElement.style.marginTop = '0.25rem';
        formGroup.appendChild(errorElement);
    }

    errorElement.textContent = message;
    input.style.borderColor = '#8b4513';
}

// Clear error message
function clearError(input) {
    const formGroup = input.closest('.form-group');
    const errorElement = formGroup.querySelector('.error-message');

    if (errorElement) {
        errorElement.remove();
    }
    input.style.borderColor = '';
}

// Add real-time validation to an input
function addValidation(input, validator, errorMessage) {
    input.addEventListener('blur', function() {
        if (input.value && !validator(input.value)) {
            showError(input, errorMessage);
        } else {
            clearError(input);
        }
    });

    input.addEventListener('input', function() {
        if (input.style.borderColor === 'rgb(139, 69, 19)') {
            if (validator(input.value)) {
                clearError(input);
            }
        }
    });
}

// Validate entire form
function validateForm(form) {
    let isValid = true;
    const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');

    inputs.forEach(input => {
        if (!input.value.trim()) {
            showError(input, 'This field is required');
            isValid = false;
        } else {
            clearError(input);
        }
    });

    return isValid;
}

// Confirm action with dialog
function confirmAction(message) {
    return confirm(message);
}

// Prevent double submission
function preventDoubleSubmit(form) {
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;

    form.addEventListener('submit', function(e) {
        if (submitBtn.disabled) {
            e.preventDefault();
            return false;
        }

        if (!validateForm(form)) {
            e.preventDefault();
            return false;
        }

        submitBtn.disabled = true;
        submitBtn.textContent = 'Processing...';
    });
}

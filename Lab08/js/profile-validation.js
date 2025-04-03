// profile-validation.js - Client-side validation for profile forms
document.addEventListener('DOMContentLoaded', function() {
    // Get form elements
    const profileForm = document.getElementById('profile-form');
    const securityForm = document.getElementById('security-form');
    const deleteForm = document.getElementById('delete-form');
    
    // Input fields
    const fnameInput = document.getElementById('fname');
    const lnameInput = document.getElementById('lname');
    const emailInput = document.getElementById('email');
    const contactInput = document.getElementById('contact');
    const newPasswordInput = document.getElementById('new_pwd');
    const confirmPasswordInput = document.getElementById('confirm_pwd');
    
    // Error messages container
    const createErrorMessage = (input, message) => {
        // Remove existing error message if any
        const existingError = input.nextElementSibling;
        if (existingError && existingError.classList.contains('invalid-feedback')) {
            existingError.remove();
        }
        
        // Create and insert new error message
        const errorDiv = document.createElement('div');
        errorDiv.className = 'invalid-feedback';
        errorDiv.textContent = message;
        errorDiv.style.display = 'block';
        input.after(errorDiv);
        
        // Add invalid class to input
        input.classList.add('is-invalid');
        return false;
    };
    
    // Clear error message
    const clearErrorMessage = (input) => {
        const existingError = input.nextElementSibling;
        if (existingError && existingError.classList.contains('invalid-feedback')) {
            existingError.remove();
        }
        input.classList.remove('is-invalid');
        input.classList.add('is-valid');
    };
    
    // Validation functions
    const validateName = (input, fieldName) => {
        const value = input.value.trim();
        const nameRegex = /^[a-zA-Z ]+$/; // Only letters and spaces allowed
        
        if (value === '') {
            return createErrorMessage(input, `${fieldName} is required`);
        }
        
        if (!nameRegex.test(value)) {
            return createErrorMessage(input, `${fieldName} should only contain letters and spaces`);
        }
        
        clearErrorMessage(input);
        return true;
    };
    
    const validateEmail = (input) => {
        const value = input.value.trim();
        // RFC 5322 compliant email regex
        const emailRegex = /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/;
        
        if (value === '') {
            return createErrorMessage(input, 'Email is required');
        }
        
        if (!emailRegex.test(value)) {
            return createErrorMessage(input, 'Please enter a valid email address');
        }
        
        clearErrorMessage(input);
        return true;
    };
    
    const validateContact = (input) => {
        const value = input.value.trim();
        const contactRegex = /^\d{8}$/; // Exactly 8 digits
        
        if (value === '') {
            return createErrorMessage(input, 'Contact number is required');
        }
        
        if (!contactRegex.test(value)) {
            return createErrorMessage(input, 'Contact number must be exactly 8 digits');
        }
        
        clearErrorMessage(input);
        return true;
    };
    
    const validatePassword = (input) => {
        const value = input.value;
        
        if (value === '') {
            return createErrorMessage(input, 'Password is required');
        }
        
        if (value.length < 8) {
            return createErrorMessage(input, 'Password must be at least 8 characters long');
        }
        
        // Check for combination of letters, numbers, and special characters
        const hasLetter = /[a-zA-Z]/.test(value);
        const hasNumber = /\d/.test(value);
        const hasSpecial = /[!@#$%^&*(),.?":{}|<>]/.test(value);
        
        if (!(hasLetter && hasNumber && hasSpecial)) {
            return createErrorMessage(input, 'Password must include letters, numbers, and special characters');
        }
        
        clearErrorMessage(input);
        return true;
    };
    
    const validateConfirmPassword = () => {
        if (!newPasswordInput || !confirmPasswordInput) return true;
        
        if (newPasswordInput.value !== confirmPasswordInput.value) {
            return createErrorMessage(confirmPasswordInput, 'Passwords do not match');
        }
        
        clearErrorMessage(confirmPasswordInput);
        return true;
    };
    
    // Add input event listeners for real-time validation
    if (fnameInput) {
        fnameInput.addEventListener('input', () => validateName(fnameInput, 'First Name'));
    }
    
    if (lnameInput) {
        lnameInput.addEventListener('input', () => validateName(lnameInput, 'Last Name'));
    }
    
    if (emailInput) {
        emailInput.addEventListener('input', () => validateEmail(emailInput));
    }
    
    if (contactInput) {
        contactInput.addEventListener('input', () => validateContact(contactInput));
    }
    
    if (newPasswordInput) {
        newPasswordInput.addEventListener('input', () => {
            validatePassword(newPasswordInput);
            if (confirmPasswordInput.value !== '') {
                validateConfirmPassword();
            }
        });
    }
    
    if (confirmPasswordInput) {
        confirmPasswordInput.addEventListener('input', validateConfirmPassword);
    }
    
    // Form submit validation
    if (profileForm) {
        profileForm.addEventListener('submit', function(e) {
            let isValid = true;
            
            // Validate each field
            if (!validateName(fnameInput, 'First Name')) isValid = false;
            if (!validateName(lnameInput, 'Last Name')) isValid = false;
            if (!validateEmail(emailInput)) isValid = false;
            if (!validateContact(contactInput)) isValid = false;
            
            if (!isValid) {
                e.preventDefault();
            }
        });
    }
    
    if (securityForm) {
        securityForm.addEventListener('submit', function(e) {
            let isValid = true;
            
            // Validate password fields
            if (!validatePassword(newPasswordInput)) isValid = false;
            if (!validateConfirmPassword()) isValid = false;
            
            if (!isValid) {
                e.preventDefault();
            }
        });
    }
    
    // Format contact number to only allow numbers
    if (contactInput) {
        contactInput.addEventListener('keypress', function(e) {
            // Allow only digits (0-9)
            if (e.key < '0' || e.key > '9') {
                e.preventDefault();
            }
            
            // Limit to 8 digits
            if (this.value.length >= 8) {
                e.preventDefault();
            }
        });
    }
});
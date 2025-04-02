document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    const cardNumberInput = document.getElementById('card_number');
    const expiryDateInput = document.getElementById('expiry_date');
    const cvvInput = document.getElementById('cvv');
    
    // Format card number as user types
    cardNumberInput.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length > 16) {
            value = value.slice(0, 16);
        }
        e.target.value = value;
    });
    
    // Format and validate expiry date as MM/YY
    expiryDateInput.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length > 4) {
            value = value.slice(0, 4);
        }
        if (value.length > 2) {
            value = value.slice(0, 2) + '/' + value.slice(2);
        }
        e.target.value = value;
    });

    // Add this function to validate the expiry date
    function isValidExpiryDate(value) {
        // Check format first (MM/YY)
        const expiryPattern = /^(0[1-9]|1[0-2])\/([0-9]{2})$/;
        if (!expiryPattern.test(value)) {
            return false;
        }
        
        // Check if the date is in the future
        const parts = value.split('/');
        const month = parseInt(parts[0], 10);
        const year = parseInt('20' + parts[1], 10);
        
        const currentDate = new Date();
        const currentYear = currentDate.getFullYear();
        const currentMonth = currentDate.getMonth() + 1; // JavaScript months are 0-indexed
        
        // Card is expired if:
        // 1. Year is in the past, or
        // 2. Year is current but month is in the past or current
        return (year > currentYear) || (year === currentYear && month >= currentMonth);
    }

    // Add form submission validation
    form.addEventListener('submit', function(e) {
        // Validate expiry date
        if (!isValidExpiryDate(expiryDateInput.value)) {
            e.preventDefault();
            alert('Please enter a valid expiry date (MM/YY) in the future');
            expiryDateInput.focus();
        }
    });
    
    // Limit CVV to 3-4 digits
    cvvInput.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length > 4) {
            value = value.slice(0, 4);
        }
        e.target.value = value;
    });
});

        // Simple form validation for payment fields
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
            
            // Format expiry date as MM/YY
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
            
            // Limit CVV to 3-4 digits
            cvvInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                if (value.length > 4) {
                    value = value.slice(0, 4);
                }
                e.target.value = value;
            });
        });
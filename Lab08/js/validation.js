document.getElementById("lname").addEventListener("input", function() {
    let lname = this.value.trim();
    let lnameError = document.getElementById("lnameError");

    let namePattern = /^[a-zA-Z]+$/; // Regex: Only allows a-z and A-Z

    if (lname.length < 3 || !namePattern.test(lname)) {
        lnameError.textContent = "Last name must be at least 3 characters and can only contain letters (a-z, A-Z).";
    } else {
        lnameError.textContent = "";
    }
});

document.getElementById("email").addEventListener("input", function () {
    let email = this.value.trim();
    let emailError = document.getElementById("emailError");

    let emailPattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;

    if (email === "") {
        emailError.textContent = "Email is required.";
    } else if (!emailPattern.test(email)) {
        emailError.textContent = "Please enter a valid email address.";
    } else {
        emailError.textContent = "";
    }
});

document.getElementById("pwd").addEventListener("input", function () {
    let password = this.value.trim();
    let passwordError = document.getElementById("passwordError");

    // Minimum 8 characters, at least one uppercase letter, one lowercase letter, one number and one special character
    let passwordPattern = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/;

    if (password === "") {
        console.log("here");
        passwordError.textContent = "Password is required.";
    } else if (!passwordPattern.test(password)) {
        passwordError.textContent = "Password must be at least 8 characters and include uppercase, lowercase, number, and special character.";
    } else {
        passwordError.textContent = "";
    }
});

document.getElementById("pwd_confirm").addEventListener("input", function () {
    let password = document.getElementById("pwd").value;
    let confirmPassword = this.value;
    let confirmError = document.getElementById("confirmPasswordError");

    if (confirmPassword === "") {
        confirmError.textContent = "Please confirm your password.";
    } else if (confirmPassword !== password) {
        confirmError.textContent = "Passwords do not match.";
    } else {
        confirmError.textContent = "";
    }
});



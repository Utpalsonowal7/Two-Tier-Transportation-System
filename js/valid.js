const passInput = document.getElementById("password");
const confirmInput = document.getElementById("confirmPass");
const passValid = document.getElementById("passwordValidation");
const form = document.querySelector('form')

const pattern = /^(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{10,}$/;
passInput.addEventListener("input", () => {
    const pattern = /^(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{10,}$/;

    if (passInput.value === "" || pattern.test(passInput.value)) {
        passValid.style.display = "none";
    } else {
        passValid.style.display = "block";
    }
});

form.addEventListener("submit", function (e) {
    let isValid = true;
    console.log("Form submitted");

    if (!pattern.test(passInput.value)) {
        passValid.style.display = "block";
        isValid = false;
    } else {
        passValid.style.display = "none";
    }

    if (passInput.value !== confirmInput.value) {
         alert("Oops!, Both passwords must be the same.");
        isValid = false;
    }

    if (!isValid) {
        e.preventDefault(); 
    }
});

document.getElementById("togglePassword").addEventListener("click", function () {
    const input = document.getElementById("password");
    input.type = input.type === "password" ? "text" : "password";
    this.classList.toggle("fa-eye-slash");
});

document.getElementById("toggleConfirmPassword").addEventListener("click", function () {
    const input = document.getElementById("confirmPass");
    input.type = input.type === "password" ? "text" : "password";
    this.classList.toggle("fa-eye-slash");
});
function saveToPHP() {
     const form = document.getElementById("infoForm");
     const messageBox = document.getElementById("formMessage");
     messageBox.textContent = "";
     messageBox.classList.remove("success", "error");

     const inputs = form.querySelectorAll("input, select, textarea");
     let valid = true;
     let errorInPuts = [];

     inputs.forEach(input => {
          input.classList.remove("error");

          if (input.hasAttribute("name") && input.type !== "hidden") {
               const value = input.value.trim();
               if (!value || value === "select") {
                    valid = false;
                    input.classList.add("error");
                    errorInPuts.push(input);
               }
          }
     });

     if (!valid) {
          showMessage("Please fill all required fields correctly.", false);
          setTimeout(() => {
               errorInPuts.forEach(el => {
                    el.classList.remove("error");
               });
          }, 5000);
          window.scrollTo({ top: form.offsetTop, behavior: "smooth" });
          return;
     }

     const formData = new FormData(form);

     fetch("both_tier_data.php", {
          method: "POST",
          body: formData
     })
          .then(res => res.text())
          .then(data => {
               showMessage("Form submitted successfully!", true);
               console.log("server response :", data);
               form.reset();
               reloadDistrict();
          })
          .catch(err => {
               showMessage("Error saving data. Please try again.", false);
               console.error(err);
          });
}


function showMessage(text, success = false) {
     const box = document.getElementById("formMessage");
     box.textContent = text;
     box.classList.toggle("success", success);
     box.classList.toggle("error", !success);

     
     setTimeout(() => {
          box.textContent = "";
          box.classList.remove("success", "error");
     }, 5000);
}

document.addEventListener("DOMContentLoaded", function () {
     const circles = document.querySelectorAll(".countCircle");
     if (!circles.length) return;

     circles.forEach(circle => {
          let count = parseInt(circle.getAttribute("data-count"));
          let fontSize = parseInt(getComputedStyle(circle).fontSize);

          function updateCircle(count) {
               circle.textContent = count;

               const digitCount = count.toString().length;
               const baseSize = 80;
               const extraPerDigit = 3;

               if (digitCount > 3) {
                    const newSize = baseSize + (digitCount - 1) * extraPerDigit;
                    circle.style.width = newSize + "px";
                    circle.style.height = newSize + "px";
               }

               if (digitCount > 5) {
                    circle.style.fontSize = "24px";
               } else if (digitCount > 4) {
                    circle.style.fontSize = "28px";
               } else if (digitCount > 2) {
                    circle.style.fontSize = "32px";
               } else {
                    circle.style.fontSize = fontSize + "px";
               }
          }

          // setInterval(() => {
          //      count += Math.floor(Math.random() * 100);
          //      updateCircle(count);
          // }, 1000);


          updateCircle(count);
     });
});

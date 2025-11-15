// function toggleDropdown(event) {
//      event.preventDefault();
//      event.stopPropagation(); // prevents immediate close by window.onclick
//      const dropdown = event.target.closest('.dropdown');
//      const menu = dropdown.querySelector('.dropdown-menu');
//      const isVisible = menu.style.display === 'block';
//      menu.style.display = isVisible ? 'none' : 'block';
// }

// window.onclick = function (event) {
//      if (!event.target.closest('.dropdown')) {
//           document.querySelectorAll('.dropdown-menu').forEach(menu => {
//                menu.style.display = 'none';
//           });
//      }
// }

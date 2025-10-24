function toggleDropdown(event) {
     event.preventDefault();
     const dropdownMenu = event.currentTarget.nextElementSibling;
     const isVisible = dropdownMenu.style.display === 'block';

     dropdownMenu.style.display = isVisible ? 'none' : 'block';
}


window.onclick = function (event) {
     if (!event.target.matches('.dropdown a')) {
          const dropdowns = document.querySelectorAll('.dropdown-menu');
          dropdowns.forEach(dropdown => {
               dropdown.style.display = 'none';
          });
     }
}

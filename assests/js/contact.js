

// ---------------------------------------------------------------------------------------------------------

(function() {
    'use strict';

    const form = document.getElementById('contactForm');
    const successMessage = document.getElementById('success-message');
    const errorMessage = document.getElementById('error-message');

    // Pehle dono ko hide kar do
    successMessage.style.display = 'none';
    errorMessage.style.display = 'none';

    form.addEventListener('submit', function(event) {
        event.preventDefault(); // Default submit ko roko

        // Agar form ke andar required field hai to validity check karo
        if (!form.checkValidity()) {
            event.stopPropagation();
            form.classList.add('was-validated');
            return;
        }

        // AJAX se form submit karna
        const formData = new FormData(form);
        formData.append('ajax_submit', '1');

        fetch('./php/insert.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            console.log('Response:', data); // Debug ke liye
            //  console.log('Raw response:', JSON.stringify(data)); // ← Add this

            if (data.trim() === 'success') {
                form.reset();
                successMessage.style.display = 'block';
                errorMessage.style.display = 'none';
                form.classList.remove('was-validated');
            } else {
                successMessage.style.display = 'none';
                errorMessage.style.display = 'block';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            successMessage.style.display = 'none';
            errorMessage.style.display = 'block';
        });
    }, false);

    // Smooth scroll
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();

            const targetId = this.getAttribute('href');
            if (targetId === '#') return;

            const targetElement = document.querySelector(targetId);
            if (targetElement) {
                targetElement.scrollIntoView({
                    behavior: 'smooth'
                });
            }
        });
    });

    // Navbar shrink on scroll
    window.addEventListener('scroll', function() {
        const navbar = document.querySelector('.navbar');
        if (window.scrollY > 50) {
            navbar.classList.add('shadow');
            navbar.style.padding = '10px 0';
        } else {
            navbar.classList.remove('shadow');
            navbar.style.padding = '';
        }
    });
})();

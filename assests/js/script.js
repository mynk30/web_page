
document.addEventListener('DOMContentLoaded', function() {
    const monthlyBtn = document.getElementById('monthly-billing');
    const annualBtn = document.getElementById('annual-billing');
    const monthlyPrices = document.querySelectorAll('.monthly-price');
    const annualPrices = document.querySelectorAll('.annual-price');
    
    monthlyBtn.addEventListener('click', function() {
        monthlyBtn.classList.add('active');
        annualBtn.classList.remove('active');
        
        monthlyPrices.forEach(price => price.style.display = 'inline');
        annualPrices.forEach(price => price.style.display = 'none');
    });
    
    annualBtn.addEventListener('click', function() {
        annualBtn.classList.add('active');
        monthlyBtn.classList.remove('active');
        
        annualPrices.forEach(price => price.style.display = 'inline');
        monthlyPrices.forEach(price => price.style.display = 'none');
    });
});

// testimonial1

document.addEventListener('DOMContentLoaded', function () {
    const container = document.getElementById('testimonialContainer');
    const dots = document.querySelectorAll('.dot');
    const totalSlides = dots.length;
    let currentIndex = 0;

    function goToSlide(index) {
        container.style.transform = `translateX(-${index * 100}%)`;
        dots.forEach(dot => dot.classList.remove('active'));
        dots[index].classList.add('active');
        currentIndex = index;
    }

    // Dot click logic
    dots.forEach(dot => {
        dot.addEventListener('click', function () {
            goToSlide(parseInt(this.getAttribute('data-index')));
        });
    });

    // Auto slide every 5 seconds
    setInterval(() => {
        let nextIndex = (currentIndex + 1) % totalSlides;
        goToSlide(nextIndex);
    }, 5000); // 5000ms = 5 seconds
});


document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('common-form');
  
    form.addEventListener('submit', function (e) {
      e.preventDefault();
  
      const name = form.elements['name'].value.trim();
      const email = form.elements['email'].value.trim();
      const message = form.elements['message'].value.trim();
  
      let hasError = false;
  
      // Reset styles
      ['name', 'email', 'message'].forEach(field => {
        form.elements[field].style.border = '';
      });
  
      if (!name) {
        form.elements['name'].style.border = '1px solid red';
        hasError = true;
      }
  
      if (!email || !isValidEmail(email)) {
        form.elements['email'].style.border = '1px solid red';
        hasError = true;
      }
  
      if (!message) {
        form.elements['message'].style.border = '1px solid red';
        hasError = true;
      }
  
      if (hasError) {
        alert('Please fill all required fields correctly.');
        return;
      }
  
      alert('Form submitted successfully!');
      form.reset();
    });
  
    function isValidEmail(email) {
      const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      return re.test(email);
    }
  });
  
  

//  TDS  ------------------------------------------------------------------------

function toggleFAQ(element) {
    const answer = element.nextElementSibling;
    const icon = element.querySelector('span');
    
    if (answer.classList.contains('show')) {
        answer.classList.remove('show');
        icon.textContent = '+';
    } else {
        answer.classList.add('show');
        icon.textContent = '-';
    }
}

function calculateTDS() {
    const paymentType = document.getElementById('paymentType').value;
    const amount = parseFloat(document.getElementById('amount').value);
    
    if (isNaN(amount)){
        alert('Please enter a valid amount');
        return;
    }    
    let rate = 0;
    let threshold = 0;
    
    switch(paymentType) {
        case '194':
            rate = 10;
            threshold = 5000;
            break;
        case '194A':
            rate = 10;
            threshold = 40000;
            break;
        case '194C':
            rate = 1; // Assuming individual/HUF
            threshold = 30000;
            break;
        case '194H':
            rate = 5;
            threshold = 15000;
            break;
        case '194I':
            rate = 2; // Assuming land/building
            threshold = 240000;
            break;
        case '194J':
            rate = 10;
            threshold = 30000;
            break;
    }
    
    let tds = 0;
    if (amount > threshold) {
        tds = (amount * rate) / 100;
    }
    
    const netPayment = amount - tds;
    
    document.getElementById('tdsAmount').innerHTML = `<strong>TDS Amount:</strong> ₹${tds.toFixed(2)} (${rate}%)`;
    document.getElementById('netPayment').innerHTML = `<strong>Net Payment:</strong> ₹${netPayment.toFixed(2)}`;
    
    document.getElementById('result').style.display = 'block';
}


// second-form  --------------------------------------------------------------


(function() {
    'use strict';

    const form = document.getElementById('ServiceForm');

    form.addEventListener('submit', function(event) {
        event.preventDefault(); // रोक दो default submit को

        if (!form.checkValidity()) {
            event.stopPropagation();
            form.classList.add('was-validated');
            return;
        }

        // AJAX से form submit करेंगे
        const formData = new FormData(form);

        fetch('./php/insert.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            console.log(data);

            // Success modal show करो
            const successModal = new bootstrap.Modal(document.getElementById('successModal'));
            successModal.show();

            // Form reset कर दो
            form.reset();
            form.classList.remove('was-validated');
        })
        .catch(error => {
            console.error('Error:', error);
            alert('There was an error submitting the form.');
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

// --------------------------------User Panel--------------------------------------

        // Add interactivity to the user panel
        document.addEventListener('DOMContentLoaded', function() {
            // Highlight active menu item
            const menuItems = document.querySelectorAll('.menu-item');
            menuItems.forEach(item => {
                item.addEventListener('click', function() {
                    menuItems.forEach(i => i.classList.remove('active'));
                    this.classList.add('active');
                });
            });
            
            // Simulate login functionality
            const userInfo = document.querySelector('.user-info');
            userInfo.addEventListener('click', function() {
                alert('User profile options would appear here');
            });
        });
// --------------------------login -------------------------------------

function showForm(formId) {
    document.querySelectorAll(".form-box").forEach(f => f.classList.remove("active"));
    const el = document.getElementById(formId);
    if (el) el.classList.add("active");
}

document.addEventListener('DOMContentLoaded', () => {
    const loginBtn = document.getElementById('login-button');
    if (loginBtn) {
        loginBtn.addEventListener('click', () => showForm('login-form'));
    }
});
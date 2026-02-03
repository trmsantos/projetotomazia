
document.addEventListener('DOMContentLoaded', function() {
    
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    const observerOptions = {
        threshold: 0.1, 
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('fade-in-visible');
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);

    document.querySelectorAll('.fade-in-on-scroll').forEach(element => {
        observer.observe(element);
    });

    document.querySelectorAll('.card, .form-container, section').forEach(element => {
        element.classList.add('fade-in-on-scroll');
        observer.observe(element);
    });

    const registrationForm = document.getElementById('registrationForm');
    if (registrationForm) {
        registrationForm.addEventListener('submit', function(e) {
            const termsCheckbox = document.getElementById('termos');
            const termsError = document.getElementById('termsError');
            
            if (termsCheckbox && !termsCheckbox.checked) {
                e.preventDefault();
                if (termsError) {
                    termsError.style.display = 'block';
                }
                termsCheckbox.focus();
                return false;
            } else {
                if (termsError) {
                    termsError.style.display = 'none';
                }
            }
        });

        const termsCheckbox = document.getElementById('termos');
        if (termsCheckbox) {
            termsCheckbox.addEventListener('change', function() {
                const termsError = document.getElementById('termsError');
                if (this.checked && termsError) {
                    termsError.style.display = 'none';
                }
            });
        }
    }
});

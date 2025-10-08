// Bar da Tomazia - Main JavaScript

// Smooth Scrolling for anchor links
document.addEventListener('DOMContentLoaded', function() {
    
    // Smooth scrolling implementation
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

    // Fade-in animations using IntersectionObserver
    const observerOptions = {
        threshold: 0.1, // Trigger when 10% of the element is visible
        rootMargin: '0px 0px -50px 0px' // Trigger slightly before element enters viewport
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('fade-in-visible');
                // Optional: stop observing after animation triggers once
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);

    // Observe all elements with fade-in-on-scroll class
    document.querySelectorAll('.fade-in-on-scroll').forEach(element => {
        observer.observe(element);
    });

    // Observe common elements that should fade in (cards, sections, etc.)
    document.querySelectorAll('.card, .form-container, section').forEach(element => {
        element.classList.add('fade-in-on-scroll');
        observer.observe(element);
    });
});

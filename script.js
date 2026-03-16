document.addEventListener('DOMContentLoaded', function() {
            
    // --- Mobile Menu Toggle ---
    const mobileMenuButton = document.getElementById('mobile-menu-button');
    const mobileMenu = document.getElementById('mobile-menu');
    mobileMenuButton.addEventListener('click', () => {
        mobileMenu.classList.toggle('hidden');
    });
    // Close mobile menu when a link is clicked
    mobileMenu.querySelectorAll('a').forEach(link => {
        link.addEventListener('click', () => mobileMenu.classList.add('hidden'));
    });

    // --- Header Background on Scroll ---
    const header = document.getElementById('header');
    window.addEventListener('scroll', () => {
        if (window.scrollY > 50) {
            header.classList.add('bg-black/80');
            header.classList.remove('bg-black/30');
        } else {
            header.classList.remove('bg-black/80');
            header.classList.add('bg-black/30');
        }
    });

    // --- Set Current Year in Footer ---
    document.getElementById('current-year').textContent = new Date().getFullYear();

    // --- Contact Form Validation ---
    const form = document.getElementById('contact-form');
    if (form) {
        form.addEventListener('submit', function(event) {
            event.preventDefault();
            let isValid = true;

            // Fields to validate
            const name = document.getElementById('name');
            const email = document.getElementById('email');
            const service = document.getElementById('service');
            const message = document.getElementById('message');

            // Helper to show/hide errors
            const toggleError = (field, show) => {
                const errorMsg = field.nextElementSibling;
                if (show) {
                    errorMsg.classList.remove('hidden');
                    field.classList.add('border-red-500');
                    isValid = false;
                } else {
                    errorMsg.classList.add('hidden');
                    field.classList.remove('border-red-500');
                }
            };

            // Validate Name
            toggleError(name, name.value.trim() === '');
            
            // Validate Email
            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            toggleError(email, !emailPattern.test(email.value));
            
            // Validate Service
            toggleError(service, service.value === '');
            
            // Validate Message
            toggleError(message, message.value.trim() === '');
            
            if (isValid) {
                const formData = {
                    name: name.value,
                    email: email.value,
                    service: service.value,
                    message: message.value
                };

                fetch('http://localhost:3000/send-email', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(formData)
                })
                .then(response => response.text())
                .then(data => {
                    console.log(data);
                    document.getElementById('form-success').classList.remove('hidden');
                    form.reset();
                    setTimeout(() => {
                        document.getElementById('form-success').classList.add('hidden');
                    }, 5000);
                })
                .catch(error => {
                    console.error('Error:', error);
                    // Optionally, show an error message to the user
                });
            }
        });
    }

    // --- Stats Counter Animation ---
    const statsCounter = document.getElementById('stats-counter');
    if (statsCounter) {
        const animateCountUp = (el) => {
            const goal = parseInt(el.dataset.goal, 10);
            const duration = 1500; // 1.5 seconds
            let startTimestamp = null;

            const step = (timestamp) => {
                if (!startTimestamp) startTimestamp = timestamp;
                const progress = Math.min((timestamp - startTimestamp) / duration, 1);
                el.textContent = Math.floor(progress * goal);
                if (progress < 1) {
                    window.requestAnimationFrame(step);
                } else {
                    el.textContent = goal + "+"; // Add the '+' at the end
                }
            };
            window.requestAnimationFrame(step);
        };

        const observer = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const counters = entry.target.querySelectorAll('.stat-number');
                    counters.forEach(counter => {
                        animateCountUp(counter);
                    });
                    observer.unobserve(entry.target); // Animate only once
                }
            });
        }, { threshold: 0.1 }); // Start when 10% of the element is visible

        observer.observe(statsCounter);
    }

    // --- Testimonial Slider ---
    const sliderContainer = document.querySelector('#testimonials .relative');
    const slider = document.getElementById('testimonial-slider');
    const prevBtn = document.getElementById('prev-btn');
    const nextBtn = document.getElementById('next-btn');
    
    if (slider && prevBtn && nextBtn && sliderContainer) {
        const testimonials = document.querySelectorAll('.testimonial-card');
        const testimonialCount = testimonials.length;
        let currentIndex = 0;
        let autoSlideInterval;

        function updateSliderPosition() {
            slider.style.transform = `translateX(-${currentIndex * 100}%)`;
        }

        function nextSlide() {
            currentIndex = (currentIndex + 1) % testimonialCount;
            updateSliderPosition();
        }

        function prevSlide() {
            currentIndex = (currentIndex - 1 + testimonialCount) % testimonialCount;
            updateSliderPosition();
        }

        function startAutoSlide() {
            stopAutoSlide(); // Prevent multiple intervals
            autoSlideInterval = setInterval(nextSlide, 5000); // Auto-slide every 5 seconds
        }

        function stopAutoSlide() {
            clearInterval(autoSlideInterval);
        }

        nextBtn.addEventListener('click', () => {
            nextSlide();
            startAutoSlide(); // Reset timer on manual navigation
        });

        prevBtn.addEventListener('click', () => {
            prevSlide();
            startAutoSlide(); // Reset timer on manual navigation
        });
        
        // Pause slider on hover
        sliderContainer.addEventListener('mouseenter', stopAutoSlide);
        sliderContainer.addEventListener('mouseleave', startAutoSlide);
        
        // Initialize
        updateSliderPosition();
        startAutoSlide();
    }
});
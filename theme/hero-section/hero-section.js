/* clean-hero.js */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize hero section enhancements
    initHeroSection();

    function initHeroSection() {
        // Add parallax effect to background elements
        const heroSection = document.querySelector('.hero-section');
        if (!heroSection) return;

        // Mouse movement parallax effect
        heroSection.addEventListener('mousemove', function(e) {
            const movementFactor = 15;
            
            // Get mouse position as percentage of the screen
            const mouseX = e.clientX / window.innerWidth;
            const mouseY = e.clientY / window.innerHeight;
            
            // Calculate movements based on mouse position
            const moveX = (mouseX - 0.5) * movementFactor;
            const moveY = (mouseY - 0.5) * movementFactor;
            
            // Move yellow circle
            const yellowCircle = document.querySelector('.hero-bg-yellow');
            if (yellowCircle) {
                yellowCircle.style.transform = `translate(${moveX * -1}px, ${moveY * -1}px)`;
            }
            
            // Move blue circle
            const blueCircle = document.querySelector('.hero-bg-blue');
            if (blueCircle) {
                blueCircle.style.transform = `translate(${moveX}px, ${moveY}px)`;
            }
            
            // Move floating icons with different factors
            const floatingIcons = document.querySelectorAll('.floating-icon');
            floatingIcons.forEach((icon, index) => {
                const factor = (index + 1) * 1.5;
                icon.style.transform = `translateY(${Math.sin(Date.now() / 1000 + index) * 10}px) translate(${moveX * factor}px, ${moveY * factor}px)`;
            });
        });

        // Add type writer effect to the hero title if enabled
        const heroTitle = document.querySelector('.hero-title[data-typewriter="true"]');
        if (heroTitle) {
            const originalText = heroTitle.textContent;
            heroTitle.textContent = '';
            
            let charIndex = 0;
            const typeInterval = setInterval(() => {
                if (charIndex < originalText.length) {
                    heroTitle.textContent += originalText.charAt(charIndex);
                    charIndex++;
                } else {
                    clearInterval(typeInterval);
                }
            }, 50);
        }
        
        // Add scroll indicator animation
        const scrollIndicator = document.querySelector('.scroll-indicator');
        if (scrollIndicator) {
            window.addEventListener('scroll', function() {
                if (window.scrollY > 100) {
                    scrollIndicator.classList.add('fade-out');
                } else {
                    scrollIndicator.classList.remove('fade-out');
                }
            });
        }
        
        // Add subtle animation to the illustration
        const illustration = document.querySelector('.hero-illustration img');
        if (illustration) {
            illustration.style.transition = 'transform 0.5s ease';
            
            // Slight zoom on hover
            illustration.addEventListener('mouseenter', function() {
                this.style.transform = 'scale(1.03)';
            });
            
            illustration.addEventListener('mouseleave', function() {
                this.style.transform = 'scale(1)';
            });
        }
        
        // Button hover animation
        const buttons = document.querySelectorAll('.hero-button');
        buttons.forEach(button => {
            button.addEventListener('mouseenter', function() {
                this.classList.add('button-hover');
            });
            
            button.addEventListener('mouseleave', function() {
                this.classList.remove('button-hover');
            });
        });
        
        // Animate words in the hero description if needed
        const heroDescription = document.querySelector('.hero-description[data-word-animation="true"]');
        if (heroDescription) {
            const words = heroDescription.textContent.split(' ');
            heroDescription.innerHTML = '';
            
            words.forEach((word, index) => {
                const span = document.createElement('span');
                span.textContent = word + ' ';
                span.style.opacity = '0';
                span.style.transform = 'translateY(10px)';
                span.style.display = 'inline-block';
                span.style.transition = 'all 0.3s ease';
                span.style.transitionDelay = `${0.03 * index}s`;
                
                heroDescription.appendChild(span);
                
                // Trigger animation after a small delay
                setTimeout(() => {
                    span.style.opacity = '1';
                    span.style.transform = 'translateY(0)';
                }, 500);
            });
        }
    }
});
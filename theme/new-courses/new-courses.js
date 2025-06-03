// new-courses.js - Final JavaScript with improved card interactions

document.addEventListener('DOMContentLoaded', function() {
    // Handle course image loading errors
    const courseImages = document.querySelectorAll('.newly-added-courses-section .course-image img');
    courseImages.forEach(img => {
        img.addEventListener('error', function() {
            // Replace with default image if loading fails
            this.src = M.cfg.wwwroot + '/theme/boost/pix/course-default.jpg';
        });
    });

    // Enhance the clickable cards behavior
    const courseCards = document.querySelectorAll('.newly-added-courses-section .course-card-link');
    courseCards.forEach(card => {
        // Add focus styles for accessibility
        card.addEventListener('focus', function() {
            this.classList.add('focused');
        });
        
        card.addEventListener('blur', function() {
            this.classList.remove('focused');
        });
        
        // Add keyboard navigation support
        card.addEventListener('keydown', function(e) {
            // Enter or space key
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                this.click();
            }
        });
    });
});

// Add focus styles if not already in CSS
document.head.insertAdjacentHTML('beforeend', `
<style>
.newly-added-courses-section .course-card-link.focused .course-card {
    box-shadow: 0 0 0 2px #3b82f6, 0 5px 15px rgba(0,0,0,0.1);
    outline: none;
}

.newly-added-courses-section .course-card-link:focus {
    outline: none;
}
</style>
`);
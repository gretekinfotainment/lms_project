// Premium JavaScript effects for the Who We Are section
document.addEventListener('DOMContentLoaded', function() {

    // Initialize the feature pillars
    initFeaturePillars();
    
    function initFeaturePillars() {
        const pillarsSection = document.querySelector('.features-pillars-section');
        const featurePillars = document.querySelectorAll('.feature-pillar');
        
        if (!pillarsSection || !featurePillars.length) return;
        
        // Add initial animation to section
        pillarsSection.classList.add('fade-in');
        
        // Add staggered entrance animation to pillars
        featurePillars.forEach((pillar, index) => {
            // Initially hide all pillars
            pillar.classList.add('slide-in');
            
            // Create a slight delay for each pillar
            setTimeout(() => {
                pillar.classList.add('fade-up');
                pillar.classList.remove('slide-in');
            }, 100 + (index * 100)); // Staggered timing
        });
        
        // Create floating animation for icons
        featurePillars.forEach(pillar => {
            const icon = pillar.querySelector('.pillar-icon');
            if (icon) {
                // Add gentle floating animation
                icon.style.animation = `float ${3 + Math.random() * 2}s infinite alternate ease-in-out`;
                
                // Create keyframes for the animation
                const style = document.createElement('style');
                style.textContent = `
                    @keyframes float {
                        0% {
                            transform: translateY(0);
                        }
                        100% {
                            transform: translateY(-10px);
                        }
                    }
                `;
                document.head.appendChild(style);
            }
        });
        
        // Add hover effects to pillars
        featurePillars.forEach(pillar => {
            // Generate unique random shape for each pillar icon on hover
            pillar.addEventListener('mouseenter', function() {
                const icon = this.querySelector('.pillar-icon');
                if (icon) {
                    // Generate a unique random blob shape
                    const borderRadius = `${30 + Math.random() * 30}% ${70 - Math.random() * 30}% ${70 - Math.random() * 30}% ${30 + Math.random() * 30}% / ${30 + Math.random() * 30}% ${30 + Math.random() * 30}% ${70 - Math.random() * 30}% ${70 - Math.random() * 30}%`;
                    icon.style.borderRadius = borderRadius;
                }
                
                // Add glow effect
                this.style.boxShadow = '0 20px 40px rgba(0, 0, 0, 0.3), 0 0 30px rgba(59, 130, 246, 0.2)';
                
                // Determine glow color based on pillar index
                const index = Array.from(featurePillars).indexOf(this);
                let glowColor;
                
                if (index % 3 === 0) {
                    glowColor = 'rgba(245, 158, 11, 0.2)'; // Amber
                } else if (index % 3 === 1) {
                    glowColor = 'rgba(16, 185, 129, 0.2)'; // Green
                } else {
                    glowColor = 'rgba(139, 92, 246, 0.2)'; // Purple
                }
                
                this.style.boxShadow = `0 20px 40px rgba(0, 0, 0, 0.3), 0 0 30px ${glowColor}`;
            });
            
            // Reset on mouse leave
            pillar.addEventListener('mouseleave', function() {
                const icon = this.querySelector('.pillar-icon');
                if (icon) {
                    // Reset to original shape with smooth transition
                    icon.style.borderRadius = '30% 70% 70% 30% / 30% 30% 70% 70%';
                }
                
                // Remove glow effect
                this.style.boxShadow = '0 10px 30px rgba(0, 0, 0, 0.2)';
            });
        });
        
        // Add subtle parallax effect to pillars on mouse move
        pillarsSection.addEventListener('mousemove', function(e) {
            const { left, top, width, height } = this.getBoundingClientRect();
            const x = (e.clientX - left) / width - 0.5;
            const y = (e.clientY - top) / height - 0.5;
            
            featurePillars.forEach((pillar, index) => {
                // Apply subtle movement based on mouse position
                // Different pillars move at slightly different rates for parallax effect
                const offsetFactor = 1 + (index % 3) * 0.5;
                const moveX = x * 10 * offsetFactor;
                const moveY = y * 10 * offsetFactor;
                
                pillar.style.transform = `translateY(-${pillar.classList.contains('fade-up') ? 15 : 0}px) translateX(${moveX}px) translateY(${moveY}px)`;
            });
        });
        
        // Reset pillar positions when mouse leaves the section
        pillarsSection.addEventListener('mouseleave', function() {
            featurePillars.forEach(pillar => {
                pillar.style.transform = pillar.classList.contains('fade-up') ? 'translateY(-15px)' : '';
            });
        });
        
        // Create subtle background grid movement on scroll
        const bgGrid = document.querySelector('.bg-grid');
        if (bgGrid) {
            window.addEventListener('scroll', function() {
                const scrollPosition = window.pageYOffset;
                const offsetY = scrollPosition * 0.03;
                
                bgGrid.style.backgroundPosition = `${offsetY}px ${offsetY}px`;
            });
        }
        
        // Add interaction to background gradient
        const bgGradient = document.querySelector('.bg-gradient');
        if (bgGradient) {
            pillarsSection.addEventListener('mousemove', function(e) {
                const { left, top, width, height } = this.getBoundingClientRect();
                const x = ((e.clientX - left) / width) * 100;
                const y = ((e.clientY - top) / height) * 100;
                
                // Move gradient slightly with mouse
                bgGradient.style.background = `radial-gradient(circle at ${x}% ${y}%, rgba(59, 130, 246, 0.15), rgba(15, 23, 42, 0) 70%)`;
            });
        }
    }

    // Force main content to be full width
    const containers = document.querySelectorAll('#page, #page-content, #region-main-box, #region-main, .container, .card');
    
    containers.forEach(container => {
        container.style.maxWidth = '100%';
        container.style.width = '100%';
        container.style.margin = '0';
        container.style.padding = '0';
        container.style.backgroundColor = 'transparent';
        container.style.border = 'none';
        container.style.boxShadow = 'none';
    });
    
    // Make sure the landing page container takes full width
    const landingPage = document.querySelector('.landing-page');
    if (landingPage) {
        landingPage.style.width = '100%';
        landingPage.style.maxWidth = '100%';
    }

    // Enhanced Premium Who We Are section
    const whoWeAreSection = document.querySelector('.who-we-are-section');
    if (whoWeAreSection) {
        // Create particle effects in the background
        const createParticleEffect = function() {
            const particleContainer = document.createElement('div');
            particleContainer.className = 'particle-container';
            particleContainer.style.position = 'absolute';
            particleContainer.style.top = '0';
            particleContainer.style.left = '0';
            particleContainer.style.width = '100%';
            particleContainer.style.height = '100%';
            particleContainer.style.overflow = 'hidden';
            particleContainer.style.zIndex = '1';
            
            // Create particles
            for (let i = 0; i < 30; i++) {
                const particle = document.createElement('div');
                particle.className = 'particle';
                particle.style.position = 'absolute';
                
                // Random size
                const size = Math.random() * 4 + 1;
                particle.style.width = `${size}px`;
                particle.style.height = `${size}px`;
                
                // Random position
                particle.style.top = `${Math.random() * 100}%`;
                particle.style.left = `${Math.random() * 100}%`;
                
                // Styling
                particle.style.borderRadius = '50%';
                particle.style.backgroundColor = 'rgba(245, 158, 11, 0.1)';
                particle.style.boxShadow = '0 0 10px rgba(245, 158, 11, 0.3)';
                
                // Generate unique animation
                const animDuration = Math.random() * 20 + 10;
                const animDelay = Math.random() * 5;
                
                particle.style.animation = `floatParticle ${animDuration}s ${animDelay}s infinite alternate ease-in-out`;
                
                // Add to container
                particleContainer.appendChild(particle);
            }
            
            // Create keyframe animation
            const style = document.createElement('style');
            style.textContent = `
                @keyframes floatParticle {
                    0% {
                        transform: translate(0, 0);
                        opacity: 0.3;
                    }
                    50% {
                        transform: translate(${Math.random() * 50 - 25}px, ${Math.random() * 50 - 25}px);
                        opacity: 0.8;
                    }
                    100% {
                        transform: translate(0, 0);
                        opacity: 0.3;
                    }
                }
            `;
            document.head.appendChild(style);
            
            // Insert particles at the beginning of the section
            whoWeAreSection.insertBefore(particleContainer, whoWeAreSection.firstChild);
        };
        
        // Create mouse follow effect for image
        const setupMouseFollowEffect = function() {
            const imageWrapper = document.querySelector('.image-wrapper');
            if (imageWrapper) {
                whoWeAreSection.addEventListener('mousemove', function(e) {
                    // Get the section's position
                    const rect = whoWeAreSection.getBoundingClientRect();
                    
                    // Calculate normalized mouse position within the section
                    const xPos = (e.clientX - rect.left) / rect.width - 0.5;
                    const yPos = (e.clientY - rect.top) / rect.height - 0.5;
                    
                    // Apply rotation based on mouse position
                    imageWrapper.style.transform = `perspective(1000px) rotateY(${xPos * 10}deg) rotateX(${-yPos * 10}deg)`;
                    
                    // Also move the floating elements slightly
                    const elem1 = document.querySelector('.elem-1');
                    const elem2 = document.querySelector('.elem-2');
                    
                    if (elem1) {
                        elem1.style.transform = `translate(${xPos * 15}px, ${yPos * 15}px) rotate(45deg)`;
                    }
                    
                    if (elem2) {
                        elem2.style.transform = `translate(${-xPos * 10}px, ${-yPos * 10}px) rotate(30deg)`;
                    }
                });
                
                // Reset on mouse leave
                whoWeAreSection.addEventListener('mouseleave', function() {
                    imageWrapper.style.transform = 'perspective(1000px) rotateY(-8deg) rotateX(5deg)';
                    
                    const elem1 = document.querySelector('.elem-1');
                    const elem2 = document.querySelector('.elem-2');
                    
                    if (elem1) {
                        elem1.style.transform = 'rotate(45deg)';
                    }
                    
                    if (elem2) {
                        elem2.style.transform = 'rotate(30deg)';
                    }
                });
            }
        };
        
        // Create text highlight effect
        const setupTextHighlightEffect = function() {
            const highlight = document.querySelector('.highlight');
            if (highlight) {
                // Create a subtle glow effect behind the highlight
                const glowEffect = document.createElement('div');
                glowEffect.className = 'highlight-glow';
                glowEffect.style.position = 'absolute';
                glowEffect.style.top = '-5px';
                glowEffect.style.left = '-5px';
                glowEffect.style.width = 'calc(100% + 10px)';
                glowEffect.style.height = 'calc(100% + 10px)';
                glowEffect.style.background = 'radial-gradient(circle at center, rgba(245, 158, 11, 0.3), rgba(245, 158, 11, 0) 70%)';
                glowEffect.style.filter = 'blur(8px)';
                glowEffect.style.zIndex = '-1';
                glowEffect.style.opacity = '0';
                glowEffect.style.transition = 'opacity 0.8s ease';
                
                // Add glow effect as a child of highlight element
                highlight.style.position = 'relative';
                highlight.appendChild(glowEffect);
                
                // Add hover effect
                highlight.addEventListener('mouseenter', function() {
                    glowEffect.style.opacity = '1';
                });
                
                highlight.addEventListener('mouseleave', function() {
                    glowEffect.style.opacity = '0';
                });
            }
        };
        
        // Staggered animation for experience blocks
        const setupExperienceBlocksAnimation = function() {
            const experienceBlocks = document.querySelectorAll('.exp-block');
            
            if (experienceBlocks.length > 0) {
                // First set all to opacity 0
                experienceBlocks.forEach(block => {
                    block.style.opacity = '0';
                    block.style.transform = 'translateY(30px)';
                    block.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                });
                
                // Create intersection observer
                const observer = new IntersectionObserver((entries, observer) => {
                    entries.forEach((entry, index) => {
                        if (entry.isIntersecting) {
                            // Add staggered delay based on index
                            setTimeout(() => {
                                entry.target.style.opacity = '1';
                                entry.target.style.transform = 'translateY(0)';
                            }, index * 200);
                            
                            // Once animated, no need to observe anymore
                            observer.unobserve(entry.target);
                        }
                    });
                }, {
                    root: null,
                    threshold: 0.2
                });
                
                // Observe each block
                experienceBlocks.forEach(block => {
                    observer.observe(block);
                });
            }
        };
        
        // Setup animated number counters for experience blocks
        const setupCounterAnimations = function() {
            const expIcons = document.querySelectorAll('.exp-icon');
            
            expIcons.forEach(icon => {
                // Add a subtle pulsing animation
                icon.style.animation = 'pulseIcon 2s infinite alternate ease-in-out';
                
                // Create keyframe animation
                const style = document.createElement('style');
                style.textContent = `
                    @keyframes pulseIcon {
                        0% {
                            transform: scale(1);
                            box-shadow: 0 0 0 rgba(245, 158, 11, 0.4);
                        }
                        100% {
                            transform: scale(1.05);
                            box-shadow: 0 0 15px rgba(245, 158, 11, 0.6);
                        }
                    }
                `;
                document.head.appendChild(style);
            });
        };
        
        // Create a scrolling grid animation effect
        const setupGridAnimation = function() {
            const bgGrid = document.querySelector('.bg-grid');
            if (bgGrid) {
                // Make grid move subtly on scroll
                window.addEventListener('scroll', function() {
                    const scrollPosition = window.pageYOffset;
                    bgGrid.style.backgroundPosition = `${scrollPosition * 0.05}px ${scrollPosition * 0.05}px`;
                });
            }
        };
        
        // Initialize all effects for premium section
        createParticleEffect();
        setupMouseFollowEffect();
        setupTextHighlightEffect();
        setupExperienceBlocksAnimation();
        setupCounterAnimations();
        setupGridAnimation();
        
        // Create a premium button hover effect
        const premiumButton = document.querySelector('.premium-button');
        if (premiumButton) {
            premiumButton.addEventListener('mouseenter', function() {
                this.style.color = '#0f172a';
                this.style.boxShadow = '0 15px 30px rgba(245, 158, 11, 0.3)';
                this.style.transform = 'translateY(-5px)';
                
                // Get the before element for the background
                const buttonStyle = document.createElement('style');
                buttonStyle.textContent = `
                    .premium-button::before {
                        transform: scaleX(1) !important;
                        transform-origin: left !important;
                    }
                    
                    .premium-button .button-icon {
                        background: rgba(15, 23, 42, 0.2) !important;
                        transform: translateX(5px) !important;
                    }
                `;
                document.head.appendChild(buttonStyle);
            });
            
            premiumButton.addEventListener('mouseleave', function() {
                this.style.color = '#f8fafc';
                this.style.boxShadow = '0 10px 25px rgba(0, 0, 0, 0.2)';
                this.style.transform = '';
                
                // Reset the before element
                const buttonStyle = document.createElement('style');
                buttonStyle.textContent = `
                    .premium-button::before {
                        transform: scaleX(0) !important;
                        transform-origin: right !important;
                    }
                    
                    .premium-button .button-icon {
                        background: rgba(245, 158, 11, 0.2) !important;
                        transform: translateX(0) !important;
                    }
                `;
                document.head.appendChild(buttonStyle);
            });
        }
    }

    // Animation on scroll functionality
    const animateOnScroll = function() {
        const elements = document.querySelectorAll('.section-heading, .card-modern, .service-card, .team-card, .benefits-image, .partners-card, .text-area, .image-container, .premium-button, .section-tag, .experience-blocks');
        
        elements.forEach(element => {
            const elementPosition = element.getBoundingClientRect().top;
            const windowHeight = window.innerHeight;
            
            // Add animation classes when elements come into view
            if (elementPosition < windowHeight * 0.85) {
                if (element.classList.contains('section-heading')) {
                    element.classList.add('fade-up');
                } else if (element.classList.contains('text-area')) {
                    element.classList.add('fade-left');
                } else if (element.classList.contains('image-container')) {
                    element.classList.add('fade-right');
                } else if (element.classList.contains('premium-button')) {
                    setTimeout(() => {
                        element.classList.add('fade-up');
                    }, 300);
                } else if (element.classList.contains('section-tag')) {
                    element.classList.add('fade-up');
                } else if (element.classList.contains('experience-blocks')) {
                    element.classList.add('fade-up');
                } else if (element.classList.contains('services-grid')) {
                    const serviceCards = element.querySelectorAll('.service-card');
                    serviceCards.forEach((card, index) => {
                        setTimeout(() => {
                            card.classList.add('fade-up');
                        }, index * 100);
                    });
                } else if (element.classList.contains('team-showcase')) {
                    const teamCards = element.querySelectorAll('.team-card');
                    teamCards.forEach((card, index) => {
                        setTimeout(() => {
                            card.classList.add('fade-up');
                        }, index * 100);
                    });
                } else if (element.classList.contains('partners-grid')) {
                    const partnerLogos = element.querySelectorAll('img');
                    partnerLogos.forEach((logo, index) => {
                        setTimeout(() => {
                            logo.classList.add('fade-in');
                        }, index * 100);
                    });
                } else {
                    element.classList.add('fade-up');
                }
            }
        });
    };
    
    // Listen for scroll events
    window.addEventListener('scroll', animateOnScroll);
    
    // Trigger once on initial load
    setTimeout(animateOnScroll, 500);
    
    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            
            const targetId = this.getAttribute('href');
            if (targetId === '#') return;
            
            const targetElement = document.querySelector(targetId);
            if (targetElement) {
                window.scrollTo({
                    top: targetElement.offsetTop,
                    behavior: 'smooth'
                });
            }
        });
    });
    
    // Parallax effect for hero section
    const heroSection = document.querySelector('.hero-section');
    if (heroSection) {
        window.addEventListener('scroll', function() {
            const scrollPosition = window.pageYOffset;
            heroSection.style.backgroundPositionY = `${scrollPosition * 0.4}px`;
        });
        
        // Subtle mouse movement effect
        heroSection.addEventListener('mousemove', function(e) {
            const moveX = (e.clientX - window.innerWidth / 2) * 0.01;
            const moveY = (e.clientY - window.innerHeight / 2) * 0.01;
            
            heroSection.style.backgroundPositionX = `calc(50% + ${moveX}px)`;
            heroSection.style.backgroundPositionY = `calc(50% + ${moveY}px)`;
        });
    }
    
    // Dynamic shadow for cards on hover
    const cards = document.querySelectorAll('.card-modern, .service-card, .team-card');
    cards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-10px)';
            this.style.boxShadow = '0 30px 60px rgba(0, 0, 0, 0.3)';
            this.style.borderColor = 'rgba(245, 158, 11, 0.3)';
            
            // Ensure the background stays dark
            this.style.backgroundColor = '#1e293b';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = '0 20px 40px rgba(0, 0, 0, 0.2)';
            this.style.borderColor = 'rgba(255, 255, 255, 0.05)';
            
            // Ensure the background stays dark
            this.style.backgroundColor = '#1e293b';
        });
    });
    
    // Enhanced hover effects for buttons
    const buttons = document.querySelectorAll('.hero-cta, .login-button, .store-button');
    buttons.forEach(button => {
        button.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
            
            if (this.classList.contains('store-button')) {
                this.style.backgroundColor = 'rgba(255, 255, 255, 0.1)';
                this.style.color = '#f8fafc';
                this.style.borderColor = '#f59e0b';
            } else {
                this.style.boxShadow = '0 10px 25px rgba(245, 158, 11, 0.4)';
            }
        });
        
        button.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            
            if (this.classList.contains('store-button')) {
                this.style.backgroundColor = 'rgba(255, 255, 255, 0.05)';
                this.style.color = '#94a3b8';
                this.style.borderColor = 'rgba(255, 255, 255, 0.05)';
            } else {
                this.style.boxShadow = '0 4px 15px rgba(245, 158, 11, 0.3)';
            }
        });
    });
    
    // Initialize animation for hero elements on page load
    const initHeroAnimations = function() {
        const heroContent = document.querySelector('.hero-content-left');
        const loginButton = document.querySelector('.login-button');
        const siteLogo = document.querySelector('.site-logo');
        
        if (siteLogo) {
            siteLogo.classList.add('fade-in');
        }
        
        if (heroContent) {
            setTimeout(() => {
                heroContent.classList.add('fade-in-up');
            }, 300);
        }
        
        if (loginButton) {
            setTimeout(() => {
                loginButton.classList.add('fade-in-up');
            }, 600);
        }
    };

    
    // Trigger hero animations
    window.addEventListener('load', initHeroAnimations);
    
    // Add 3D tilt effect to experience blocks
    const expBlocks = document.querySelectorAll('.exp-block');
    expBlocks.forEach(block => {
        block.addEventListener('mousemove', function(e) {
            const blockRect = this.getBoundingClientRect();
            
            // Calculate mouse position relative to the card
            const x = e.clientX - blockRect.left;
            const y = e.clientY - blockRect.top;
            
            // Calculate rotation values (limited to a small range)
            const rotateY = ((x / blockRect.width) - 0.5) * 5; // -2.5 to 2.5 degrees
            const rotateX = ((y / blockRect.height) - 0.5) * -5; // -2.5 to 2.5 degrees
            
            // Apply the 3D transform
            this.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) translateZ(10px) translateY(-10px)`;
            
            // Add a dynamic shadow based on the rotation
            const shadowOffsetX = rotateY * 2;
            const shadowOffsetY = rotateX * -2;
            this.style.boxShadow = `${shadowOffsetX}px ${shadowOffsetY}px 30px rgba(0, 0, 0, 0.3)`;
            
            // Highlight the icon when hovered
            const icon = this.querySelector('.exp-icon');
            if (icon) {
                icon.style.transform = 'scale(1.1)';
                icon.style.background = 'rgba(245, 158, 11, 0.2)';
                icon.style.boxShadow = '0 0 20px rgba(245, 158, 11, 0.4)';
            }
        });
        
        block.addEventListener('mouseleave', function() {
            // Reset transform and shadow on mouse leave
            this.style.transform = '';
            this.style.boxShadow = '';
            
            // Reset icon
            const icon = this.querySelector('.exp-icon');
            if (icon) {
                icon.style.transform = '';
                icon.style.background = '';
                icon.style.boxShadow = '';
            }
        });
    });
});
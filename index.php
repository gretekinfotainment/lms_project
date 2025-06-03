<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Moodle frontpage.
 *
 * @package    core
 * @copyright  1999 onwards Martin Dougiamas (http://dougiamas.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

if (!file_exists('./config.php')) {
    header('Location: install.php');
    die;
}

require_once('config.php');
require_once($CFG->dirroot .'/course/lib.php');
require_once($CFG->libdir .'/filelib.php');

redirect_if_major_upgrade_required();

// Redirect logged-in users to homepage if required.
$redirect = optional_param('redirect', 1, PARAM_BOOL);

$urlparams = array();
if (!empty($CFG->defaulthomepage) &&
        ($CFG->defaulthomepage == HOMEPAGE_MY || $CFG->defaulthomepage == HOMEPAGE_MYCOURSES) &&
        $redirect === 0
) {
    $urlparams['redirect'] = 0;
}
$PAGE->set_url('/', $urlparams);
$PAGE->set_pagelayout('frontpage');
$PAGE->add_body_class('limitedwidth');
$PAGE->set_other_editing_capability('moodle/course:update');
$PAGE->set_other_editing_capability('moodle/course:manageactivities');
$PAGE->set_other_editing_capability('moodle/course:activityvisibility');

// Prevent caching of this page to stop confusion when changing page after making AJAX changes.
$PAGE->set_cacheable(false);

// Check if user is logged in - if not, show the storytelling design
$isLoggedIn = isloggedin() && !isguestuser();
if (!$isLoggedIn) {
    // Include custom CSS and JavaScript for non-logged-in users
    $PAGE->requires->css(new moodle_url('/theme/custom_frontpage.css'));
    $PAGE->requires->js(new moodle_url('/theme/custom_frontpage.js'));
} else {
    // Include updated CSS and JS for logged-in users
    $PAGE->requires->css(new moodle_url('/theme/hero-section/hero-section.css'));
    $PAGE->requires->js(new moodle_url('/theme/hero-section/hero-section.js'));
    $PAGE->requires->css(new moodle_url('/theme/infopanel/infopanel.css'));
    $PAGE->requires->js(new moodle_url('/theme/infopanel/infopanel.js'));
    $PAGE->requires->css(new moodle_url('/theme/achievements/achievements.css'));
    $PAGE->requires->js(new moodle_url('/theme/achievements/achievements.js'));
    $PAGE->requires->css(new moodle_url('/theme/new-courses/new-courses.css'));
    $PAGE->requires->js(new moodle_url('/theme/new-courses/new-courses.js'));
}

require_course_login($SITE);

$hasmaintenanceaccess = has_capability('moodle/site:maintenanceaccess', context_system::instance());

// If the site is currently under maintenance, then print a message.
if (!empty($CFG->maintenance_enabled) and !$hasmaintenanceaccess) {
    print_maintenance_message();
}

$hassiteconfig = has_capability('moodle/site:config', context_system::instance());

if ($hassiteconfig && moodle_needs_upgrading()) {
    redirect($CFG->wwwroot .'/'. $CFG->admin .'/index.php');
}

// If site registration needs updating, redirect.
\core\hub\registration::registration_reminder('/index.php');

$homepage = get_home_page();
if ($homepage != HOMEPAGE_SITE) {
    if (optional_param('setdefaulthome', false, PARAM_BOOL)) {
        set_user_preference('user_home_page_preference', HOMEPAGE_SITE);
    } else if (!empty($CFG->defaulthomepage) && ($CFG->defaulthomepage == HOMEPAGE_MY) && $redirect === 1) {
        redirect($CFG->wwwroot .'/my/');
    } else if (!empty($CFG->defaulthomepage) && ($CFG->defaulthomepage == HOMEPAGE_MYCOURSES) && $redirect === 1) {
        redirect($CFG->wwwroot .'/my/courses.php');
    } else if ($homepage == HOMEPAGE_URL) {
        redirect(get_default_home_page_url());
    } else if (!empty($CFG->defaulthomepage) && ($CFG->defaulthomepage == HOMEPAGE_USER)) {
        $frontpagenode = $PAGE->settingsnav->find('frontpage', null);
        if ($frontpagenode) {
            $frontpagenode->add(
                get_string('makethismyhome'),
                new moodle_url('/', array('setdefaulthome' => true)),
                navigation_node::TYPE_SETTING);
        } else {
            $frontpagenode = $PAGE->settingsnav->add(get_string('frontpagesettings'), null, navigation_node::TYPE_SETTING, null);
            $frontpagenode->force_open();
            $frontpagenode->add(get_string('makethismyhome'),
                new moodle_url('/', array('setdefaulthome' => true)),
                navigation_node::TYPE_SETTING);
        }
    }
}

// Trigger event.
course_view(context_course::instance(SITEID));

$PAGE->set_pagetype('site-index');
$PAGE->set_docs_path('');
$editing = $PAGE->user_is_editing();
$PAGE->set_title(get_string('home'));
$PAGE->set_heading($SITE->fullname);
$PAGE->set_secondary_active_tab('coursehome');

$siteformatoptions = course_get_format($SITE)->get_format_options();
$modinfo = get_fast_modinfo($SITE);
$modnamesused = $modinfo->get_used_module_names();

// Initialize course editor for activities in block aside.
include_course_ajax($SITE, $modnamesused);

$courserenderer = $PAGE->get_renderer('core', 'course');

if ($hassiteconfig) {
    $editurl = new moodle_url('/course/view.php', ['id' => SITEID, 'sesskey' => sesskey()]);
    $editbutton = $OUTPUT->edit_button($editurl);
    $PAGE->set_button($editbutton);
}

if (!$isLoggedIn) {
    // Output modernized landing page for non-logged in users
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>' . format_string($SITE->fullname) . '</title>
        <!-- Simple Favicon - Just this one line! -->
        <link rel="icon" type="image/x-icon" href="' . $CFG->wwwroot . '/theme/boost/pix/new-yellow-board.ico">
        <link rel="stylesheet" href="' . $CFG->wwwroot . '/theme/custom_frontpage.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap">
        <script src="' . $CFG->wwwroot . '/theme/custom_frontpage.js" defer></script>
    </head>
    <body>
    <div class="landing-page">
        <!-- Hero Section -->
        <div class="hero-section">
            <!-- Logo with dedicated container and proper spacing -->
            <div class="site-logo-container">
                <div class="site-logo">
                    <img src="' . $CFG->wwwroot . '/theme/boost/pix/logo1.png" alt="E-Learning Solutions Logo">
                </div>
            </div>
            
            <!-- Hero content with proper top margin to avoid overlap -->
            <div class="hero-content-wrapper">
                <div class="hero-content-left">
                    <h1>
                        E-LEARNING SOLUTIONS FOR<br>
                        <span class="gradient-text">EDUCATION & BUSINESS</span>
                    </h1>
                    <p>Transform your organization with our state-of-the-art learning platform, designed for maximum engagement and learning outcomes.</p>
                    <a href="login/index.php" class="login-button">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Who We Are Section - PREMIUM VERSION -->
        <div class="section who-we-are-section" id="who-we-are">
            <div class="bg-grid"></div>
            <div class="glow-effect"></div>
            
            <div class="section-container">
                <div class="section-heading">
                    <div class="heading-line"></div>
                    <span class="section-tag">About Us</span>
                    <h2>Who we are</h2>
                </div>
                
                <div class="premium-layout">
                    <div class="content-area">
                        <div class="text-area">
                            <p>Today\'s learners seek relevant, mobile, self-paced, and personalized content needs best met by online learning. It offers flexibility for students, professionals, and homemakers to learn anytime, anywhere. The digital shift has transformed how content is accessed, shared, and discussed.</p>
                            <p>We provide end-to-end online learning solutions, including design, custom development, implementation, training, and support, for institutions and businesses across Europe, Asia, and America. Even if you\'re already using an e-learning platform, you can still benefit from our services.</p>
                            <div class="text-area-accent"></div>
                        </div>
                        
                        <div class="action-area">
                            <a href="#services" class="premium-button">
                                <span class="button-text">Explore Services</span>
                                <span class="button-icon"><i class="fas fa-chevron-right"></i></span>
                            </a>
                        </div>
                    </div>
                    
                    <div class="image-area">
                        <div class="image-container">
                            <div class="image-wrapper">
                                <img src="' . $CFG->wwwroot . '/theme/boost/pix/woman-professional.jpg" alt="Team member">
                                <div class="image-overlay"></div>
                            </div>
                            <div class="floating-element elem-1"></div>
                            <div class="floating-element elem-2"></div>
                            <div class="image-shadow"></div>
                        </div>
                        <div class="decorative-circle"></div>
                        <div class="decorative-line"></div>
                    </div>
                </div>
                
                <div class="experience-blocks">
                    <div class="exp-block">
                        <div class="exp-icon">
                            <i class="fas fa-laptop-code"></i>
                        </div>
                        <h3>Seamless Experience</h3>
                        <p>Cross-platform learning with synchronization across all devices</p>
                    </div>
                    <div class="exp-block">
                        <div class="exp-icon">
                            <i class="fas fa-rocket"></i>
                        </div>
                        <h3>Fast Implementation</h3>
                        <p>Quick deployment with minimal disruption to your operations</p>
                    </div>
                    <div class="exp-block">
                        <div class="exp-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h3>Secure Environment</h3>
                        <p>Enterprise-grade security protocols to protect your data</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Services Section -->
        <div class="section services-section" id="services">
            <div class="section-container">
                <div class="section-heading">
                    <h2>Our Services</h2>
                    <p class="section-subheading">Comprehensive solutions for your educational and training needs</p>
                </div>
                <div class="services-grid">
                    <!-- Service 1 -->
                    <div class="service-card">
                        <div class="service-content">
                            <div class="service-icon">
                                <i class="fas fa-palette"></i>
                            </div>
                            <h3>Theme Design</h3>
                            <p>Modern UI with intuitive navigation for an optimal learning experience. Our designs focus on engagement, accessibility, and responsive layouts for any device.</p>
                        </div>
                    </div>
                    
                    <!-- Service 2 -->
                    <div class="service-card">
                        <div class="service-content">
                            <div class="service-icon">
                                <i class="fas fa-code"></i>
                            </div>
                            <h3>Custom Development</h3>
                            <p>Tailored solutions and plugins to meet your specific educational needs, seamlessly integrated with your existing LMS for maximum impact and efficiency.</p>
                        </div>
                    </div>
                    
                    <!-- Service 3 -->
                    <div class="service-card">
                        <div class="service-content">
                            <div class="service-icon">
                                <i class="fas fa-server"></i>
                            </div>
                            <h3>Hosting</h3>
                            <p>Reliable cloud hosting specifically optimized for e-learning environments. High uptime, excellent performance, and automatic scaling with regular backups.</p>
                        </div>
                    </div>
                    
                    <!-- Service 4 -->
                    <div class="service-card">
                        <div class="service-content">
                            <div class="service-icon">
                                <i class="fas fa-chalkboard-teacher"></i>
                            </div>
                            <h3>Training</h3>
                            <p>Comprehensive training programs for educators and administrators. Learn to maximize platform features and create engaging learning experiences.</p>
                        </div>
                    </div>
                    
                    <!-- Service 5 -->
                    <div class="service-card">
                        <div class="service-content">
                            <div class="service-icon">
                                <i class="fas fa-headset"></i>
                            </div>
                            <h3>Support</h3>
                            <p>Expert technical assistance available 24/7. Our dedicated support team ensures your e-learning platform runs smoothly with minimal disruption.</p>
                        </div>
                    </div>
                    
                    <!-- Service 6 -->
                    <div class="service-card">
                        <div class="service-content">
                            <div class="service-icon">
                                <i class="fas fa-chart-line"></i>
                            </div>
                            <h3>Analytics</h3>
                            <p>Advanced learning analytics to track progress, identify trends, and optimize educational outcomes with actionable insights for continuous improvement.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Feature Pillars - "Why Choose Our Platform?" Section -->
        <div class="features-pillars-section" id="why-choose">
            <div class="pillars-background">
                <div class="bg-gradient"></div>
                <div class="bg-grid"></div>
            </div>
            
            <div class="section-container">
                <div class="section-heading">
                    <h2>Why Choose Our Platform?</h2>
                    <p class="section-subheading">Powerful features designed for exceptional results</p>
                </div>
                
                <div class="pillars-container">
                    <!-- Feature 1 -->
                    <div class="feature-pillar">
                        <div class="pillar-icon">
                            <i class="fas fa-palette"></i>
                        </div>
                        <div class="pillar-content">
                            <h3>Stunning Visual Design</h3>
                            <p>Crafted with elegance and creativity, our designs captivate users at first glance, delivering a memorable experience.</p>
                        </div>
                    </div>
                    
                    <!-- Feature 2 -->
                    <div class="feature-pillar">
                        <div class="pillar-icon">
                            <i class="fas fa-mobile-alt"></i>
                        </div>
                        <div class="pillar-content">
                            <h3>Fully Responsive Layout</h3>
                            <p>No matter the device—desktop, tablet, or smartphone—your site will always look and function beautifully.</p>
                        </div>
                    </div>
                    
                    <!-- Feature 3 -->
                    <div class="feature-pillar">
                        <div class="pillar-icon">
                            <i class="fas fa-headset"></i>
                        </div>
                        <div class="pillar-content">
                            <h3>Expert Support</h3>
                            <p>Our dedicated support team is always ready to assist you, ensuring smooth performance and peace of mind.</p>
                        </div>
                    </div>
                    
                    <!-- Feature 4 -->
                    <div class="feature-pillar">
                        <div class="pillar-icon">
                            <i class="fas fa-sliders-h"></i>
                        </div>
                        <div class="pillar-content">
                            <h3>Dynamic Featured Sliders</h3>
                            <p>Showcase your top content with sleek and interactive sliders that draw attention and drive engagement.</p>
                        </div>
                    </div>
                    
                    <!-- Feature 5 -->
                    <div class="feature-pillar">
                        <div class="pillar-icon">
                            <i class="fas fa-th-large"></i>
                        </div>
                        <div class="pillar-content">
                            <h3>Versatile Page Layouts</h3>
                            <p>From single-page setups to full-fledged multi-page sites, our flexible layouts adapt to your content structure.</p>
                        </div>
                    </div>
                    
                    <!-- Feature 6 -->
                    <div class="feature-pillar">
                        <div class="pillar-icon">
                            <i class="fas fa-globe"></i>
                        </div>
                        <div class="pillar-content">
                            <h3>Cross-Browser Friendly</h3>
                            <p>We ensure full compatibility across all major browsers, maintaining consistency in appearance and behavior.</p>
                        </div>
                    </div>
                    
                    <!-- Feature 7 -->
                    <div class="feature-pillar">
                        <div class="pillar-icon">
                            <i class="fas fa-sliders-h"></i>
                        </div>
                        <div class="pillar-content">
                            <h3>Intuitive Theme Controls</h3>
                            <p>Customize your site effortlessly with a wide range of user-friendly theme settings.</p>
                        </div>
                    </div>
                    
                    <!-- Feature 8 -->
                    <div class="feature-pillar">
                        <div class="pillar-icon">
                            <i class="fas fa-language"></i>
                        </div>
                        <div class="pillar-content">
                            <h3>Multilingual & RTL Ready</h3>
                            <p>Break language barriers with support for multiple languages and right-to-left scripts, expanding your global reach.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Team Section -->
        <div class="section team-section" id="team">
            <div class="section-container">
                <div class="section-heading">
                    <h2>Our Team</h2>
                    <p class="section-subheading">Meet the talented professionals behind our success</p>
                </div>
                
                <div class="team-showcase">
                    <!-- Team member 1 -->
                    <div class="team-card">
                        <div class="team-card-inner">
                            <div class="team-card-front">
                                <div class="team-image-wrapper">
                                    <img src="' . $CFG->wwwroot . '/theme/boost/pix/user1.jpg" alt="G S Gunanidhi">
                                </div>
                                <div class="team-info">
                                    <h3>G S Gunanidhi</h3>
                                    <p>Founder & Chief Visionary Officer (CVO)</p>
                                </div>
                            </div>
                            <div class="team-card-back">
                                <div class="team-bio">
                                    <h3>G S Gunanidhi</h3>
                                    <p class="team-title">Founder & Chief Visionary Officer (CVO)</p>
                                    <p class="team-description">Visionary leader with 15+ years in educational technology, committed to transforming learning experiences through innovation.</p>
                                    <div class="team-social">
                                        <a href="#" class="social-icon"><i class="fab fa-linkedin-in"></i></a>
                                        <a href="#" class="social-icon"><i class="fab fa-twitter"></i></a>
                                        <a href="#" class="social-icon"><i class="fas fa-envelope"></i></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Team member 2 -->
                    <div class="team-card">
                        <div class="team-card-inner">
                            <div class="team-card-front">
                                <div class="team-image-wrapper">
                                    <img src="' . $CFG->wwwroot . '/theme/boost/pix/user2.jpg" alt="Mukesh S">
                                </div>
                                <div class="team-info">
                                    <h3>Mukesh S</h3>
                                    <p>Lead Software Architect</p>
                                </div>
                            </div>
                            <div class="team-card-back">
                                <div class="team-bio">
                                    <h3>Mukesh S</h3>
                                    <p class="team-title">Lead Software Architect</p>
                                    <p class="team-description">Expert in creating seamless and intuitive e-learning platforms with a passion for user-centered design and cutting-edge technology.</p>
                                    <div class="team-social">
                                        <a href="#" class="social-icon"><i class="fab fa-linkedin-in"></i></a>
                                        <a href="#" class="social-icon"><i class="fab fa-github"></i></a>
                                        <a href="#" class="social-icon"><i class="fas fa-envelope"></i></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Team member 3 -->
                    <div class="team-card">
                        <div class="team-card-inner">
                            <div class="team-card-front">
                                <div class="team-image-wrapper">
                                    <img src="' . $CFG->wwwroot . '/theme/boost/pix/user3.jpg" alt="Manjunathan V">
                                </div>
                                <div class="team-info">
                                    <h3>Manjunathan V</h3>
                                    <p>Cloud Architect & UI Design Head</p>
                                </div>
                            </div>
                            <div class="team-card-back">
                                <div class="team-bio">
                                    <h3>Manjunathan V</h3>
                                    <p class="team-title">Cloud Architect & UI Design Head</p>
                                    <p class="team-description">Strategic thinker with a strong background in educational management, dedicated to creating effective learning solutions.</p>
                                    <div class="team-social">
                                        <a href="#" class="social-icon"><i class="fab fa-linkedin-in"></i></a>
                                        <a href="#" class="social-icon"><i class="fab fa-twitter"></i></a>
                                        <a href="#" class="social-icon"><i class="fas fa-envelope"></i></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Team member 4 -->
                    <div class="team-card">
                        <div class="team-card-inner">
                            <div class="team-card-front">
                                <div class="team-image-wrapper">
                                    <img src="' . $CFG->wwwroot . '/theme/boost/pix/user4.jpg" alt="Gokul S">
                                </div>
                                <div class="team-info">
                                    <h3>Gokul S</h3>
                                    <p>Software Engineer - Product Innovation</p>
                                </div>
                            </div>
                            <div class="team-card-back">
                                <div class="team-bio">
                                    <h3>Gokul S</h3>
                                    <p class="team-title">Software Engineer - Product Innovation</p>
                                    <p class="team-description">Full-stack developer specialized in creating responsive and interactive e-learning platforms with a focus on performance and usability.</p>
                                    <div class="team-social">
                                        <a href="#" class="social-icon"><i class="fab fa-linkedin-in"></i></a>
                                        <a href="#" class="social-icon"><i class="fab fa-github"></i></a>
                                        <a href="#" class="social-icon"><i class="fas fa-envelope"></i></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        
        <!-- Footer -->
        <footer class="footer-section">
            <div class="section-container">
                <div class="footer-content">
                    <!-- Column 1: About -->
                    <div class="footer-logo">
                        <h3>Gretek Infotainment</h3>
                        <p>Transforming education through innovative technology solutions since 2015. Our mission is to make quality education accessible to everyone.</p>
                        <div class="footer-social">
                            <a href="#" class="social-icon"><i class="fab fa-facebook-f"></i></a>
                            <a href="#" class="social-icon"><i class="fab fa-twitter"></i></a>
                            <a href="#" class="social-icon"><i class="fab fa-linkedin-in"></i></a>
                            <a href="#" class="social-icon"><i class="fab fa-instagram"></i></a>
                        </div>
                    </div>
                    
                    <!-- Column 2: Quick Links -->
                    <div class="footer-links">
                        <h4>Quick Links</h4>
                        <ul>
                            <li><a href="#who-we-are">About Us</a></li>
                            <li><a href="#services">Services</a></li>
                            <li><a href="#team">Our Team</a></li>
                            <li><a href="#partners">Partners</a></li>
                            <li><a href="login/index.php">Login</a></li>
                        </ul>
                    </div>
                    
                    <!-- Column 3: Contact Info -->
                    <div class="footer-contact">
                        <h4>Contact Us</h4>
                        <p><i class="fas fa-envelope"></i> info@gretekinfotainment.com</p>
                        <p><i class="fas fa-phone-alt"></i> +91 7200-550 085 </p>
                    </div>
                    
                    <!-- Column 4: Mobile App -->
                    <div class="app-store-links">
                        <h4>Mobile App</h4>
                        <p>Access our platform on the go with our mobile applications</p>
                        <div class="store-buttons">
                            <a href="#" class="store-button">
                                <i class="fab fa-google-play"></i> Google Play
                            </a>
                            <a href="#" class="store-button">
                                <i class="fab fa-apple"></i> App Store
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="footer-bottom">
                    <p>&copy; ' . date('Y') . ' Gretek Infotainment. All rights reserved.</p>
                </div>
            </div>
        </footer>
    </div>
    </body>
    </html>';
    exit; 
} else {
    echo $OUTPUT->header();
    // Slider section with a single professional slide
    echo '<section class="hero-section">
    <!-- Background elements -->
    <div class="hero-bg-pattern"></div>
    <div class="hero-bg-element hero-bg-yellow"></div>
    <div class="hero-bg-element hero-bg-blue"></div>
    <div class="hero-bg-accent"></div>
    
    <!-- Floating icons -->
    <div class="floating-icon floating-icon-1">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: #111827; opacity: 0.4;">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="14.31" y1="8" x2="20.05" y2="17.94"></line>
            <line x1="9.69" y1="8" x2="21.17" y2="8"></line>
            <line x1="7.38" y1="12" x2="13.12" y2="2.06"></line>
            <line x1="9.69" y1="16" x2="3.95" y2="6.06"></line>
            <line x1="14.31" y1="16" x2="2.83" y2="16"></line>
            <line x1="16.62" y1="12" x2="10.88" y2="21.94"></line>
        </svg>
    </div>
    <div class="floating-icon floating-icon-2">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: #4CC9F0; opacity: 0.4;">
            <polyline points="16 18 22 12 16 6"></polyline>
            <polyline points="8 6 2 12 8 18"></polyline>
        </svg>
    </div>
    <div class="floating-icon floating-icon-3">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: #FDDB33; opacity: 0.4;">
            <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
            <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
            <line x1="12" y1="22.08" x2="12" y2="12"></line>
        </svg>
    </div>
    
    <div class="hero-container">
        <!-- Left content area -->
        <div class="hero-content">
            <h1 class="hero-title"><span>Accelerate Your Success - Excel in NEET, JEE Main & JEE Advanced with Next-Level Prep</span></h1>
            <p class="hero-description">
                Getting ready for NEET or JEE exams doesn\'t have to be hard. With our smart e-learning platform, we make it easy to learn, understand, and remember every concept. From video lessons and practice tests to expert tips and doubt-clearing support, everything is designed to guide you step by step. Our unique learning methods turn tough topics into simple ideas, making your journey from easy to easier. Whether you\'re just starting or revising, you\'ll feel confident and prepared. Join us and unlock your full potential—success is just a smart plan away!
            </p>
        </div>
        
        <!-- Right illustration area -->
        <div class="hero-illustration">
            <img src="' . $CFG->wwwroot . '/theme/boost/pix/community-illustration.png" alt="AI & ML Community" />
        </div>
    </div>
</section>';

    // Updated info panel section
    echo '<div class="info-panel-container">
    <div class="info-panel-header">
        <h2 class="info-panel-title">Achieve your dream rank with the right start</h2>
        
        <!-- Simple tab design with FIXED onclick handlers -->
        <div class="simple-tabs">
            <button class="simple-tab active" onclick="switchTab(\'neet\')">National Eligibility cum Entrance Test(NEET)</button>
            <button class="simple-tab" onclick="switchTab(\'jee\')">Joint Entrance Examination(JEE)</button>
        </div>
    </div>
    
    <div class="info-panel-content">
        <!-- NEET Panel -->
        <div class="info-panel active" id="neet" style="display: block;">
            <div class="simple-panel-layout">
                <div class="simple-panel-image">
                    <div class="blue-circle-bg"></div>
                    <div class="yellow-circle-overlay"></div>
                    <img src="' . $CFG->wwwroot . '/theme/infopanel/images/neet-preparation.jpg" alt="NEET Preparation">
                </div>
                <div class="simple-panel-text">
                    <h3>Achieve NEET Success with the Right Strategy</h3>
                    <p>Our NEET Excellence Program is designed to make your preparation smarter, sharper, and stress-free. Here\'s how we help you succeed:</p>
                    
                    <ul>
                        <li>NCERT-aligned study plan to build a strong conceptual foundation.</li>
                        <li>Daily practice and mock tests to boost speed and accuracy.</li>
                        <li>Smart performance insights to focus on key improvement areas.</li>
                        <li>Topic-wise MCQ analysis to target high-yield chapters effectively.</li>
                        <li>Live expert-led sessions to clear doubts instantly.</li>
                        <li>Regular mindset and motivation sessions to stay focused and stress-free.</li>
                    </ul>
                    
                    <a href="' . $CFG->wwwroot . '/course/index.php?categoryid=2" class="info-panel-button">Explore NEET Courses</a>
                </div>
            </div>
        </div>
        
        <!-- JEE Panel -->
        <div class="info-panel" id="jee" style="display: none;">
            <div class="simple-panel-layout">
                <div class="simple-panel-text">
                    <h3>Excel in JEE Main & Advanced with Precision Preparation</h3>
                    <p>Our dual-track program for JEE Main and Advanced blends strong conceptual learning with problem-solving mastery, helping students stand out in both exams.</p>
                    
                    <ul>
                        <li>Syllabus-mapped learning modules focused on deep concept clarity</li>
                        <li>Daily practice questions to build speed, accuracy, and consistency</li>
                        <li>Weekly mock tests tailored separately for Main and Advanced formats</li>
                        <li>Smart analytics to identify strengths and improve weak zones</li>
                        <li>Doubt-clearing sessions and personalized guidance from IIT mentors</li>
                        <li>Special strategy classes for high-weightage chapters and tricky problems</li>
                    </ul>
                    
                    <a href="' . $CFG->wwwroot . '/course/index.php?categoryid=3" class="info-panel-button">Explore JEE Courses</a>
                </div>
                <div class="simple-panel-image">
                    <div class="blue-circle-bg"></div>
                    <div class="yellow-circle-overlay"></div>
                    <img src="' . $CFG->wwwroot . '/theme/infopanel/images/jee-preparation.jpg" alt="JEE Preparation">
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Inline script with FIXED syntax for Moodle
function switchTab(tabId) {
    // Get all tabs and panels
    var tabs = document.querySelectorAll(".simple-tab");
    var panels = document.querySelectorAll(".info-panel");
    
    // Update tabs appearance
    for (var i = 0; i < tabs.length; i++) {
        tabs[i].classList.remove("active");
    }
    
    // Update panels visibility
    for (var j = 0; j < panels.length; j++) {
        panels[j].classList.remove("active");
        panels[j].style.display = "none";
    }
    
    // Activate selected tab - Using a different approach to avoid selector issues
    if (tabId === "neet") {
        tabs[0].classList.add("active");
    } else if (tabId === "jee") {
        tabs[1].classList.add("active");
    }
    
    // Show selected panel
    var selectedPanel = document.getElementById(tabId);
    if (selectedPanel) {
        selectedPanel.classList.add("active");
        selectedPanel.style.display = "block";
    }
}
</script>';

    // Original Moodle content
    if (!empty($CFG->customfrontpageinclude)) {
        $modnames = get_module_types_names();
        $modnamesplural = get_module_types_names(true);
        $mods = $modinfo->get_cms();
        include($CFG->customfrontpageinclude);
    } else if ($siteformatoptions['numsections'] > 0) {
        echo $courserenderer->frontpage_section1();
    }
    echo $courserenderer->frontpage();
    if ($editing && has_capability('moodle/course:create', context_system::instance())) {
        echo $courserenderer->add_new_course_button();
    }
    // Add Newly Added Courses section - Insert this code before the achievements section
    // after echo $courserenderer->frontpage(); line

    // Add Newly Added Courses section here
    echo '<div class="newly-added-courses-section">
        <h2 class="section-heading">Newly Added Courses</h2>
        <p class="section-subheading">Explore our latest educational offerings</p>';

    // Get courses added in the last 15 days
    $time = time() - (15 * 24 * 60 * 60); // 15 days ago
    $sql = "SELECT c.*, cc.name AS category_name, 
            (SELECT CONCAT(u.firstname, ' ', u.lastname) 
            FROM {user} u 
            JOIN {role_assignments} ra ON ra.userid = u.id
            JOIN {context} ctx ON ctx.id = ra.contextid
            JOIN {role} r ON r.id = ra.roleid
            WHERE ctx.instanceid = c.id 
            AND ctx.contextlevel = 50
            AND r.shortname = 'editingteacher'
            LIMIT 1) AS teacher_name
            FROM {course} c
            JOIN {course_categories} cc ON c.category = cc.id
            WHERE c.timecreated > :time 
            AND c.id != :siteid
            ORDER BY c.timecreated DESC";
            
    $params = array('time' => $time, 'siteid' => SITEID);
    $newcourses = $DB->get_records_sql($sql, $params);

    if (!empty($newcourses)) {
        echo '<div class="course-cards">';
        foreach ($newcourses as $course) {
            // Get course image if available
            $courseobj = new core_course_list_element($course);
            $courseimage = '';
            foreach ($courseobj->get_course_overviewfiles() as $file) {
                if ($file->is_valid_image()) {
                    $courseimage = file_encode_url($CFG->wwwroot . '/pluginfile.php',
                        '/' . $file->get_contextid() . '/' . $file->get_component() . '/' .
                        $file->get_filearea() . $file->get_filepath() . $file->get_filename());
                    break;
                }
            }
            
            // If no image, use a default
            if (empty($courseimage)) {
                $courseimage = $CFG->wwwroot . '/theme/boost/pix/course-default.jpg';
            }
            
            // Get the first letter of course name
            $firstLetter = strtoupper(substr($course->fullname, 0, 1));
            
            // Get teacher name - properly check course contacts
            $teachername = '';
            $coursecontacts = $courseobj->get_course_contacts();
            if (!empty($coursecontacts)) {
                $contact = reset($coursecontacts);
                $teachername = $contact['username'];
            } else {
                $teachername = $course->teacher_name ? $course->teacher_name : get_string('defaultcourseteacher');
            }
            
            echo '<a href="' . $CFG->wwwroot . '/course/view.php?id=' . $course->id . '" class="course-card-link">
                <div class="course-card">
                    <div class="course-image">
                        <img src="' . $courseimage . '" alt="' . $course->fullname . '">
                        <div class="course-letter-overlay">' . $firstLetter . '</div>
                        <div class="new-badge">NEW</div>
                    </div>
                    <div class="course-info">
                        <div class="course-menu">⋮</div>
                        <h3>' . $course->fullname . '</h3>
                        <div class="course-meta">
                            <div class="course-teacher">
                                <span class="course-meta-label">' . $teachername . '</span>
                            </div>
                        </div>
                    </div>
                    <div class="course-footer">
                        <div class="course-category">' . $course->category_name . '</div>
                    </div>
                </div>
            </a>';
        }
        echo '</div>';
    } else {
        echo '<div class="no-courses-message">
                <p>No new courses have been added in the last 15 days.</p>';
                
        // Add New Course button for teachers and admins
        if (has_capability('moodle/course:create', context_system::instance())) {
            echo '<a href="' . $CFG->wwwroot . '/course/edit.php?category=0" class="add-course-btn">
                    <i class="fa fa-plus-circle"></i> Add New Course
                </a>';
        }
        
        echo '</div>';
    }

    echo '</div>';
    // Code snippet to update index.php for the exact layout matching Image 2
    // Code snippet to update index.php for the exact layout matching Image 2

    // Updated achievements section with grid layout exactly matching Image 2
    $can_edit_achievements = has_capability('moodle/course:update', context_system::instance());
    echo '<div class="achievements-section-container">
        <h2 class="achievements-title">Our Achievements</h2>
        <div class="achievements-grid">';

    // Fetch achievements from database
    $achievements = $DB->get_records('theme_achievements', null, 'id ASC', '*', 0, 5); // Limit to 5 to match Image 2
    
    if (!empty($achievements)) {
        // Use a counter to determine grid position
        $counter = 1;
        foreach ($achievements as $achievement) {
            // First card gets special styling for full-height left column
            $special_class = $counter === 1 ? ' first-card' : '';
            
            echo '<div class="achievement-card animate-fade-in' . $special_class . '" data-id="' . $achievement->id . '">
                    <img src="' . $achievement->imagepath . '" alt="' . $achievement->category . '">
                    <div class="achievement-caption">
                        <h3>' . $achievement->category . '</h3>
                        <p>' . $achievement->title . '</p>
                    </div>';
            if ($can_edit_achievements) {
                echo '<div class="achievement-actions">
                        <button class="edit-achievement-btn" data-id="' . $achievement->id . '">Edit</button>
                        <button class="delete-achievement-btn" data-id="' . $achievement->id . '">Delete</button>
                    </div>';
            }
            echo '</div>';
            $counter++;
        }
    } else {
        // If no achievements are found, display a message for admins
        if ($can_edit_achievements) {
            echo '<div class="no-achievements-message">
                    <p>No achievements have been added yet. Click the "+" button below to add your first achievement.</p>
                  </div>';
        }
    }

    // Placeholder for adding new achievement - only show if less than 5 achievements
    $achievement_count = !empty($achievements) ? count($achievements) : count($default_categories);
    if ($can_edit_achievements && $achievement_count < 5) {
        echo '<div class="achievement-card add-achievement-card animate-fade-in">
                <button class="add-achievement-btn">+</button>
            </div>';
    } elseif ($can_edit_achievements) {
        // Display a message that max achievements are reached
        echo '<div class="achievement-max-message">
                <p>Maximum of 5 achievements reached. Delete an achievement to add a new one.</p>
              </div>';
    }

    echo '</div></div>';
    echo $OUTPUT->footer();
}
?>
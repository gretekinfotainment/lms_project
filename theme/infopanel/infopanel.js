// infopanel.js - Replace your current JS with this file

// This file is intentionally minimal since we're using inline JavaScript in the PHP
// to ensure the tabs function properly in Moodle

document.addEventListener('DOMContentLoaded', function() {
    // This function is a backup in case the inline switchTab function doesn't work
    if (typeof switchTab !== 'function') {
        window.switchTab = function(tabId) {
            // Get all tabs and panels
            var tabs = document.querySelectorAll('.simple-tab');
            var panels = document.querySelectorAll('.info-panel');
            
            // Update tabs appearance
            tabs.forEach(function(tab) {
                tab.classList.remove('active');
            });
            
            // Update panels visibility
            panels.forEach(function(panel) {
                panel.classList.remove('active');
                panel.style.display = 'none';
            });
            
            // Activate selected tab
            var selectedTab = document.querySelector('.simple-tab[onclick="switchTab(\'' + tabId + '\')"]');
            if (selectedTab) {
                selectedTab.classList.add('active');
            }
            
            // Show selected panel
            var selectedPanel = document.getElementById(tabId);
            if (selectedPanel) {
                selectedPanel.classList.add('active');
                selectedPanel.style.display = 'block';
            }
        };
    }
    
    // Make sure panels are properly initialized on page load
    var panels = document.querySelectorAll('.info-panel');
    panels.forEach(function(panel) {
        if (panel.classList.contains('active')) {
            panel.style.display = 'block';
        } else {
            panel.style.display = 'none';
        }
    });
    
    // Log for debugging
    console.log("Info panel initialized with " + panels.length + " panels");
});
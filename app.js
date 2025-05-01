document.addEventListener('DOMContentLoaded', () => {
    const hamburger = document.querySelector('.header .nav-bar .nav-list .hamburger');
    const mobile_menu = document.querySelector('.header .nav-bar .nav-list ul');
    const menu_items = document.querySelectorAll('.header .nav-bar .nav-list ul li a'); // Corrected selector
    const header = document.querySelector('#header'); // Target by ID for specificity

    // --- Hamburger Menu Toggle ---
    if (hamburger && mobile_menu) {
        hamburger.addEventListener('click', () => {
            hamburger.classList.toggle('active');
            mobile_menu.classList.toggle('active');
             // Optional: Prevent body scroll when menu is open
            document.body.style.overflow = mobile_menu.classList.contains('active') ? 'hidden' : '';
        });
    }

    // --- Close Mobile Menu on Item Click ---
    if (menu_items && mobile_menu && hamburger) {
        menu_items.forEach((item) => {
            item.addEventListener('click', () => {
                // Only toggle if hamburger is currently active (visible on mobile)
                if (hamburger.classList.contains('active')) {
                    hamburger.classList.remove('active'); // Use remove/add for clarity
                    mobile_menu.classList.remove('active');
                    document.body.style.overflow = ''; // Re-enable scroll
                }
            });
        });
    }

    // --- Header Background Change on Scroll ---
    if (header) {
        const scroll_trigger = 80; // Pixels to scroll before changing header bg

        const handleScroll = () => {
            if (window.scrollY > scroll_trigger) {
                // Add a class instead of directly manipulating style for better CSS control
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        };

        // Initial check in case page loads already scrolled
        handleScroll();

        // Listen for scroll events
        document.addEventListener('scroll', handleScroll);
    }

     // --- Active Link Highlighting (Basic Example) ---
     // This needs refinement based on your site structure/routing
     const currentPath = window.location.pathname.split("/").pop() || "index.html"; // Get current file name
     if (menu_items) {
        menu_items.forEach(item => {
            const itemPath = item.getAttribute('href').split("/").pop();
            if (itemPath === currentPath) {
                item.classList.add('active'); // Add active class
            } else {
                item.classList.remove('active'); // Ensure others are not active
            }
        });
     }


}); // End DOMContentLoaded
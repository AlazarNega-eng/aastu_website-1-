document.addEventListener('DOMContentLoaded', () => {
    // --- Hamburger Menu ---
    const hamburger = document.querySelector('.header .nav-bar .nav-list .hamburger');
    const mobile_menu = document.querySelector('.header .nav-bar .nav-list ul');
    const menu_items = document.querySelectorAll('.header .nav-bar .nav-list ul li a');
    const header = document.querySelector('#header'); // Target by ID

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
                if (hamburger.classList.contains('active')) {
                    hamburger.classList.remove('active');
                    mobile_menu.classList.remove('active');
                    document.body.style.overflow = '';
                }
            });
        });
    }

    // --- Header Background Change on Scroll ---
    if (header) {
        const scroll_trigger = 80;
        const handleScroll = () => {
            if (window.scrollY > scroll_trigger) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        };
        handleScroll(); // Initial check
        document.addEventListener('scroll', handleScroll);
    }

    // --- Active Link Highlighting (Basic) ---
    try { // Added try...catch for robustness if pathname is unusual
        const currentPath = window.location.pathname.split("/").pop() || "index.php";
        if (menu_items) {
            menu_items.forEach(item => {
                const itemLink = item.getAttribute('href');
                if (itemLink) { // Check if href exists
                    const itemPath = itemLink.split("/").pop();
                    // Highlight if paths match or if it's index and current is empty/root
                    if (itemPath === currentPath || (itemPath === 'index.php' && (currentPath === '' || currentPath === '/'))) {
                        item.classList.add('active');
                    } else {
                        item.classList.remove('active');
                    }
                }
            });
        }
    } catch (e) {
        console.error("Error setting active link:", e);
    }


    // --- Student Registration Form - Parent Address Toggle ---
    // Check if the specific elements for this form exist on the current page
    const regCheckbox = document.getElementById('parent_diff_address_check');
    const regParentSection = document.getElementById('parent_residence_section');

    // Only add listeners and logic if BOTH elements are found
    if (regCheckbox && regParentSection) {
        const regParentInputs = regParentSection.querySelectorAll('input'); // Get inputs inside

        const toggleParentAddress = () => {
            if (regCheckbox.checked) {
                regParentSection.style.display = 'block';
                // Make inputs required only if section is visible
                regParentInputs.forEach(input => input.required = true);
            } else {
                regParentSection.style.display = 'none';
                // Make inputs not required if section is hidden
                regParentInputs.forEach(input => {
                     input.required = false;
                     // Optionally clear values when hidden: input.value = '';
                });
            }
        };

        // Add the event listener to the checkbox
        regCheckbox.addEventListener('change', toggleParentAddress);

        // Note: No need for an initial call here if PHP correctly sets
        // the initial display style based on saved data. If you find
        // the section is sometimes incorrectly shown/hidden on load,
        // you might uncomment the line below, but PHP is preferred.
        // toggleParentAddress();
    }
    // --- End Student Registration Form Specific Logic ---


}); // End DOMContentLoaded
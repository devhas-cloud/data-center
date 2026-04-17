document.addEventListener("DOMContentLoaded", function () {
    // Mobile Menu Toggle
    const mobileMenuToggle = document.getElementById("mobileMenuToggle");
    const mobileNav = document.getElementById("mobileNav");
    const mobileNavClose = document.getElementById("mobileNavClose");

    mobileMenuToggle.addEventListener("click", function () {
        mobileNav.classList.toggle("show");
    });

    mobileNavClose.addEventListener("click", function () {
        mobileNav.classList.remove("show");
    });

    // Close menu when clicking outside
    document.addEventListener("click", function (event) {
        const isClickInsideMenu = mobileNav.contains(event.target);
        const isClickOnToggle = mobileMenuToggle.contains(event.target);

        if (
            !isClickInsideMenu &&
            !isClickOnToggle &&
            mobileNav.classList.contains("show")
        ) {
            mobileNav.classList.remove("show");
        }
    });

    // User Profile Dropdown Toggle
    const userProfileDropdown = document.getElementById("userProfileDropdown");
    const userProfileToggle = document.getElementById("userProfileToggle");

    userProfileToggle.addEventListener("click", function (e) {
        e.stopPropagation();
        userProfileDropdown.classList.toggle("show");
    });

    // Close dropdown when clicking outside
    document.addEventListener("click", function (event) {
        if (
            !userProfileDropdown.contains(event.target) &&
            userProfileDropdown.classList.contains("show")
        ) {
            userProfileDropdown.classList.remove("show");
        }
    });

    



});

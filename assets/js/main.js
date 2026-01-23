// Main JavaScript for MediKo

document.addEventListener("DOMContentLoaded", function () {
  // Mobile menu toggle
  const mobileMenuButton = document.getElementById("mobile-menu-button");
  const mobileMenu = document.getElementById("mobile-menu");

  if (mobileMenuButton && mobileMenu) {
    mobileMenuButton.addEventListener("click", function () {
      mobileMenu.classList.toggle("hidden");
    });
  }

  // Update login form to use our new handler
  const loginForm = document.getElementById("login-form");
  if (loginForm) {
    // Remove any existing submit handlers
    loginForm.onsubmit = null;
    const newForm = loginForm.cloneNode(true);
    loginForm.parentNode.replaceChild(newForm, loginForm);

    // Add our new handler
    newForm.onsubmit =
      window.handleLogin ||
      function (e) {
        e.preventDefault();
        console.error("handleLogin function not found");
        return false;
      };
  }

  // Modal functionality
  const modals = document.querySelectorAll(".modal");

  // Open modal
  window.openModal = function (modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
      modal.classList.remove("hidden");
      document.body.style.overflow = "hidden";
    }
  };

  // Close modal
  window.closeModal = function (modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
      modal.classList.add("hidden");
      document.body.style.overflow = "auto";
    }
  };

  // Toggle password visibility
  window.togglePassword = function (inputId) {
    const input = document.getElementById(inputId);
    const icon = document.getElementById(`toggle-${inputId}`);

    if (!input || !icon) return;

    if (input.type === "password") {
      input.type = "text";
      icon.classList.remove("fa-eye");
      icon.classList.add("fa-eye-slash");
    } else {
      input.type = "password";
      icon.classList.remove("fa-eye-slash");
      icon.classList.add("fa-eye");
    }
  };

  // Close modal when clicking outside
  modals.forEach((modal) => {
    modal.addEventListener("click", function (e) {
      if (e.target === modal) {
        modal.classList.add("hidden");
        document.body.style.overflow = "auto";
      }
    });
  });

  // Smooth scrolling for anchor links
  document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
    anchor.addEventListener("click", function (e) {
      e.preventDefault();
      const targetId = this.getAttribute("href");
      if (targetId === "#") return;

      const targetElement = document.querySelector(targetId);
      if (targetElement) {
        targetElement.scrollIntoView({
          behavior: "smooth",
        });
      }
    });
  });
});

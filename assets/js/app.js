// Import required modules
import Alpine from "alpinejs";

// Initialize Alpine.js
window.Alpine = Alpine;
Alpine.start();

// Your custom JavaScript code
document.addEventListener("DOMContentLoaded", function () {
  // Initialize tooltips
  const tooltipTriggers = document.querySelectorAll("[data-tooltip]");

  tooltipTriggers.forEach((trigger) => {
    trigger.addEventListener("mouseenter", showTooltip);
    trigger.addEventListener("mouseleave", hideTooltip);
  });

  // Initialize dropdowns
  const dropdownButtons = document.querySelectorAll("[data-dropdown-toggle]");

  dropdownButtons.forEach((button) => {
    button.addEventListener("click", toggleDropdown);
  });

  // Close dropdowns when clicking outside
  document.addEventListener("click", function (event) {
    if (!event.target.matches("[data-dropdown-toggle]")) {
      const dropdowns = document.querySelectorAll(".dropdown-menu");
      dropdowns.forEach((dropdown) => {
        if (dropdown.classList.contains("show")) {
          dropdown.classList.remove("show");
        }
      });
    }
  });
});

// Helper functions
function showTooltip(event) {
  const tooltipText = this.getAttribute("data-tooltip");
  const tooltip = document.createElement("div");
  tooltip.className =
    "absolute z-50 px-2 py-1 text-sm text-white bg-gray-800 rounded-md";
  tooltip.textContent = tooltipText;
  tooltip.style.top = `${
    this.getBoundingClientRect().bottom + window.scrollY
  }px`;
  tooltip.style.left = `${
    this.getBoundingClientRect().left + window.scrollX
  }px`;
  tooltip.id = "tooltip";
  document.body.appendChild(tooltip);
}

function hideTooltip() {
  const tooltip = document.getElementById("tooltip");
  if (tooltip) {
    tooltip.remove();
  }
}

function toggleDropdown(event) {
  event.preventDefault();
  const dropdownId = this.getAttribute("data-dropdown-toggle");
  const dropdown = document.getElementById(dropdownId);
  dropdown.classList.toggle("show");
}

// Form validation
function validateForm(form) {
  let isValid = true;
  const inputs = form.querySelectorAll(
    "input[required], select[required], textarea[required]"
  );

  inputs.forEach((input) => {
    if (!input.value.trim()) {
      isValid = false;
      input.classList.add("border-red-500");
    } else {
      input.classList.remove("border-red-500");
    }
  });

  return isValid;
}

// Export functions for use in other modules
export { validateForm };

// public/assets/js/app.js

document.addEventListener('DOMContentLoaded', function () {
    // Demo Account Quick-Fill Helper
    const demoButtons = document.querySelectorAll('[data-demo-email]');
    demoButtons.forEach(btn => {
        btn.addEventListener('click', function () {
            const email = this.getAttribute('data-demo-email');
            const pass = this.getAttribute('data-demo-pass');
            const emailInput = document.getElementById('email');
            const passInput = document.getElementById('password');
            if (emailInput && passInput) {
                emailInput.value = email;
                passInput.value = pass;
            }
        });
    });

    // Auto calculate affected population when households change
    const hhInput = document.getElementById('affected_households');
    const popInput = document.getElementById('affected_population');
    if (hhInput && popInput) {
        hhInput.addEventListener('input', function () {
            const hh = parseInt(this.value, 10) || 0;
            // Standard average Philippine household size is ~4 to 5 persons
            if (!popInput.dataset.manualEdited) {
                popInput.value = hh * 4;
            }
        });
        popInput.addEventListener('input', function () {
            popInput.dataset.manualEdited = "true";
        });
    }

    // Modal or review toggle helper
    const reviewActionSelect = document.getElementById('review_action');
    const modQtyGroup = document.getElementById('modified_qty_group');
    if (reviewActionSelect && modQtyGroup) {
        reviewActionSelect.addEventListener('change', function () {
            if (this.value === 'modify') {
                modQtyGroup.style.display = 'block';
            } else {
                modQtyGroup.style.display = 'none';
            }
        });
    }
});

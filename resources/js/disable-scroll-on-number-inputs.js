// This script prevents the value of a number input from changing when scrolling.
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('input[type="number"]').forEach(function(input) {
        input.addEventListener('wheel', function(e) {
            if (document.activeElement === e.target) {
                e.preventDefault();
            }
        }, { passive: false });
    });
});

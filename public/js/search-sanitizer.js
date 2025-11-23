/**
 * Global Search Input Sanitization
 * Automatically sanitizes all search inputs to prevent XSS and SQL injection
 */

document.addEventListener('DOMContentLoaded', function() {
    // Find all search inputs
    const searchInputs = document.querySelectorAll(
        'input[type="text"][id*="search" i], ' +
        'input[type="text"][id*="Search" i], ' +
        'input[type="search"], ' +
        'input[placeholder*="search" i], ' +
        'input[placeholder*="Search" i], ' +
        'input[placeholder*="Find" i]'
    );

    searchInputs.forEach(input => {
        // Add input sanitization
        input.addEventListener('input', function(e) {
            const originalValue = this.value;
            
            // Check for malicious patterns
            if (ValidationUtils.hasSQLInjection(originalValue) || ValidationUtils.hasXSS(originalValue)) {
                // Sanitize the input
                this.value = ValidationUtils.sanitize(originalValue);
                
                // Show warning
                if (!this.dataset.warningShown) {
                    ValidationUtils.showToast('Invalid characters detected and removed', 'warning');
                    this.dataset.warningShown = 'true';
                    setTimeout(() => {
                        delete this.dataset.warningShown;
                    }, 3000);
                }
            }
        });

        // Enforce maximum length
        input.addEventListener('input', function() {
            if (this.value.length > ValidationUtils.lengths.search.max) {
                this.value = this.value.substring(0, ValidationUtils.lengths.search.max);
                ValidationUtils.showToast(`Search limited to ${ValidationUtils.lengths.search.max} characters`, 'info');
            }
        });
    });

    // Protect all form submissions
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            // Sanitize all text inputs before submission
            const textInputs = this.querySelectorAll('input[type="text"], input[type="search"], textarea');
            
            textInputs.forEach(input => {
                if (input.value) {
                    // Check for malicious content
                    if (ValidationUtils.hasSQLInjection(input.value) || ValidationUtils.hasXSS(input.value)) {
                        e.preventDefault();
                        ValidationUtils.showToast('Form contains invalid characters', 'error');
                        ValidationUtils.showError(input, 'Invalid characters detected');
                        input.focus();
                        return false;
                    }
                }
            });
        });
    });

    console.log(`🛡️ Search sanitization active on ${searchInputs.length} inputs`);
});

/**
 * Frontend Validation Utilities
 * Comprehensive client-side validation to prevent spam and unwanted data
 * 
 * SECURITY FEATURES:
 * - Input sanitization
 * - XSS prevention
 * - SQL injection prevention at input level
 * - Length limits enforcement
 * - Pattern matching
 * - Rate limiting support
 */

const ValidationUtils = {
    // Regex patterns
    patterns: {
        email: /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/,
        phonePhilippines: /^(\+63|0)[0-9]{10}$/,
        phoneInternational: /^\+?[1-9]\d{1,14}$/,
        alphanumeric: /^[a-zA-Z0-9\s]+$/,
        alphabetic: /^[a-zA-Z\s]+$/,
        numeric: /^[0-9]+$/,
        noSpecialChars: /^[a-zA-Z0-9\s\-_.,]+$/,
        url: /^(https?:\/\/)?([\da-z.-]+)\.([a-z.]{2,6})([\/\w .-]*)*\/?$/,
        sqlInjection: /(\b(SELECT|INSERT|UPDATE|DELETE|DROP|CREATE|ALTER|EXEC|EXECUTE|UNION|SCRIPT)\b)|(-{2})|('|")|(<script|<\/script>)/gi,
        xss: /<script[^>]*>.*?<\/script>|<iframe[^>]*>.*?<\/iframe>|javascript:|onerror=|onload=|onclick=/gi
    },

    // Length constraints
    lengths: {
        name: { min: 2, max: 100 },
        email: { min: 5, max: 100 },
        phone: { min: 10, max: 15 },
        password: { min: 8, max: 255 },
        reason: { min: 10, max: 500 },
        address: { min: 5, max: 500 },
        search: { max: 100 },
        message: { min: 1, max: 1000 }
    },

    /**
     * Sanitize input to prevent XSS and SQL injection
     */
    sanitize(input) {
        if (typeof input !== 'string') return input;
        
        // Remove potential XSS vectors
        let sanitized = input
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#x27;')
            .replace(/\//g, '&#x2F;');
        
        // Trim whitespace
        sanitized = sanitized.trim();
        
        // Remove null bytes
        sanitized = sanitized.replace(/\0/g, '');
        
        return sanitized;
    },

    /**
     * Check for SQL injection patterns
     */
    hasSQLInjection(input) {
        return this.patterns.sqlInjection.test(input);
    },

    /**
     * Check for XSS patterns
     */
    hasXSS(input) {
        return this.patterns.xss.test(input);
    },

    /**
     * Validate email format
     */
    validateEmail(email) {
        const errors = [];
        
        if (!email || email.trim() === '') {
            errors.push('Email is required');
            return { valid: false, errors };
        }

        const sanitized = this.sanitize(email);
        
        if (sanitized.length < this.lengths.email.min) {
            errors.push(`Email must be at least ${this.lengths.email.min} characters`);
        }
        
        if (sanitized.length > this.lengths.email.max) {
            errors.push(`Email must not exceed ${this.lengths.email.max} characters`);
        }
        
        if (!this.patterns.email.test(sanitized)) {
            errors.push('Invalid email format');
        }

        if (this.hasSQLInjection(sanitized) || this.hasXSS(sanitized)) {
            errors.push('Invalid characters detected');
        }

        return {
            valid: errors.length === 0,
            errors,
            sanitized
        };
    },

    /**
     * Validate phone number (Philippines format)
     */
    validatePhone(phone, required = false) {
        const errors = [];
        
        if (!phone || phone.trim() === '') {
            if (required) {
                errors.push('Phone number is required');
            }
            return { valid: !required, errors, sanitized: '' };
        }

        const sanitized = phone.replace(/[\s\-()]/g, ''); // Remove formatting
        
        if (sanitized.length < this.lengths.phone.min) {
            errors.push(`Phone number must be at least ${this.lengths.phone.min} digits`);
        }
        
        if (sanitized.length > this.lengths.phone.max) {
            errors.push(`Phone number must not exceed ${this.lengths.phone.max} digits`);
        }
        
        if (!this.patterns.phonePhilippines.test(sanitized)) {
            errors.push('Invalid phone format. Use +639XXXXXXXXX or 09XXXXXXXXX');
        }

        return {
            valid: errors.length === 0,
            errors,
            sanitized
        };
    },

    /**
     * Validate name (full name or workplace name)
     */
    validateName(name, fieldName = 'Name') {
        const errors = [];
        
        if (!name || name.trim() === '') {
            errors.push(`${fieldName} is required`);
            return { valid: false, errors };
        }

        const sanitized = this.sanitize(name);
        
        if (sanitized.length < this.lengths.name.min) {
            errors.push(`${fieldName} must be at least ${this.lengths.name.min} characters`);
        }
        
        if (sanitized.length > this.lengths.name.max) {
            errors.push(`${fieldName} must not exceed ${this.lengths.name.max} characters`);
        }

        // Allow letters, numbers, spaces, and basic punctuation
        if (!/^[a-zA-Z0-9\s\-'.,]+$/.test(sanitized)) {
            errors.push(`${fieldName} contains invalid characters`);
        }

        if (this.hasSQLInjection(sanitized) || this.hasXSS(sanitized)) {
            errors.push('Invalid characters detected');
        }

        return {
            valid: errors.length === 0,
            errors,
            sanitized
        };
    },

    /**
     * Validate password
     */
    validatePassword(password, confirmPassword = null) {
        const errors = [];
        
        if (!password || password.trim() === '') {
            errors.push('Password is required');
            return { valid: false, errors };
        }

        if (password.length < this.lengths.password.min) {
            errors.push(`Password must be at least ${this.lengths.password.min} characters`);
        }
        
        if (password.length > this.lengths.password.max) {
            errors.push(`Password must not exceed ${this.lengths.password.max} characters`);
        }

        // Check password strength
        const hasUpperCase = /[A-Z]/.test(password);
        const hasLowerCase = /[a-z]/.test(password);
        const hasNumbers = /\d/.test(password);
        const hasSpecialChar = /[!@#$%^&*(),.?":{}|<>]/.test(password);

        if (!hasUpperCase || !hasLowerCase || !hasNumbers) {
            errors.push('Password must contain uppercase, lowercase, and numbers');
        }

        // Check confirmation match
        if (confirmPassword !== null && password !== confirmPassword) {
            errors.push('Passwords do not match');
        }

        return {
            valid: errors.length === 0,
            errors,
            strength: (hasUpperCase + hasLowerCase + hasNumbers + hasSpecialChar)
        };
    },

    /**
     * Validate text area (reason, address, message)
     */
    validateTextArea(text, minLength, maxLength, fieldName = 'Text') {
        const errors = [];
        
        if (!text || text.trim() === '') {
            errors.push(`${fieldName} is required`);
            return { valid: false, errors };
        }

        const sanitized = this.sanitize(text);
        
        if (sanitized.length < minLength) {
            errors.push(`${fieldName} must be at least ${minLength} characters`);
        }
        
        if (sanitized.length > maxLength) {
            errors.push(`${fieldName} must not exceed ${maxLength} characters`);
        }

        if (this.hasSQLInjection(sanitized) || this.hasXSS(sanitized)) {
            errors.push('Invalid characters detected');
        }

        return {
            valid: errors.length === 0,
            errors,
            sanitized
        };
    },

    /**
     * Validate search input (prevent spam and injection)
     */
    validateSearch(search) {
        const errors = [];
        
        if (!search || search.trim() === '') {
            return { valid: true, errors: [], sanitized: '' };
        }

        const sanitized = this.sanitize(search);
        
        if (sanitized.length > this.lengths.search.max) {
            errors.push(`Search query too long (max ${this.lengths.search.max} characters)`);
        }

        if (this.hasSQLInjection(sanitized) || this.hasXSS(sanitized)) {
            errors.push('Invalid search query');
        }

        return {
            valid: errors.length === 0,
            errors,
            sanitized
        };
    },

    /**
     * Validate coordinates
     */
    validateCoordinates(lat, lng) {
        const errors = [];
        
        const latitude = parseFloat(lat);
        const longitude = parseFloat(lng);

        if (isNaN(latitude) || latitude < -90 || latitude > 90) {
            errors.push('Invalid latitude');
        }

        if (isNaN(longitude) || longitude < -180 || longitude > 180) {
            errors.push('Invalid longitude');
        }

        return {
            valid: errors.length === 0,
            errors,
            latitude,
            longitude
        };
    },

    /**
     * Show error message in form
     */
    showError(element, message) {
        // Remove existing error
        this.clearError(element);

        // Add error styling
        element.classList.add('border-red-500', 'focus:ring-red-500', 'focus:border-red-500');
        element.classList.remove('border-gray-300', 'focus:ring-indigo-500', 'focus:border-indigo-500');

        // Create error message element
        const errorDiv = document.createElement('p');
        // Use mt-1 for spacing, matching the Blade error styling
        errorDiv.className = 'mt-1 text-sm text-red-600 validation-error'; 
        errorDiv.textContent = message;

        // Find the main container div that wraps the input (and potentially the label)
        // For your login form, this is the parent <div> of the element.
        const mainContainer = element.closest('div'); 
        
        if (mainContainer) {
             // Insert the error message right after the main container div
            mainContainer.parentNode.insertBefore(errorDiv, mainContainer.nextSibling);
        } else {
            // Fallback: insert right after the element itself
            element.parentNode.insertBefore(errorDiv, element.nextSibling);
        }
    },

    /**
     * Clear error message
     */
    clearError(element) {
        // Remove error styling
        element.classList.remove('border-red-500', 'focus:ring-red-500', 'focus:border-red-500');
        element.classList.add('border-gray-300', 'focus:ring-indigo-500', 'focus:border-indigo-500');

        // Remove error message - check both locations
        const container = element.closest('.relative') || element.parentNode;
        const outerContainer = container.parentNode;
        const errorMsg = outerContainer.querySelector('.validation-error');
        if (errorMsg) {
            errorMsg.remove();
        }
    },

    /**
     * Show success message
     */
    showSuccess(element) {
        this.clearError(element);
        element.classList.add('border-green-500', 'focus:ring-green-500');
        element.classList.remove('border-red-500', 'focus:ring-red-500');
    },

    /**
     * Rate limiting check (simple client-side)
     */
    rateLimiter: {
        attempts: {},
        
        canSubmit(formId, maxAttempts = 3, timeWindow = 60000) {
            const now = Date.now();
            
            if (!this.attempts[formId]) {
                this.attempts[formId] = [];
            }

            // Remove old attempts outside time window
            this.attempts[formId] = this.attempts[formId].filter(time => now - time < timeWindow);

            // Check if limit exceeded
            if (this.attempts[formId].length >= maxAttempts) {
                const oldestAttempt = Math.min(...this.attempts[formId]);
                const waitTime = Math.ceil((timeWindow - (now - oldestAttempt)) / 1000);
                return {
                    allowed: false,
                    message: `Too many attempts. Please wait ${waitTime} seconds.`
                };
            }

            // Record this attempt
            this.attempts[formId].push(now);
            return { allowed: true };
        },

        reset(formId) {
            if (this.attempts[formId]) {
                this.attempts[formId] = [];
            }
        }
    },

    /**
     * Show toast notification
     */
    showToast(message, type = 'error') {
        const colors = {
            error: 'bg-red-500',
            success: 'bg-green-500',
            warning: 'bg-yellow-500',
            info: 'bg-blue-500'
        };

        const toast = document.createElement('div');
        toast.className = `fixed top-4 right-4 ${colors[type]} text-white px-6 py-3 rounded-lg shadow-lg z-50 animate-fade-in`;
        toast.textContent = message;

        document.body.appendChild(toast);

        setTimeout(() => {
            toast.classList.add('animate-fade-out');
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }
};

// Export for use in modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = ValidationUtils;
}

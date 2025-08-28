/**
 * Form validation JavaScript for order and contact forms
 */

(function() {
    'use strict';

    // Get FormValidator from main.js
    const FormValidator = window.SatouIllustration?.FormValidator;
    
    if (!FormValidator) {
        console.warn('FormValidator not found. Make sure main.js is loaded first.');
        return;
    }

    // DOM Elements
    const forms = document.querySelectorAll('form[data-validate]');

    /**
     * Initialize form validation
     */
    function init() {
        forms.forEach(form => {
            setupFormValidation(form);
        });
    }

    /**
     * Setup validation for a specific form
     */
    function setupFormValidation(form) {
        const fields = form.querySelectorAll('.form-input, .form-textarea, .form-select');
        
        // Add event listeners to fields
        fields.forEach(field => {
            field.addEventListener('blur', () => validateField(field));
            field.addEventListener('input', () => clearFieldError(field));
        });

        // Add submit event listener
        form.addEventListener('submit', (e) => handleFormSubmit(e, form));
    }

    /**
     * Validate individual field
     */
    function validateField(field) {
        const value = field.value.trim();
        const fieldName = field.name;
        const isRequired = field.hasAttribute('required');
        
        // Clear previous errors
        FormValidator.clearError(field);

        // Required field validation
        if (isRequired && !FormValidator.isRequired(value)) {
            FormValidator.showError(field, 'この項目は必須です。');
            return false;
        }

        // Skip other validations if field is empty and not required
        if (!value && !isRequired) {
            return true;
        }

        // Email validation
        if (field.type === 'email' && !FormValidator.isValidEmail(value)) {
            FormValidator.showError(field, '正しいメールアドレスを入力してください。');
            return false;
        }

        // Phone validation (basic)
        if (field.type === 'tel' && value && !isValidPhone(value)) {
            FormValidator.showError(field, '正しい電話番号を入力してください。');
            return false;
        }

        // Text length validation
        if (field.type === 'text' || field.tagName === 'TEXTAREA') {
            if (fieldName === 'name' && !FormValidator.minLength(value, 1)) {
                FormValidator.showError(field, 'お名前を入力してください。');
                return false;
            }
            
            if (fieldName === 'subject' && !FormValidator.minLength(value, 3)) {
                FormValidator.showError(field, '件名は3文字以上で入力してください。');
                return false;
            }
            
            if ((fieldName === 'message' || fieldName === 'project-description') && !FormValidator.minLength(value, 10)) {
                FormValidator.showError(field, '内容は10文字以上で入力してください。');
                return false;
            }
        }

        // Select validation
        if (field.tagName === 'SELECT' && isRequired && !value) {
            FormValidator.showError(field, '選択してください。');
            return false;
        }

        // Date validation (future date for deadline)
        if (field.type === 'date' && fieldName === 'deadline' && value) {
            const selectedDate = new Date(value);
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            
            if (selectedDate < today) {
                FormValidator.showError(field, '今日以降の日付を選択してください。');
                return false;
            }
        }

        return true;
    }

    /**
     * Clear field error on input
     */
    function clearFieldError(field) {
        if (field.classList.contains('is-invalid')) {
            FormValidator.clearError(field);
        }
    }

    /**
     * Handle form submission
     */
    function handleFormSubmit(e, form) {
        e.preventDefault();
        
        const fields = form.querySelectorAll('.form-input, .form-textarea, .form-select');
        let isValid = true;
        
        // Validate all fields
        fields.forEach(field => {
            if (!validateField(field)) {
                isValid = false;
            }
        });

        if (isValid) {
            // Show loading state
            showLoadingState(form);
            
            // Simulate form submission (replace with actual submission logic)
            setTimeout(() => {
                showSuccessMessage(form);
            }, 2000);
        } else {
            // Focus on first invalid field
            const firstInvalidField = form.querySelector('.is-invalid');
            if (firstInvalidField) {
                firstInvalidField.focus();
                firstInvalidField.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
    }

    /**
     * Show loading state
     */
    function showLoadingState(form) {
        const submitButton = form.querySelector('button[type="submit"]');
        if (submitButton) {
            submitButton.disabled = true;
            submitButton.textContent = '送信中...';
            submitButton.classList.add('is-loading');
        }
    }

    /**
     * Show success message
     */
    function showSuccessMessage(form) {
        const submitButton = form.querySelector('button[type="submit"]');
        const formContainer = form.parentNode;
        
        // Create success message
        const successMessage = document.createElement('div');
        successMessage.className = 'form-success';
        successMessage.innerHTML = `
            <div style="text-align: center; padding: 2rem; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 8px; color: #155724;">
                <h3 style="margin-bottom: 1rem; color: #155724;">送信完了</h3>
                <p style="margin: 0;">お問い合わせありがとうございます。<br>3営業日以内にご連絡いたします。</p>
            </div>
        `;
        
        // Hide form and show success message
        form.style.display = 'none';
        formContainer.appendChild(successMessage);
        
        // Scroll to success message
        successMessage.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    /**
     * Basic phone number validation
     */
    function isValidPhone(phone) {
        // Remove all non-digit characters
        const cleanPhone = phone.replace(/\D/g, '');
        
        // Check if it's a valid Japanese phone number format
        // Mobile: 090/080/070 + 8 digits
        // Landline: Area code + 7-8 digits
        return cleanPhone.length >= 10 && cleanPhone.length <= 11;
    }

    /**
     * Real-time character counter (optional enhancement)
     */
    function setupCharacterCounter() {
        const textareas = document.querySelectorAll('.form-textarea');
        
        textareas.forEach(textarea => {
            const maxLength = textarea.getAttribute('maxlength');
            if (maxLength) {
                const counter = document.createElement('div');
                counter.className = 'character-counter';
                counter.style.cssText = 'text-align: right; font-size: 0.875rem; color: #666; margin-top: 0.25rem;';
                
                const updateCounter = () => {
                    const remaining = maxLength - textarea.value.length;
                    counter.textContent = `残り${remaining}文字`;
                    
                    if (remaining < 50) {
                        counter.style.color = '#e74c3c';
                    } else {
                        counter.style.color = '#666';
                    }
                };
                
                textarea.addEventListener('input', updateCounter);
                textarea.parentNode.appendChild(counter);
                updateCounter();
            }
        });
    }

    /**
     * Auto-resize textareas
     */
    function setupAutoResize() {
        const textareas = document.querySelectorAll('.form-textarea');
        
        textareas.forEach(textarea => {
            const autoResize = () => {
                textarea.style.height = 'auto';
                textarea.style.height = textarea.scrollHeight + 'px';
            };
            
            textarea.addEventListener('input', autoResize);
            // Initial resize
            autoResize();
        });
    }

    /**
     * Setup form enhancements
     */
    function setupEnhancements() {
        setupCharacterCounter();
        setupAutoResize();
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            init();
            setupEnhancements();
        });
    } else {
        init();
        setupEnhancements();
    }

})();

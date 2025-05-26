document.addEventListener('DOMContentLoaded', function() {
    // Fix input label positions for pre-filled inputs (edit form)
    document.querySelectorAll('.form-control').forEach(input => {
        if (input.value) {
            const label = input.nextElementSibling;
            if (label && label.tagName === 'LABEL') {
                label.classList.add('active');
            }
        }
    });

    // Add password visibility toggle
    setupPasswordToggles();
    
    // Add form submission animation
    setupFormAnimation();
    
    // Add input focus effects
    setupInputEffects();
});

function setupPasswordToggles() {
    // Add toggle buttons after password fields if they don't exist
    document.querySelectorAll('input[type="password"]').forEach(input => {
        // Create wrapper if it doesn't exist
        if (!input.parentElement.classList.contains('password-field')) {
            const wrapper = document.createElement('div');
            wrapper.classList.add('password-field');
            input.parentNode.insertBefore(wrapper, input);
            wrapper.appendChild(input);
        }
        
        // Add toggle button if it doesn't exist
        if (!input.nextElementSibling || !input.nextElementSibling.classList.contains('password-toggle')) {
            const toggleBtn = document.createElement('button');
            toggleBtn.type = 'button';
            toggleBtn.classList.add('password-toggle');
            toggleBtn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>';
            toggleBtn.addEventListener('click', function() {
                const inputType = input.getAttribute('type');
                input.setAttribute('type', inputType === 'password' ? 'text' : 'password');
                
                // Change icon
                this.innerHTML = inputType === 'password' 
                    ? '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>' 
                    : '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>';
            });
            input.parentNode.appendChild(toggleBtn);
        }
    });
}

function setupFormAnimation() {
    const form = document.querySelector('form');
    const submitBtn = form.querySelector('button[type="submit"]');
    
    // Add success animation class to button if it doesn't have it
    if (!submitBtn.classList.contains('btn-success-animation')) {
        submitBtn.classList.add('btn-success-animation');
    }
    
    form.addEventListener('submit', function(e) {
 
        const invalidInputs = form.querySelectorAll('.is-invalid');
        if (invalidInputs.length > 0) {
            e.preventDefault();
            invalidInputs[0].focus();
        } else {
        
            submitBtn.classList.add('success');
            
          
        }
    });
}

function setupInputEffects() {
    // Add placeholder attribute for floating label effect
    document.querySelectorAll('.form-control').forEach(input => {
        input.setAttribute('placeholder', ' ');
        
        // Move labels after inputs for the CSS floating effect to work
        const label = document.querySelector(`label[for="${input.id}"]`);
        if (label && label.parentNode === input.parentNode && !input.nextElementSibling) {
            input.parentNode.insertBefore(label, input.nextSibling);
        }
        
        // Add focus and blur events for animation
        input.addEventListener('focus', function() {
            this.parentNode.classList.add('focused');
        });
        
        input.addEventListener('blur', function() {
            this.parentNode.classList.remove('focused');
        });
    });
    
    // Group name and surname inputs in a row if they aren't already
    const nomInput = document.getElementById('nom');
    const prenomInput = document.getElementById('prenom');
    
    if (nomInput && prenomInput && 
        nomInput.parentNode.classList.contains('form-group') && 
        prenomInput.parentNode.classList.contains('form-group')) {
        
        // Check if they're not already in a form-row
        if (nomInput.parentNode.parentNode !== prenomInput.parentNode.parentNode || 
            !nomInput.parentNode.parentNode.classList.contains('form-row')) {
            
            const formRow = document.createElement('div');
            formRow.classList.add('form-row');
            
            // Insert the row before the nom's form-group
            nomInput.parentNode.parentNode.insertBefore(formRow, nomInput.parentNode);
            
            // Move both form-groups into the row
            formRow.appendChild(nomInput.parentNode);
            formRow.appendChild(prenomInput.parentNode);
        }
    }
}
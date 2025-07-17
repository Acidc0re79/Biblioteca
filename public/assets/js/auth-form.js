// /public/assets/js/auth-form.js

document.addEventListener('DOMContentLoaded', function() {
    const loginBtn = document.getElementById('btn-login');
    const registerBtn = document.getElementById('btn-register');
    const formsContainer = document.querySelector('.forms-container');

    if (loginBtn && registerBtn && formsContainer) {
        // Muestra el formulario de login por defecto
        loginBtn.addEventListener('click', (e) => {
            e.preventDefault();
            formsContainer.style.transform = 'translateX(0%)';
            loginBtn.classList.add('active');
            registerBtn.classList.remove('active');
        });

        // Muestra el formulario de registro
        registerBtn.addEventListener('click', (e) => {
            e.preventDefault();
            formsContainer.style.transform = 'translateX(-50%)';
            registerBtn.classList.add('active');
            loginBtn.classList.remove('active');
        });

        // Comprueba si la URL indica que debe mostrarse un mensaje de registro.
        // Si es así, activa el formulario de login para que el usuario vea el mensaje de éxito.
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('registro')) {
            loginBtn.click();
        } else {
            // Si no, activa el formulario de login por defecto.
            loginBtn.classList.add('active');

        }
    }
});
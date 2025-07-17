document.addEventListener('click', function(event) {
    if (event.target.classList.contains('copy-btn')) {
        const button = event.target;
        const targetId = button.dataset.target;
        const contentElement = document.getElementById(targetId);

        if (contentElement) {
            navigator.clipboard.writeText(contentElement.textContent).then(() => {
                const originalText = button.textContent;
                button.textContent = '¡Copiado!';
                button.classList.add('copied');
                
                setTimeout(() => {
                    button.textContent = originalText;
                    button.classList.remove('copied');
                }, 2000);
            }).catch(err => {
                console.error('Error al copiar el log: ', err);
            });
        }
    }
});
document.addEventListener('click', (event) => {
    if (!(event.target instanceof Element)) {
        return;
    }

    const navigationLink = event.target.closest('nav[aria-label="Navegación móvil"] a');

    navigationLink?.closest('details')?.removeAttribute('open');
});

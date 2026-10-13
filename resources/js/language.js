// This preference has no application expiry. Storage may still be cleared by the browser.
const key = 'juanovio.language';
document.addEventListener('click', (event) => {
    const choice = event.target instanceof Element ? event.target.closest('[data-language-choice]') : null;
    if (!choice || !['en', 'es'].includes(choice.dataset.languageChoice)) return;
    try { localStorage.setItem(key, choice.dataset.languageChoice); } catch { /* Server cookie remains available. */ }
});

// Restore the server cookie after browser cookie lifetime limits, keeping localStorage authoritative.
try {
    const locale = localStorage.getItem(key);
    const current = document.documentElement.lang;
    if (['en', 'es'].includes(locale) && locale !== current) {
        const choice = document.querySelector('[data-language-choice="' + locale + '"]');
        if (choice) window.location.replace(choice.href);
    }
} catch { /* Language links work when browser storage is unavailable. */ }

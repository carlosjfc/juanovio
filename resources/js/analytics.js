const storageKey = 'juanovio-analytics-consent';
let settings;
let preference;
let loaded = false;
let trackedPage;

function command() {
    window.dataLayer ??= [];
    window.dataLayer.push(arguments);
}

function consent(analytics) {
    return {
        analytics_storage: analytics ? 'granted' : 'denied',
        ad_storage: 'denied',
        ad_user_data: 'denied',
        ad_personalization: 'denied',
    };
}

function readPreference() {
    try {
        const stored = JSON.parse(localStorage.getItem(storageKey));
        const age = Date.now() - stored?.decidedAt;
        if (stored?.version === settings.version && typeof stored.analytics === 'boolean'
            && Number.isFinite(age) && age >= 0 && age < settings.maxAgeDays * 86400000) {
            return stored;
        }
    } catch { /* Unavailable or invalid storage leaves consent denied. */ }
}

function pageView() {
    if (!preference?.analytics) return;
    // Exclude query strings and fragments, which may contain personal information.
    const location = window.location.origin + window.location.pathname;
    if (trackedPage === location) return;
    const payload = { page_location: location, page_title: document.title, page_referrer: '' };
    command('set', { page_location: location, page_referrer: '' });
    if (settings.driver === 'gtm') {
        window.dataLayer.push({ event: 'juanovio_page_view', ...payload });
    } else {
        command('event', 'page_view', { ...payload, send_to: settings.id });
    }
    trackedPage = location;
}

function loadProvider() {
    if (!preference?.analytics) return;
    if (!loaded) {
        command('set', { page_location: window.location.origin + window.location.pathname, page_referrer: '' });
        const script = document.createElement('script');
        script.async = true;
        script.dataset.analyticsLoader = settings.driver;
        if (settings.driver === 'gtm') {
            window.dataLayer.push({ 'gtm.start': Date.now(), event: 'gtm.js' });
            script.src = `https://www.googletagmanager.com/gtm.js?id=${settings.id}`;
        } else {
            command('js', new Date());
            command('config', settings.id, { send_page_view: false, page_location: window.location.origin + window.location.pathname, page_referrer: '' });
            script.src = `https://www.googletagmanager.com/gtag/js?id=${settings.id}`;
        }
        loaded = true;
        document.head.append(script);
    }
    pageView();
}

function refresh() {
    const banner = document.querySelector('[data-analytics-banner]');
    if (banner) banner.hidden = Boolean(preference);
    loadProvider();
}

function initialize() {
    const element = document.getElementById('analytics-settings');
    if (!element || settings) return;
    settings = JSON.parse(element.textContent);
    command('consent', 'default', consent(false));
    command('set', 'ads_data_redaction', true);
    preference = readPreference();
    if (preference) command('consent', 'update', consent(preference.analytics));
    refresh();
}

document.addEventListener('click', (event) => {
    if (!settings || !(event.target instanceof Element)) return;
    const banner = document.querySelector('[data-analytics-banner]');
    if (event.target.closest('[data-analytics-preferences]')) {
        banner.hidden = false;
        banner.querySelector('[data-analytics-choice]').focus();
    }
    const button = event.target.closest('[data-analytics-choice]');
    if (!button) return;
    preference = { version: settings.version, analytics: button.dataset.analyticsChoice === 'accept', decidedAt: Date.now() };
    let saved = false;
    try {
        localStorage.setItem(storageKey, JSON.stringify(preference));
        saved = true;
    } catch { /* The choice still applies to this page. */ }
    command('consent', 'update', consent(preference.analytics));
    // Reload after withdrawal so already-loaded third-party scripts cannot keep running.
    if (loaded && !preference.analytics && saved) {
        window.location.reload();
        return;
    }
    banner.querySelector('[data-analytics-status]').textContent = saved
        ? 'Your analytics preference has been saved.'
        : 'Your choice applies to this page. Browser storage is unavailable; preferences cannot be saved.';
    document.querySelector('[data-analytics-preferences]').focus();
    refresh();
});

document.addEventListener('inquiry-submitted', (event) => {
    if (!settings || !preference?.analytics) return;
    const type = event.detail?.inquiry_type;
    if (!['buy', 'supply', 'partnership', 'general'].includes(type)) return;
    const payload = { inquiry_type: type };
    if (settings.driver === 'gtm') {
        window.dataLayer.push({ event: 'generate_lead', ...payload });
    } else {
        command('event', 'generate_lead', { ...payload, send_to: settings.id });
    }
});

window.addEventListener('storage', (event) => {
    if (settings && event.key === storageKey) window.location.reload();
});
document.addEventListener('DOMContentLoaded', initialize);
document.addEventListener('livewire:navigated', () => { initialize(); refresh(); });
if (document.readyState !== 'loading') initialize();

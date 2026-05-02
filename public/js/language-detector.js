class LanguageManager {
    constructor() {
        this.supportedLanguages = ['en', 'ku', 'ar'];
        this.rtlLanguages = ['ar', 'ku'];
        this.currentLocale = document.documentElement.lang || 'en';
        this.init();
    }

    init() {
        this.updateDirection();
        this.setupEventListeners();
    }

    updateDirection() {
        const html = document.documentElement;
        const isRtl = this.rtlLanguages.includes(this.currentLocale);

        html.setAttribute('dir', isRtl ? 'rtl' : 'ltr');
        html.setAttribute('lang', this.currentLocale);

        // Update Bootstrap classes if needed
        if (isRtl) {
            document.body.classList.add('rtl');
            document.body.classList.remove('ltr');
        } else {
            document.body.classList.add('ltr');
            document.body.classList.remove('rtl');
        }

        // Trigger custom event
        window.dispatchEvent(new CustomEvent('directionChanged', {
            detail: { direction: isRtl ? 'rtl' : 'ltr', locale: this.currentLocale }
        }));
    }

    switchLanguage(locale) {
        if (this.supportedLanguages.includes(locale)) {
            this.currentLocale = locale;
            window.location.href = `/language/${locale}`;
        }
    }

    setupEventListeners() {
        // Listen for direction changes
        window.addEventListener('directionChanged', (e) => {
            this.adjustUIForDirection(e.detail.direction);
        });
    }

    adjustUIForDirection(direction) {
        const isRtl = direction === 'rtl';

        // Adjust any dynamic elements that need manual direction handling
        document.querySelectorAll('[data-rtl-aware]').forEach(element => {
            if (isRtl) {
                element.classList.add('rtl-aware');
            } else {
                element.classList.remove('rtl-aware');
            }
        });
    }

    // Get translation key (for client-side translations)
    translate(key, replacements = {}) {
        // This would typically fetch from a client-side translation store
        // For now, return the key
        let translation = window.translations?.[this.currentLocale]?.[key] || key;

        Object.keys(replacements).forEach(placeholder => {
            translation = translation.replace(`:${placeholder}`, replacements[placeholder]);
        });

        return translation;
    }
}

// Initialize language manager
const languageManager = new LanguageManager();

// Export for use in other scripts
window.languageManager = languageManager;

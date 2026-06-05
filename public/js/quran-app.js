/**
 * Quran Application - Main JavaScript
 * Handles sidebar, header interactions, and RTL/LTR support
 */

class QuranApp {
    constructor() {
        this.sidebar = document.getElementById('mainSidebar');
        this.sidebarOverlay = document.getElementById('sidebarOverlay');
        this.sidebarToggle = document.getElementById('mobileMenuBtn');
        this.sidebarToggleBtn = document.getElementById('sidebarToggleBtn');
        this.closeSidebarBtn = document.getElementById('closeSidebarBtn');
        this.collapseSidebarBtn = document.getElementById('collapseSidebarBtn');
        this.languageDropdown = document.querySelector('.quran-language-dropdown');
        this.notificationsDropdown = document.querySelector('.quran-notifications-dropdown');
        this.userDropdown = document.querySelector('.quran-user-dropdown');
        this.searchResults = document.getElementById('searchResults');
        this.audioPlayer = document.getElementById('audioPlayer');

        this.init();
    }

    init() {
        this.setupSidebar();
        this.setupDropdowns();
        this.setupSearch();
        this.setupAudioPlayer();
        this.setupRTLSupport();
        this.setupKeyboardShortcuts();
        this.setupThemeToggle();
    }

    setupSidebar() {
        // Mobile sidebar toggle (from header)
        if (this.sidebarToggle) {
            this.sidebarToggle.addEventListener('click', () => {
                this.sidebar.classList.add('mobile-open');
                this.sidebarOverlay.classList.add('active');
            });
        }

        // Mobile sidebar toggle (floating button on page)
        if (this.sidebarToggleBtn) {
            this.sidebarToggleBtn.addEventListener('click', () => {
                this.sidebar.classList.add('mobile-open');
                this.sidebarOverlay.classList.add('active');
            });
        }

        // Close sidebar from close button
        if (this.closeSidebarBtn) {
            this.closeSidebarBtn.addEventListener('click', () => {
                this.sidebar.classList.remove('mobile-open');
                this.sidebarOverlay.classList.remove('active');
            });
        }

        // Close sidebar when clicking overlay
        if (this.sidebarOverlay) {
            this.sidebarOverlay.addEventListener('click', () => {
                this.sidebar.classList.remove('mobile-open');
                this.sidebarOverlay.classList.remove('active');
            });
        }

        // Collapse/Expand sidebar (desktop)
        if (this.collapseSidebarBtn) {
            this.collapseSidebarBtn.addEventListener('click', () => {
                this.sidebar.classList.toggle('collapsed');
                document.body.classList.toggle('sidebar-collapsed');

                // Update icon direction
                const icon = this.collapseSidebarBtn.querySelector('i');
                if (this.sidebar.classList.contains('collapsed')) {
                    icon.classList.remove('bi-chevron-double-left');
                    icon.classList.add('bi-chevron-double-right');
                } else {
                    icon.classList.remove('bi-chevron-double-right');
                    icon.classList.add('bi-chevron-double-left');
                }
            });
        }

        // Handle submenu toggle
        document.querySelectorAll('.quran-nav-item.has-submenu').forEach(item => {
            item.addEventListener('click', (e) => {
                e.preventDefault();
                const target = item.getAttribute('data-bs-target');
                const submenu = document.querySelector(target);

                if (submenu) {
                    const isExpanded = item.getAttribute('aria-expanded') === 'true';
                    item.setAttribute('aria-expanded', !isExpanded);
                }
            });
        });
    }

    setupDropdowns() {
        // Language dropdown
        const langBtn = document.getElementById('languageDropdownBtn');
        if (langBtn && this.languageDropdown) {
            langBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                this.closeAllDropdowns();
                this.languageDropdown.classList.toggle('show');
            });
        }

        // Notifications dropdown
        const notifBtn = document.getElementById('notificationsBtn');
        if (notifBtn && this.notificationsDropdown) {
            notifBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                this.closeAllDropdowns();
                this.notificationsDropdown.classList.toggle('show');
            });
        }

        // User dropdown
        const userBtn = document.getElementById('userMenuBtn');
        if (userBtn && this.userDropdown) {
            userBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                this.closeAllDropdowns();
                this.userDropdown.classList.toggle('show');
            });
        }

        // Close dropdowns when clicking outside
        document.addEventListener('click', () => {
            this.closeAllDropdowns();
        });
    }

    closeAllDropdowns() {
        if (this.languageDropdown) this.languageDropdown.classList.remove('show');
        if (this.notificationsDropdown) this.notificationsDropdown.classList.remove('show');
        if (this.userDropdown) this.userDropdown.classList.remove('show');
        if (this.searchResults) this.searchResults.classList.remove('show');
    }

    setupSearch() {
        const searchInput = document.getElementById('globalSearchInput');

        if (searchInput && this.searchResults) {
            searchInput.addEventListener('focus', () => {
                this.closeAllDropdowns();
                this.searchResults.classList.add('show');
            });

            searchInput.addEventListener('input', (e) => {
                const query = e.target.value;
                if (query.length > 2) {
                    this.performSearch(query);
                }
            });

            // Close search results when clicking outside
            document.addEventListener('click', (e) => {
                if (!searchInput.contains(e.target) && !this.searchResults.contains(e.target)) {
                    this.searchResults.classList.remove('show');
                }
            });
        }
    }

    performSearch(query) {
        // Implement search functionality
        const resultsList = document.querySelector('.quran-search-results-list');
        if (resultsList) {
            // Show loading state
            resultsList.innerHTML = '<div class="text-center p-3">Searching...</div>';

            // Make API call
            fetch(`/api/search?q=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(data => {
                    this.displaySearchResults(data);
                })
                .catch(error => {
                    resultsList.innerHTML = '<div class="text-center p-3 text-danger">Search failed</div>';
                });
        }
    }

    displaySearchResults(results) {
        const resultsList = document.querySelector('.quran-search-results-list');
        if (!resultsList) return;

        if (results.length === 0) {
            resultsList.innerHTML = '<div class="text-center p-3">No results found</div>';
            return;
        }

        let html = '';
        results.forEach(result => {
            html += `
                <a href="${result.url}" class="quran-search-result-item">
                    <div class="d-flex align-items-center">
                        <span class="badge bg-primary me-2">${result.type}</span>
                        <span>${result.title}</span>
                    </div>
                    <small class="text-muted">${result.description}</small>
                </a>
            `;
        });

        resultsList.innerHTML = html;
    }

    setupAudioPlayer() {
        const audioToggle = document.getElementById('audioPlayerToggle');
        const closeAudio = document.getElementById('closeAudioPlayer');

        if (audioToggle && this.audioPlayer) {
            audioToggle.addEventListener('click', () => {
                this.audioPlayer.style.display = 'block';
            });
        }

        if (closeAudio && this.audioPlayer) {
            closeAudio.addEventListener('click', () => {
                this.audioPlayer.style.display = 'none';
            });
        }
    }

    setupRTLSupport() {
        const html = document.documentElement;
        const locale = html.getAttribute('lang') || 'en';
        const rtlLocales = ['ar', 'ku'];

        if (rtlLocales.includes(locale)) {
            html.setAttribute('dir', 'rtl');
            this.adjustUIForRTL();
        } else {
            html.setAttribute('dir', 'ltr');
        }

        // Listen for language changes
        window.addEventListener('languageChanged', (e) => {
            const newLocale = e.detail.locale;
            if (rtlLocales.includes(newLocale)) {
                html.setAttribute('dir', 'rtl');
                this.adjustUIForRTL();
            } else {
                html.setAttribute('dir', 'ltr');
            }
        });
    }

    adjustUIForRTL() {
        // Adjust Bootstrap dropdowns
        document.querySelectorAll('.dropdown-menu').forEach(menu => {
            menu.classList.add('dropdown-menu-end');
        });

        // Adjust any specific elements
        document.querySelectorAll('[data-rtl-aware]').forEach(el => {
            el.classList.add('rtl-aware');
        });
    }

    setupKeyboardShortcuts() {
        document.addEventListener('keydown', (e) => {
            // Ctrl/Cmd + K for search
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                document.getElementById('globalSearchInput')?.focus();
            }

            // Escape to close modals/dropdowns
            if (e.key === 'Escape') {
                this.closeAllDropdowns();
                if (this.sidebar) {
                    this.sidebar.classList.remove('mobile-open');
                    this.sidebarOverlay?.classList.remove('active');
                }
            }
        });
    }

    setupThemeToggle() {
        const themeToggle = document.getElementById('themeSwitch');
        const themeToggleBtn = document.getElementById('themeToggleBtn');
        const themeToggleHeaderBtn = document.getElementById('themeToggleHeaderBtn');
        const themeHeaderIcon = document.getElementById('themeHeaderIcon');

        // Load saved theme
        const savedTheme = localStorage.getItem('theme') || localStorage.getItem('quran-theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);
        if (savedTheme === 'dark') {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
        
        const updateUI = (theme) => {
            if (themeToggle) {
                themeToggle.checked = theme === 'dark';
            }
            if (themeHeaderIcon) {
                if (theme === 'dark') {
                    themeHeaderIcon.className = 'bi bi-sun';
                } else {
                    themeHeaderIcon.className = 'bi bi-moon-stars';
                }
            }
        };

        updateUI(savedTheme);

        // Toggle theme
        const toggleTheme = () => {
            const currentTheme = document.documentElement.getAttribute('data-theme') || (document.documentElement.classList.contains('dark') ? 'dark' : 'light');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';

            document.documentElement.setAttribute('data-theme', newTheme);
            if (newTheme === 'dark') {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
            
            localStorage.setItem('theme', newTheme);
            localStorage.setItem('quran-theme', newTheme);

            updateUI(newTheme);
        };

        if (themeToggle) {
            themeToggle.addEventListener('change', toggleTheme);
        }

        if (themeToggleBtn) {
            themeToggleBtn.addEventListener('click', toggleTheme);
        }

        if (themeToggleHeaderBtn) {
            themeToggleHeaderBtn.addEventListener('click', toggleTheme);
        }
    }
}

// Initialize app when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.quranApp = new QuranApp();
});

// Surah List Manager
class SurahListManager {
    constructor() {
        this.surahList = document.getElementById('surahListContainer');
        this.searchInput = document.getElementById('surahSearchInput');
        this.surahs = [];

        this.init();
    }

    async init() {
        await this.loadSurahs();
        this.setupSearch();
    }

    async loadSurahs() {
        try {
            const response = await fetch('/api/surahs');
            this.surahs = await response.json();
            this.renderSurahs(this.surahs);
        } catch (error) {
            console.error('Failed to load surahs:', error);
        }
    }

    renderSurahs(surahs) {
        if (!this.surahList) return;

        const html = surahs.map(surah => `
            <a href="/surah/${surah.id}" class="quran-surah-item">
                <span class="surah-number">${surah.id}</span>
                <span class="surah-name">${surah.name_arabic}</span>
                <span class="surah-trans">${surah.name_english}</span>
                <span class="surah-info">${surah.ayahs_count} Ayahs</span>
            </a>
        `).join('');

        this.surahList.innerHTML = html;
    }

    setupSearch() {
        if (!this.searchInput) return;

        this.searchInput.addEventListener('input', (e) => {
            const query = e.target.value.toLowerCase();
            const filtered = this.surahs.filter(surah =>
                surah.name_arabic.includes(query) ||
                surah.name_english.toLowerCase().includes(query) ||
                surah.id.toString() === query
            );
            this.renderSurahs(filtered);
        });
    }
}

// Initialize surah list if on appropriate page
if (document.getElementById('surahListContainer')) {
    new SurahListManager();
}

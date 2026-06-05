<!-- Main Footer -->
<footer class="quran-footer">
    <div class="quran-footer-container">
        <!-- Footer Top Section -->
        <div class="quran-footer-top">
            <div class="row g-4">
                <!-- About Section -->
                <div class="col-lg-4 col-md-6">
                    <div class="quran-footer-about">
                        <div class="quran-footer-logo">
                            @php
                                $settings = \App\Models\Setting::first();
                            @endphp
                            @if($settings->app_logo)
                                <img src="{{ asset('storage/' . $settings->app_logo) }}" alt="Logo" class="quran-footer-logo-icon" style="width: 100px; height: 100px; object-fit: cover; border-radius: 12px;">
                            @else
                                <i class="bi bi-book quran-logo-icon"></i>
                            @endif
                            <h5>{{ __('common.app_name') }}</h5>
                        </div>
                        <p class="quran-footer-description">
                            {{ __('footer.quran_description') }}
                        </p>
                        <div class="quran-footer-social">
                            <a href="#" class="quran-social-link">
                                <i class="bi bi-facebook"></i>
                            </a>
                            <a href="#" class="quran-social-link">
                                <i class="bi bi-twitter-x"></i>
                            </a>
                            <a href="#" class="quran-social-link">
                                <i class="bi bi-telegram"></i>
                            </a>
                            <a href="#" class="quran-social-link">
                                <i class="bi bi-youtube"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="col-lg-2 col-md-6">
                    <div class="quran-footer-links">
                        <h6 class="quran-footer-title">{{ __('footer.quick_links') }}</h6>
                        <ul class="quran-footer-list">
                            <li><a href="{{ route('surahs.index') }}">{{ __('sidebar.surahs') }}</a></li>
                            <li><a href="#">{{ __('sidebar.juz') }}</a></li>
                            <li><a href="#">{{ __('sidebar.reciters') }}</a></li>
                            <li><a href="#">{{ __('sidebar.tafsir') }}</a></li>
                            <li><a href="#">{{ __('sidebar.tajweed_rules') }}</a></li>
                        </ul>
                    </div>
                </div>

                <!-- Resources -->
                <div class="col-lg-2 col-md-6">
                    <div class="quran-footer-links">
                        <h6 class="quran-footer-title">{{ __('footer.resources') }}</h6>
                        <ul class="quran-footer-list">
                            <li><a href="#">{{ __('footer.help_center') }}</a></li>
                            <li><a href="#">{{ __('footer.quran_academy') }}</a></li>
                            <li><a href="#">{{ __('footer.blog') }}</a></li>
                            <li><a href="#">{{ __('footer.developers') }}</a></li>
                            <li><a href="#">{{ __('footer.api_docs') }}</a></li>
                        </ul>
                    </div>
                </div>

                <!-- Contact & Newsletter -->
                <div class="col-lg-4 col-md-6">
                    <div class="quran-footer-newsletter">
                        <h6 class="quran-footer-title">{{ __('footer.stay_updated') }}</h6>
                        <p class="quran-footer-text">{{ __('footer.newsletter_text') }}</p>
                        <form class="quran-newsletter-form">
                            <div class="quran-newsletter-input-group">
                                <input type="email"
                                       class="form-control"
                                       placeholder="{{ __('footer.enter_email') }}"
                                       required>
                                <button type="submit" class="quran-newsletter-btn">
                                    <i class="bi bi-send"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer Bottom Section -->
        <div class="quran-footer-bottom">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <div class="quran-copyright">
                        <p class="mb-0">
                            &copy; {{ date('Y') }} {{ __('common.app_name') }}.
                            {{ __('footer.all_rights_reserved') }}
                        </p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="quran-footer-bottom-links">
                        <a href="#">{{ __('footer.privacy_policy') }}</a>
                        <span class="quran-footer-divider">|</span>
                        <a href="#">{{ __('footer.terms_of_service') }}</a>
                        <span class="quran-footer-divider">|</span>
                        <a href="#">{{ __('footer.cookie_policy') }}</a>
                        <span class="quran-footer-divider">|</span>
                        <a href="#">{{ __('footer.contact_us') }}</a>
                    </div>
                </div>
            </div>

            <!-- Quran Verse -->
            <div class="quran-footer-verse">
                <p class="quran-verse-arabic">
                    إِنَّ هَٰذَا الْقُرْآنَ يَهْدِي لِلَّتِي هِيَ أَقْوَمُ
                </p>
                <p class="quran-verse-translation">
                    "Indeed, this Qur'an guides to that which is most suitable"
                </p>
                <p class="quran-verse-translation-kurdish">
                    "بەراستی ئەم قورئانە ڕێنمایی دەکات بۆ ئەوەی باشترین ڕێگایە"
                </p>
                <p class="quran-verse-reference">
                    {{ __('footer.quran_verse_ref') }} (17:9)
                </p>
            </div>
        </div>
    </div>
</footer>

<!-- Floating Action Button -->
<div class="quran-fab">
    <button class="quran-fab-btn" id="fabMainBtn">
        <i class="bi bi-plus-lg"></i>
    </button>
    <div class="quran-fab-menu">
        <a href="#" class="quran-fab-item" title="{{ __('sidebar.bookmark') }}">
            <i class="bi bi-bookmark-plus"></i>
        </a>
        <a href="#" class="quran-fab-item" title="{{ __('sidebar.add_to_favorites') }}">
            <i class="bi bi-heart"></i>
        </a>
        <a href="#" class="quran-fab-item" title="{{ __('sidebar.start_memorization') }}">
            <i class="bi bi-journal-bookmark-fill"></i>
        </a>
        <a href="#" class="quran-fab-item" title="{{ __('sidebar.share') }}">
            <i class="bi bi-share"></i>
        </a>
    </div>
</div>

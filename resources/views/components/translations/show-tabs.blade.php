@props([
    'model',
    'activeLanguages'
])

@php
    $translatableFields = $model->getTranslatableAttributes();
    $componentId = 'trans-tabs-' . strtolower(class_basename($model)) . '-' . $model->getKey();
@endphp

<div class="quran-card mb-4" id="{{ $componentId }}">
    <div class="quran-card-header border-bottom-0">
        <h5 class="quran-card-title mb-0">
            <i class="bi bi-translate me-2 text-primary"></i>
            {{ __('common.translations') }}
        </h5>
    </div>
    <div class="quran-card-body pt-0">
        @if(empty($translatableFields))
            <div class="text-center p-3 text-muted">
                {{ __('common.no_data') }}
            </div>
        @else
            <!-- Nav Tabs -->
            <ul class="nav nav-tabs quran-tabs mb-3" id="{{ $componentId }}-tabs" role="tablist">
                @foreach($activeLanguages as $index => $lang)
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ $index === 0 ? 'active' : '' }}" 
                                id="{{ $componentId }}-tab-{{ $lang->code }}" 
                                data-bs-toggle="tab" 
                                data-bs-target="#{{ $componentId }}-pane-{{ $lang->code }}" 
                                type="button" 
                                role="tab" 
                                aria-controls="{{ $componentId }}-pane-{{ $lang->code }}" 
                                aria-selected="{{ $index === 0 ? 'true' : 'false' }}">
                            <span class="badge bg-light text-dark border me-1">{{ strtoupper($lang->code) }}</span>
                            {{ $lang->name }}
                        </button>
                    </li>
                @endforeach
            </ul>

            <!-- Tab Content -->
            <div class="tab-content" id="{{ $componentId }}-content">
                @foreach($activeLanguages as $index => $lang)
                    <div class="tab-pane fade {{ $index === 0 ? 'show active' : '' }}" 
                         id="{{ $componentId }}-pane-{{ $lang->code }}" 
                         role="tabpanel" 
                         aria-labelledby="{{ $componentId }}-tab-{{ $lang->code }}"
                         tabindex="0">
                        
                        @if($index === 0)
                            <!-- Active tab rendered immediately -->
                            <div class="p-3 bg-light rounded-3 border-start border-primary border-3">
                                @foreach($translatableFields as $field)
                                    @php
                                        $val = $model->translations->where('locale', $lang->code)->first()?->{$field};
                                    @endphp
                                    <div class="{{ !$loop->last ? 'mb-3' : '' }}">
                                        <label class="text-muted small d-block mb-1">
                                            {{ __('common.' . $field) }}
                                        </label>
                                        @if($val !== null && $val !== '')
                                            <div class="{{ $lang->typography_class }} {{ $lang->align_class }}" 
                                                 dir="{{ $lang->is_rtl ? 'rtl' : 'ltr' }}" 
                                                 style="text-align: {{ $lang->text_align }}; font-size: 1.1rem; line-height: 1.6; color: var(--quran-text-primary);">
                                                @if(in_array($field, ['description', 'explanation']))
                                                    {!! nl2br(e($val)) !!}
                                                @else
                                                    {{ $val }}
                                                @endif
                                            </div>
                                        @else
                                            <span class="badge bg-light text-muted border small">{{ __('common.missing_translation') }}</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <!-- Inactive tabs rendered lazily -->
                            <template class="lazy-tab-template">
                                <div class="p-3 bg-light rounded-3 border-start border-primary border-3">
                                    @foreach($translatableFields as $field)
                                        @php
                                            $val = $model->translations->where('locale', $lang->code)->first()?->{$field};
                                        @endphp
                                        <div class="{{ !$loop->last ? 'mb-3' : '' }}">
                                            <label class="text-muted small d-block mb-1">
                                                {{ __('common.' . $field) }}
                                            </label>
                                            @if($val !== null && $val !== '')
                                                <div class="{{ $lang->typography_class }} {{ $lang->align_class }}" 
                                                     dir="{{ $lang->is_rtl ? 'rtl' : 'ltr' }}" 
                                                     style="text-align: {{ $lang->text_align }}; font-size: 1.1rem; line-height: 1.6; color: var(--quran-text-primary);">
                                                    @if(in_array($field, ['description', 'explanation']))
                                                        {!! nl2br(e($val)) !!}
                                                    @else
                                                        {{ $val }}
                                                    @endif
                                                </div>
                                            @else
                                                <span class="badge bg-light text-muted border small">{{ __('common.missing_translation') }}</span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </template>
                            <div class="lazy-tab-placeholder d-flex align-items-center justify-content-center p-4">
                                <span class="spinner-border spinner-border-sm text-primary me-2" role="status" aria-hidden="true"></span>
                                <span class="text-muted small">{{ __('common.loading') }}</span>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

@once
<script>
    (function() {
        function initLazyTabs() {
            document.body.addEventListener('shown.bs.tab', function(event) {
                const targetId = event.target.getAttribute('data-bs-target');
                if (!targetId) return;
                const targetPane = document.querySelector(targetId);
                if (targetPane) {
                    const placeholder = targetPane.querySelector('.lazy-tab-placeholder');
                    const template = targetPane.querySelector('.lazy-tab-template');
                    if (placeholder && template) {
                        const clone = template.content.cloneNode(true);
                        placeholder.before(clone);
                        placeholder.remove();
                        template.remove();
                    }
                }
            });
        }
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initLazyTabs);
        } else {
            initLazyTabs();
        }
    })();
</script>
@endonce

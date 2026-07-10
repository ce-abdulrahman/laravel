{{-- resources/views/tajweed-segments/_form.blade.php --}}
@php
    /** @var \App\Models\AyahTajweedSegment $tajweedSegment */
    $selectedRule = $selectedRule ?? $tajweedSegment->tajweedRule ?? null;
    $selectedAyah = $selectedAyah ?? $tajweedSegment->ayah ?? null;
@endphp

<div class="quran-form">
    <div class="row g-4">
        <!-- Selection -->
        <div class="col-12">
            <div class="quran-form-section">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="quran-form-section-title mb-0">
                        <i class="bi bi-journal-text me-2"></i>
                        {{ __('tajweed_segments.sections.selection') }}
                    </h6>
                    @if(!$tajweedSegment->exists)
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="multi_mode" name="multi_mode">
                        <label class="form-check-label fw-bold text-primary mb-0" for="multi_mode">
                            <i class="bi bi-layers-half me-1"></i>
                            {{ app()->getLocale() == 'ku' ? 'دۆخی فرە سێگمێنت' : (app()->getLocale() == 'ar' ? 'وضع المقاطع المتعددة' : 'Multi-Segment Mode') }}
                        </label>
                    </div>
                    @endif
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="quran-form-label" for="tajweed_rule_id">
                            {{ __('tajweed_segments.fields.tajweed_rule') }}
                            <span class="text-danger">*</span>
                        </label>
                        <select name="tajweed_rule_id" id="tajweed_rule_id" 
                                class="quran-form-select @error('tajweed_rule_id') is-invalid @enderror" required>
                            <option value="">{{ __('tajweed_segments.select_rule') }}</option>
                            @foreach($tajweedRules as $rule)
                            <option value="{{ $rule->id }}" 
                                {{ old('tajweed_rule_id', $tajweedSegment->tajweed_rule_id) == $rule->id ? 'selected' : '' }}
                                data-color="{{ $rule->color_code }}">
                                {{ $rule->name }} ({{ $rule->category?->name ?? 'No Category' }})
                            </option>
                            @endforeach
                        </select>
                        @error('tajweed_rule_id')
                        <div class="quran-invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="quran-form-label" for="ayah_id">
                            {{ __('tajweed_segments.fields.ayah') }}
                            <span class="text-danger">*</span>
                        </label>
                        <select name="ayah_id" id="ayah_id" 
                                class="quran-form-select @error('ayah_id') is-invalid @enderror" required>
                            <option value="">{{ __('tajweed_segments.select_ayah') }}</option>
                            @foreach($ayahs as $ayah)
                            <option value="{{ $ayah->id }}" 
                                {{ old('ayah_id', $tajweedSegment->ayah_id) == $ayah->id ? 'selected' : '' }}>
                                {{ $ayah->surah->number }}:{{ $ayah->ayah_number }} - 
                                {{ $ayah->surah->name_ar }} 
                                ({{ $ayah->tajweed_segments_count ?? 0 }} {{ app()->getLocale() == 'ku' ? 'سێگمێنت' : (app()->getLocale() == 'ar' ? 'مقاطع' : 'segments') }})
                            </option>
                            @endforeach
                        </select>
                        @error('ayah_id')
                        <div class="quran-invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div id="selected-ayah-container" class="mt-3 p-3 bg-light rounded-3" style="display: {{ $selectedAyah ? 'block' : 'none' }};">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="quran-detail-label  mb-0">{{ __('tajweed_segments.selected_ayah') }}</label>
                        <small class="text-muted">
                            <i class="bi bi-info-circle me-1"></i>
                            {{ app()->getLocale() == 'ku' ? 'تێکستەکە دیاری بکە بۆ دانانی ئیندێکسەکان' : (app()->getLocale() == 'ar' ? 'حدد النص لتحديد المؤشرات' : 'Select text directly to set indices') }}
                        </small>
                    </div>
                    <div id="ayah-preview-text" class="arabic-text text-center p-3 bg-white rounded border" style="font-size: 24px; line-height: 2; cursor: pointer; user-select: text; letter-spacing: 0.5px;" data-original="{{ $selectedAyah ? $selectedAyah->text_uthmani : '' }}">
                        {{ $selectedAyah ? $selectedAyah->text_uthmani : '' }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Segment Details -->
        <div class="col-12">
            <div class="quran-form-section">
                <h6 class="quran-form-section-title">
                    <i class="bi bi-puzzle me-2"></i>
                    {{ __('tajweed_segments.sections.segment_details') }}
                </h6>

                <div class="row g-3">
                    <div class="col-12">
                        <label class="quran-form-label" for="matched_text">
                            {{ __('tajweed_segments.fields.matched_text') }}
                            <span class="text-danger">*</span>
                        </label>
                        <textarea name="matched_text" id="matched_text" rows="2"
                                  class="quran-form-control arabic-text @error('matched_text') is-invalid @enderror"
                                  dir="rtl"
                                  placeholder="{{ __('tajweed_segments.placeholders.matched_text') }}"
                                  required>{{ old('matched_text', $tajweedSegment->matched_text) }}</textarea>
                        @error('matched_text')
                        <div class="quran-invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="quran-form-label" for="start_index">
                            {{ __('tajweed_segments.fields.start_index') }}
                        </label>
                        <input type="number" name="start_index" id="start_index" 
                               class="quran-form-control @error('start_index') is-invalid @enderror"
                               value="{{ old('start_index', $tajweedSegment->start_index) }}" min="0">
                        @error('start_index')
                        <div class="quran-invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="quran-form-label" for="end_index">
                            {{ __('tajweed_segments.fields.end_index') }}
                        </label>
                        <input type="number" name="end_index" id="end_index" 
                               class="quran-form-control @error('end_index') is-invalid @enderror"
                               value="{{ old('end_index', $tajweedSegment->end_index) }}" min="0">
                        @error('end_index')
                        <div class="quran-invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label class="quran-form-label" for="metadata">
                            {{ __('tajweed_segments.fields.metadata') }}
                        </label>
                        <textarea name="metadata" id="metadata" rows="3"
                                  class="quran-form-control font-monospace @error('metadata') is-invalid @enderror"
                                  placeholder="{{ __('tajweed_segments.placeholders.metadata') }}">{{ old('metadata', $tajweedSegment->metadata ? json_encode($tajweedSegment->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : '') }}</textarea>
                        <small class="text-muted d-block mt-1">Must be valid JSON formatting, e.g. <code>{"duration": "2_harakat", "confidence": 100}</code></small>
                        @error('metadata')
                        <div class="quran-invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label class="quran-form-label" for="note">
                            {{ __('tajweed_segments.fields.note') }}
                        </label>
                        <textarea name="note" id="note" rows="2"
                                  class="quran-form-control @error('note') is-invalid @enderror"
                                  placeholder="{{ __('tajweed_segments.placeholders.note') }}">{{ old('note', $tajweedSegment->note) }}</textarea>
                        @error('note')
                        <div class="quran-invalid-feedback">{{ $message }}</div>
                        @enderror
                    @if(!$tajweedSegment->exists)
                    <div class="col-12 mt-3 multi-mode-only" style="display: none;">
                        <button type="button" id="add-segment-btn" class="quran-btn quran-btn-outline-primary w-100 py-2 fw-bold">
                            <i class="bi bi-plus-circle me-1"></i>
                            {{ app()->getLocale() == 'ku' ? 'زیادکردن بۆ لیستی سێگمێنتەکان' : (app()->getLocale() == 'ar' ? 'إضافة إلى قائمة المقاطع' : 'Add Segment to List') }}
                        </button>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        @if(!$tajweedSegment->exists)
        <!-- Multi-Segment List Table -->
        <div class="col-12 multi-mode-only" style="display: none;">
            <div class="quran-form-section">
                <h6 class="quran-form-section-title">
                    <i class="bi bi-list-task me-2"></i>
                    {{ app()->getLocale() == 'ku' ? 'سێگمێنتە زیادکراوەکان بۆ پاشەکەوتکردن' : (app()->getLocale() == 'ar' ? 'المقاطع المضافة للحفظ' : 'Segments to be Saved') }}
                </h6>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle mb-0" id="segments-table">
                        <thead class="table-light">
                            <tr>
                                <th>{{ app()->getLocale() == 'ku' ? 'یاسا' : (app()->getLocale() == 'ar' ? 'القاعدة' : 'Rule') }}</th>
                                <th>{{ app()->getLocale() == 'ku' ? 'دەق' : (app()->getLocale() == 'ar' ? 'النص' : 'Text') }}</th>
                                <th>{{ app()->getLocale() == 'ku' ? 'مەودا (ئیندێکس)' : (app()->getLocale() == 'ar' ? 'المدى' : 'Range') }}</th>
                                <th>{{ app()->getLocale() == 'ku' ? 'تێبینی' : (app()->getLocale() == 'ar' ? 'ملاحظة' : 'Note') }}</th>
                                <th class="text-center" style="width: 80px;">{{ app()->getLocale() == 'ku' ? 'کردار' : (app()->getLocale() == 'ar' ? 'الإجراء' : 'Action') }}</th>
                            </tr>
                        </thead>
                        <tbody id="segments-list-body">
                            <tr id="no-segments-row">
                                <td colspan="5" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox fs-4 d-block mb-2"></i>
                                    {{ app()->getLocale() == 'ku' ? 'هیچ سێگمێنتێک زیاد نەکراوە. یاسایەک هەڵبژێرە، دەقەکە دیاری بکە و کلیک لە "زیادکردن بۆ لیستی سێگمێنتەکان" بکە.' : (app()->getLocale() == 'ar' ? 'لم يتم إضافة أي مقاطع بعد. اختر قاعدة، حدد النص، وانقر على "إضافة إلى قائمة المقاطع".' : 'No segments added yet. Choose a rule, highlight the text, and click "Add Segment to List".') }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div id="hidden-segments-container"></div>
            </div>
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ayahSelect = document.getElementById('ayah_id');
    const ruleSelect = document.getElementById('tajweed_rule_id');
    const startIndexInput = document.getElementById('start_index');
    const endIndexInput = document.getElementById('end_index');
    const matchedTextInput = document.getElementById('matched_text');
    const previewContainer = document.getElementById('selected-ayah-container');
    const previewText = document.getElementById('ayah-preview-text');
    const addSegmentBtn = document.getElementById('add-segment-btn');
    const multiModeCheckbox = document.getElementById('multi_mode');

    let originalText = previewText ? previewText.getAttribute('data-original') || '' : '';
    let addedSegments = [];

    function updatePreview() {
        if (!originalText) return;

        const start = parseInt(startIndexInput.value);
        const end = parseInt(endIndexInput.value);
        const selectedRuleOption = ruleSelect.options[ruleSelect.selectedIndex];
        const ruleColor = selectedRuleOption ? selectedRuleOption.getAttribute('data-color') || '#2ca58d' : '#2ca58d';

        // Prepare list of segments to draw (both added and current draft)
        let displaySegments = [...addedSegments];

        if (!isNaN(start) && !isNaN(end) && start >= 0 && end > start && end <= originalText.length) {
            displaySegments.push({
                start_index: start,
                end_index: end,
                rule_color: ruleColor,
                is_draft: true
            });

            const match = originalText.substring(start, end);
            if (matchedTextInput && matchedTextInput.value !== match) {
                matchedTextInput.value = match;
            }
        }

        // Sort to render in order without overlapping issues
        displaySegments.sort((a, b) => a.start_index - b.start_index);

        let html = '';
        let lastIndex = 0;

        for (const seg of displaySegments) {
            const segStart = parseInt(seg.start_index);
            const segEnd = parseInt(seg.end_index);

            if (segStart < lastIndex) {
                // Skip overlapping segments for display sanity
                continue;
            }

            html += originalText.substring(lastIndex, segStart);

            const segmentText = originalText.substring(segStart, segEnd);
            const color = seg.rule_color || '#2ca58d';
            const opacity = seg.is_draft ? '40' : '20'; // Draft is slightly darker/more visible
            const borderStyle = seg.is_draft ? 'dashed' : 'solid';

            html += `<span class="tajweed-highlight" style="background-color: ${color}${opacity}; border-bottom: 2px ${borderStyle} ${color}; padding: 2px 4px; border-radius: 4px; font-weight: bold; transition: all 0.2s ease;">${segmentText}</span>`;
            lastIndex = segEnd;
        }

        html += originalText.substring(lastIndex);
        previewText.innerHTML = html;
    }

    function handleTextSelection() {
        const selection = window.getSelection();
        if (selection.rangeCount === 0 || selection.isCollapsed) return;

        const range = selection.getRangeAt(0);
        if (!previewText.contains(range.startContainer) || !previewText.contains(range.endContainer)) return;

        const offsets = getSelectionCharacterOffsetsWithin(previewText);
        
        if (offsets.start !== offsets.end) {
            startIndexInput.value = offsets.start;
            endIndexInput.value = offsets.end;
            updatePreview();
        }
    }

    function getSelectionCharacterOffsetsWithin(element) {
        let start = 0, end = 0;
        const sel = window.getSelection();
        if (sel.rangeCount > 0) {
            const range = sel.getRangeAt(0);
            const preCaretRange = range.cloneRange();
            preCaretRange.selectNodeContents(element);
            preCaretRange.setEnd(range.startContainer, range.startOffset);
            start = preCaretRange.toString().length;
            end = start + sel.toString().length;
        }
        return { start, end };
    }

    function renderTable() {
        const tbody = document.getElementById('segments-list-body');
        if (!tbody) return;

        tbody.innerHTML = '';

        if (addedSegments.length === 0) {
            tbody.innerHTML = `
                <tr id="no-segments-row">
                    <td colspan="5" class="text-center text-muted py-4">
                        <i class="bi bi-inbox fs-4 d-block mb-2"></i>
                        ${window.quranConfig.locale === 'ku' ? 'هیچ سێگمێنتێک زیاد نەکراوە. یاسایەک هەڵبژێرە، دەقەکە دیاری بکە و کلیک لە "زیادکردن بۆ لیستی سێگمێنتەکان" بکە.' : (window.quranConfig.locale === 'ar' ? 'لم يتم إضافة أي مقاطع بعد. اختر قاعدة، حدد النص، وانقر على "إضافة إلى قائمة المقاطع".' : 'No segments added yet. Choose a rule, highlight the text, and click "Add Segment to List".')}
                    </td>
                </tr>
            `;
            return;
        }

        addedSegments.forEach((seg, index) => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>
                    <span class="badge" style="background-color: ${seg.rule_color}22; color: ${seg.rule_color}; border: 1px solid ${seg.rule_color}55;">
                        ${seg.rule_name}
                    </span>
                </td>
                <td class="arabic-text" style="font-size: 18px; text-align: right;" dir="rtl">${seg.matched_text}</td>
                <td><code class="text-secondary">${seg.start_index} - ${seg.end_index}</code></td>
                <td class="text-truncate" style="max-width: 150px;">${seg.note || '-'}</td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-link text-danger p-0" onclick="removeSegment(${index})">
                        <i class="bi bi-trash fs-5"></i>
                    </button>
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    window.removeSegment = function(index) {
        addedSegments.splice(index, 1);
        renderTable();
        updateHiddenFields();
        updatePreview();
        
        if (window.showToast) {
            const deleteMsg = window.quranConfig.locale === 'ku' ? 'سێگمێنتەکە سڕایەوە' : (window.quranConfig.locale === 'ar' ? 'تم حذف المقطع' : 'Segment removed');
            window.showToast(deleteMsg, 'info');
        }
    };

    function updateHiddenFields() {
        const container = document.getElementById('hidden-segments-container');
        if (!container) return;

        container.innerHTML = '';
        addedSegments.forEach((seg, index) => {
            const fields = [
                { name: `segments[${index}][tajweed_rule_id]`, value: seg.tajweed_rule_id },
                { name: `segments[${index}][start_index]`, value: seg.start_index },
                { name: `segments[${index}][end_index]`, value: seg.end_index },
                { name: `segments[${index}][matched_text]`, value: seg.matched_text },
                { name: `segments[${index}][metadata]`, value: seg.metadata },
                { name: `segments[${index}][note]`, value: seg.note }
            ];

            fields.forEach(f => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = f.name;
                input.value = f.value;
                container.appendChild(input);
            });
        });
    }

    function toggleMultiMode(isActive) {
        const multiElements = document.querySelectorAll('.multi-mode-only');
        multiElements.forEach(el => el.style.display = isActive ? 'block' : 'none');

        const submitBtn = document.querySelector('button[type="submit"]');

        if (isActive) {
            if (ruleSelect) ruleSelect.removeAttribute('required');
            if (matchedTextInput) matchedTextInput.removeAttribute('required');
            if (submitBtn) {
                submitBtn.innerHTML = '<i class="bi bi-save me-1"></i> ' + 
                    (window.quranConfig.locale === 'ku' ? 'پاشەکەوتکردنی هەموو سێگمێنتەکان' : (window.quranConfig.locale === 'ar' ? 'حفظ جميع المقاطع' : 'Save All Segments'));
            }
        } else {
            if (ruleSelect) ruleSelect.setAttribute('required', 'required');
            if (matchedTextInput) matchedTextInput.setAttribute('required', 'required');
            if (submitBtn) {
                submitBtn.innerHTML = '<i class="bi bi-save me-1"></i> ' + 
                    (window.quranConfig.locale === 'ku' ? 'پاشەکەوتکردن' : (window.quranConfig.locale === 'ar' ? 'حفظ' : 'Save'));
            }
        }
        updatePreview();
    }

    function showValidationError(msg) {
        if (window.showToast) {
            window.showToast(msg, 'error');
        } else {
            alert(msg);
        }
    }

    // Set up listeners
    if (startIndexInput) startIndexInput.addEventListener('input', updatePreview);
    if (endIndexInput) endIndexInput.addEventListener('input', updatePreview);
    if (ruleSelect) ruleSelect.addEventListener('change', updatePreview);
    
    if (previewText) {
        previewText.addEventListener('mouseup', handleTextSelection);
        previewText.addEventListener('touchend', handleTextSelection);
    }

    if (multiModeCheckbox) {
        multiModeCheckbox.addEventListener('change', function() {
            toggleMultiMode(this.checked);
        });
    }

    if (addSegmentBtn) {
        addSegmentBtn.addEventListener('click', function() {
            if (!ayahSelect.value) {
                showValidationError(window.quranConfig.locale === 'ku' ? 'تکایە ئایەتێک هەڵبژێرە!' : 'Please select an Ayah!');
                return;
            }
            if (!ruleSelect.value) {
                showValidationError(window.quranConfig.locale === 'ku' ? 'تکایە یاسایەکی تەجوید هەڵبژێرە!' : 'Please select a Tajweed rule!');
                return;
            }
            
            const start = parseInt(startIndexInput.value);
            const end = parseInt(endIndexInput.value);
            
            if (isNaN(start) || isNaN(end) || start < 0 || end <= start || end > originalText.length) {
                showValidationError(window.quranConfig.locale === 'ku' ? 'ئیندێکسەکان نادروستن!' : 'Indices are invalid!');
                return;
            }

            const selectedRuleOption = ruleSelect.options[ruleSelect.selectedIndex];
            const ruleName = selectedRuleOption.text;
            const ruleColor = selectedRuleOption.getAttribute('data-color') || '#2ca58d';
            
            const segment = {
                tajweed_rule_id: ruleSelect.value,
                rule_name: ruleName,
                rule_color: ruleColor,
                matched_text: matchedTextInput.value,
                start_index: start,
                end_index: end,
                metadata: document.getElementById('metadata').value,
                note: document.getElementById('note').value
            };

            addedSegments.push(segment);
            renderTable();
            updateHiddenFields();
            
            // Clear inputs for the next entry (but keep ayah and rule selected)
            startIndexInput.value = '';
            endIndexInput.value = '';
            matchedTextInput.value = '';
            document.getElementById('note').value = '';
            
            updatePreview();

            if (window.showToast) {
                const addMsg = window.quranConfig.locale === 'ku' ? 'سێگمێنتەکە زیادکرا بۆ لیستەکە' : (window.quranConfig.locale === 'ar' ? 'تم إضافة المقطع للقائمة' : 'Segment added to list');
                window.showToast(addMsg, 'success');
            }
        });
    }

    if (ayahSelect) {
        ayahSelect.addEventListener('change', function() {
            const ayahId = this.value;
            if (!ayahId) {
                if (previewContainer) previewContainer.style.display = 'none';
                if (previewText) {
                    previewText.textContent = '';
                    previewText.setAttribute('data-original', '');
                }
                originalText = '';
                addedSegments = [];
                renderTable();
                updateHiddenFields();
                return;
            }

            const url = `${window.quranConfig.apiUrl}/v1/ayahs/${ayahId}`;
            axios.get(url)
                .then(response => {
                    if (response.data && response.data.status === 'success') {
                        const ayah = response.data.data;
                        originalText = ayah.text_uthmani;
                        if (previewText) {
                            previewText.textContent = originalText;
                            previewText.setAttribute('data-original', originalText);
                        }
                        if (previewContainer) previewContainer.style.display = 'block';

                        // Reset lists and fields for new ayah selection
                        addedSegments = [];
                        renderTable();
                        updateHiddenFields();

                        if (startIndexInput) startIndexInput.value = 0;
                        if (endIndexInput) endIndexInput.value = originalText.length;
                        updatePreview();
                    }
                })
                .catch(error => {
                    console.error('Error fetching ayah text:', error);
                    if (window.showToast) {
                        window.showToast('Failed to fetch Ayah details', 'error');
                    }
                });
        });
    }

    // Intercept form submit to prevent sending empty list in multi mode
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            if (multiModeCheckbox && multiModeCheckbox.checked) {
                if (addedSegments.length === 0) {
                    e.preventDefault();
                    showValidationError(window.quranConfig.locale === 'ku' ? 'تکایە لانی کەم یەک سێگمێنت زیاد بکە پێش پاشەکەوتکردن!' : 'Please add at least one segment before saving!');
                }
            }
        });
    }

    // Initial update if edit mode
    updatePreview();
});
</script>
@endpush
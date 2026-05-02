{{-- resources/views/tajweed-rules/_form.blade.php --}}
@php
    /** @var \App\Models\TajweedRule $tajweedRule */
@endphp

<div class="quran-form">
    <div class="row g-4">
        <!-- Basic Information -->
        <div class="col-12">
            <div class="quran-form-section">
                <h6 class="quran-form-section-title">
                    <i class="bi bi-info-circle me-2"></i>
                    {{ __('tajweed_rules.sections.basic_info') }}
                </h6>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="quran-form-label" for="name">
                            {{ __('tajweed_rules.fields.name') }}
                            <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="name" id="name" 
                               class="quran-form-control @error('name') is-invalid @enderror"
                               value="{{ old('name', $tajweedRule->name) }}" required>
                        @error('name')
                        <div class="quran-invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="quran-form-label" for="category">
                            {{ __('tajweed_rules.fields.category') }}
                        </label>
                        <select name="category" id="category" 
                                class="quran-form-select @error('category') is-invalid @enderror">
                            <option value="">{{ __('tajweed_rules.select_category') }}</option>
                            @foreach($categories as $key => $name)
                            <option value="{{ $key }}" 
                                {{ old('category', $tajweedRule->category) == $key ? 'selected' : '' }}>
                                {{ $name }}
                            </option>
                            @endforeach
                        </select>
                        @error('category')
                        <div class="quran-invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="quran-form-label" for="color_code">
                            {{ __('tajweed_rules.fields.color_code') }}
                        </label>
                        <div class="d-flex gap-2">
                            <input type="color" name="color_code" id="color_code" 
                                   class="form-control form-control-color @error('color_code') is-invalid @enderror"
                                   value="{{ old('color_code', $tajweedRule->color_code ?? '#1B7340') }}"
                                   style="width: 60px; height: 44px;">
                            <input type="text" id="color_code_text" 
                                   class="quran-form-control" 
                                   value="{{ old('color_code', $tajweedRule->color_code) }}"
                                   placeholder="#RRGGBB" readonly>
                        </div>
                        @error('color_code')
                        <div class="quran-invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="quran-form-label" for="priority">
                            {{ __('tajweed_rules.fields.priority') }}
                        </label>
                        <input type="number" name="priority" id="priority" 
                               class="quran-form-control @error('priority') is-invalid @enderror"
                               value="{{ old('priority', $tajweedRule->priority ?? 0) }}" min="0">
                        <small class="text-muted">{{ __('tajweed_rules.hints.priority') }}</small>
                        @error('priority')
                        <div class="quran-invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Color Palette -->
        <div class="col-12">
            <div class="quran-form-section">
                <h6 class="quran-form-section-title">
                    <i class="bi bi-palette me-2"></i>
                    {{ __('tajweed_rules.sections.color_palette') }}
                </h6>
                <div class="d-flex flex-wrap gap-2">
                    @foreach($colorPalette as $color => $name)
                    <button type="button" class="color-preset-btn" 
                            style="background-color: {{ $color }}; width: 40px; height: 40px; border-radius: 8px; border: 2px solid var(--quran-border-light); cursor: pointer;"
                            data-color="{{ $color }}"
                            title="{{ $name }}"></button>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Description -->
        <div class="col-12">
            <div class="quran-form-section">
                <h6 class="quran-form-section-title">
                    <i class="bi bi-card-text me-2"></i>
                    {{ __('tajweed_rules.fields.description') }}
                </h6>

                <div class="row g-3">
                    <div class="col-12">
                        <textarea name="description" id="description" rows="4"
                                  class="quran-form-control @error('description') is-invalid @enderror"
                                  placeholder="{{ __('tajweed_rules.placeholders.description') }}"
                                  required>{{ old('description', $tajweedRule->description) }}</textarea>
                        @error('description')
                        <div class="quran-invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label class="quran-form-label" for="example_text">
                            {{ __('tajweed_rules.fields.example_text') }}
                        </label>
                        <textarea name="example_text" id="example_text" rows="2"
                                  class="quran-form-control arabic-text @error('example_text') is-invalid @enderror"
                                  dir="rtl"
                                  placeholder="{{ __('tajweed_rules.placeholders.example') }}">{{ old('example_text', $tajweedRule->example_text) }}</textarea>
                        @error('example_text')
                        <div class="quran-invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Settings -->
        <div class="col-12">
            <div class="quran-form-section">
                <div class="quran-form-check">
                    <input type="checkbox" name="is_active" id="is_active" value="1"
                           class="quran-form-check-input" 
                           {{ old('is_active', $tajweedRule->is_active ?? true) ? 'checked' : '' }}>
                    <label class="quran-form-check-label" for="is_active">
                        <i class="bi bi-check-circle-fill text-success me-2"></i>
                        {{ __('tajweed_rules.fields.is_active') }}
                    </label>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const colorInput = document.getElementById('color_code');
    const colorText = document.getElementById('color_code_text');
    
    if (colorInput && colorText) {
        colorInput.addEventListener('input', function() {
            colorText.value = this.value;
        });
        
        colorText.addEventListener('input', function() {
            if (/^#[0-9A-F]{6}$/i.test(this.value)) {
                colorInput.value = this.value;
            }
        });
    }
    
    // Color preset buttons
    document.querySelectorAll('.color-preset-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const color = this.dataset.color;
            if (colorInput) {
                colorInput.value = color;
                colorText.value = color;
            }
        });
    });
});
</script>
@endpush
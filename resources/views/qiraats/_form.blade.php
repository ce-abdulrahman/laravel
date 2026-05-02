{{-- resources/views/qiraats/_form.blade.php --}}
@php
    /** @var \App\Models\Qiraat $qiraat */
@endphp

<div class="quran-form">
    <div class="row g-4">
        <!-- Basic Information -->
        <div class="col-12">
            <div class="quran-form-section">
                <h6 class="quran-form-section-title">
                    <i class="bi bi-info-circle me-2"></i>
                    {{ __('qiraats.sections.basic_info') }}
                </h6>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="quran-form-label" for="name">
                            {{ __('qiraats.fields.name') }}
                            <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="name" id="name" 
                               class="quran-form-control @error('name') is-invalid @enderror"
                               value="{{ old('name', $qiraat->name) }}" required>
                        @error('name')
                        <div class="quran-invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="quran-form-label" for="riwayah">
                            {{ __('qiraats.fields.riwayah') }}
                        </label>
                        <select name="riwayah" id="riwayah" 
                                class="quran-form-select @error('riwayah') is-invalid @enderror">
                            <option value="">{{ __('qiraats.select_riwayah') }}</option>
                            @foreach($riwayahs as $key => $name)
                            <option value="{{ $key }}" 
                                {{ old('riwayah', $qiraat->riwayah) == $key ? 'selected' : '' }}>
                                {{ $name }}
                            </option>
                            @endforeach
                        </select>
                        @error('riwayah')
                        <div class="quran-invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label class="quran-form-label" for="description">
                            {{ __('qiraats.fields.description') }}
                        </label>
                        <textarea name="description" id="description" rows="4"
                                  class="quran-form-control @error('description') is-invalid @enderror"
                                  placeholder="{{ __('qiraats.placeholders.description') }}">{{ old('description', $qiraat->description) }}</textarea>
                        @error('description')
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
                           {{ old('is_active', $qiraat->is_active ?? true) ? 'checked' : '' }}>
                    <label class="quran-form-check-label" for="is_active">
                        <i class="bi bi-check-circle-fill text-success me-2"></i>
                        {{ __('qiraats.fields.is_active') }}
                    </label>
                </div>
            </div>
        </div>
    </div>
</div>
{{-- resources/views/reciters/_form.blade.php --}}
@php
    /** @var \App\Models\Reciter $reciter */
@endphp

<div class="quran-form">
    <div class="row g-4">
        <!-- Basic Information -->
        <div class="col-12">
            <div class="quran-form-section">
                <h6 class="quran-form-section-title">
                    <i class="bi bi-person me-2"></i>
                    {{ __('reciters.sections.basic_info') }}
                </h6>

                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="quran-form-label" for="name">
                            {{ __('reciters.fields.name') }}
                            <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="name" id="name" 
                               class="quran-form-control @error('name') is-invalid @enderror"
                               value="{{ old('name', $reciter->name) }}" required>
                        @error('name')
                        <div class="quran-invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="quran-form-label" for="language">
                            {{ __('reciters.fields.language') }}
                        </label>
                        <select name="language" id="language" 
                                class="quran-form-select @error('language') is-invalid @enderror">
                            <option value="">{{ __('reciters.select_language') }}</option>
                            @foreach($languages as $code => $name)
                            <option value="{{ $code }}" 
                                {{ old('language', $reciter->language) == $code ? 'selected' : '' }}>
                                {{ $name }}
                            </option>
                            @endforeach
                        </select>
                        @error('language')
                        <div class="quran-invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="quran-form-label" for="riwayah">
                            {{ __('reciters.fields.riwayah') }}
                        </label>
                        <select name="riwayah" id="riwayah" 
                                class="quran-form-select @error('riwayah') is-invalid @enderror">
                            <option value="">{{ __('reciters.select_riwayah') }}</option>
                            @foreach($riwayahs as $key => $name)
                            <option value="{{ $key }}" 
                                {{ old('riwayah', $reciter->riwayah) == $key ? 'selected' : '' }}>
                                {{ $name }}
                            </option>
                            @endforeach
                        </select>
                        @error('riwayah')
                        <div class="quran-invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Image -->
        <div class="col-12">
            <div class="quran-form-section">
                <h6 class="quran-form-section-title">
                    <i class="bi bi-image me-2"></i>
                    {{ __('reciters.fields.image') }}
                </h6>

                <div class="row g-3 align-items-center">
                    <div class="col-md-3">
                        <div class="quran-avatar-preview">
                            @if($reciter->image)
                            <img src="{{ Storage::url($reciter->image) }}" alt="Preview" 
                                 class="img-thumbnail" style="width: 120px; height: 120px; object-fit: cover;">
                            @else
                            <div class="bg-light d-flex align-items-center justify-content-center" 
                                 style="width: 120px; height: 120px; border-radius: 12px;">
                                <i class="bi bi-person-circle" style="font-size: 48px; color: var(--quran-text-muted);"></i>
                            </div>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-9">
                        <input type="file" name="image" id="image" 
                               class="quran-form-control @error('image') is-invalid @enderror"
                               accept="image/*" onchange="previewImage(this)">
                        <small class="text-muted">{{ __('reciters.hints.image_help') }}</small>
                        @error('image')
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
                           {{ old('is_active', $reciter->is_active ?? true) ? 'checked' : '' }}>
                    <label class="quran-form-check-label" for="is_active">
                        <i class="bi bi-check-circle-fill text-success me-2"></i>
                        {{ __('reciters.fields.is_active') }}
                    </label>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function previewImage(input) {
    const preview = document.querySelector('.quran-avatar-preview');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.innerHTML = `<img src="${e.target.result}" alt="Preview" 
                                       class="img-thumbnail" style="width: 120px; height: 120px; object-fit: cover;">`;
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endpush
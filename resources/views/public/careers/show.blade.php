@php
    $title    = $career->getTranslation('title', app()->getLocale(), false) ?: $career->getTranslation('title', 'en', false);
    $desc     = $career->getTranslation('description', app()->getLocale(), false) ?: $career->getTranslation('description', 'en', false);
    $metaDesc = Str::limit(strip_tags($desc), 155);
    $siteName = \App\Models\Setting::get('site_name', config('app.name'));
    $siteUrl  = config('app.url');
    $careerUrl = route('careers.show', $career->getSlug());
@endphp

@push('structured-data')
@php
    $jobSchema = [
        '@context'          => 'https://schema.org',
        '@type'             => 'JobPosting',
        'title'             => $title,
        'description'       => $desc,
        'url'               => $careerUrl,
        'datePosted'        => $career->created_at->toIso8601String(),
        'hiringOrganization'=> [
            '@type' => 'Organization',
            'name'  => $siteName,
            'url'   => $siteUrl,
        ],
        'jobLocation' => [
            '@type'   => 'Place',
            'address' => [
                '@type'           => 'PostalAddress',
                'addressCountry'  => 'ID',
            ],
        ],
        'employmentType' => 'FULL_TIME',
        'directApply'    => true,
    ];
    if ($career->expires_at) {
        $jobSchema['validThrough'] = $career->expires_at->toIso8601String();
    }
    $breadcrumbSchema = [
        '@context'        => 'https://schema.org',
        '@type'           => 'BreadcrumbList',
        'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home',             'item' => route('home')],
            ['@type' => 'ListItem', 'position' => 2, 'name' => __('Careers'),      'item' => route('careers.index')],
            ['@type' => 'ListItem', 'position' => 3, 'name' => $title,             'item' => $careerUrl],
        ],
    ];
@endphp
<script type="application/ld+json">
{!! json_encode([$jobSchema, $breadcrumbSchema], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_TAG) !!}
</script>
@endpush

<x-layouts.public
    :title="$title . ' — ' . __('Careers')"
    :metaDescription="$metaDesc"
    :canonicalUrl="route('careers.show', $career->getSlug())"
>
    <div class="home-header-background" aria-hidden="true"></div>

    {{-- Hero / Page Header --}}
    <section class="careers-hero-section" aria-labelledby="career-detail-heading">
        <div class="container container-2">
            <nav class="news-breadcrumb" aria-label="{{ __('Breadcrumb') }}">
                <a href="{{ route('home') }}">{{ __('Home') }}</a>
                <span aria-hidden="true">/</span>
                <a href="{{ route('careers.index') }}">{{ __('Careers') }}</a>
                <span aria-hidden="true">/</span>
                <span aria-current="page" style="color:#164e39; font-weight:600;">{{ $title }}</span>
            </nav>

            <div class="careers-hero-badge">
                <span class="badge-dot"></span>
                <span>{{ $career->type->label() }}</span>
            </div>

            <h1 id="career-detail-heading">{{ $title }}</h1>

            <p class="careers-hero-lead" style="font-size:15px; color:#5a7060;">
                {{ __('Posted') }} {{ $career->created_at->format('d M Y') }}
            </p>
        </div>
    </section>

    {{-- Main Content --}}
    <section class="careers-list-section" style="padding-top:56px;">
        <div class="container container-2">
            <div class="career-detail-layout">

                {{-- Left: Job Description --}}
                <div class="career-detail-main">
                    <div class="career-detail-panel">

                        {{-- Featured Image --}}
                        @if($career->image)
                            <div class="career-detail-img-wrap">
                                <img src="{{ $career->imageUrl() }}" alt="{{ $title }}" loading="lazy">
                            </div>
                        @endif

                        {{-- Description --}}
                        <div class="career-detail-body">
                            <div class="career-detail-section-label">
                                <i class="fa-solid fa-file-lines" aria-hidden="true"></i>
                                <span>{{ __('Job Description & Requirements') }}</span>
                            </div>
                            <div class="news-article-content">
                                {!! $desc !!}
                            </div>
                        </div>
                    </div>

                    {{-- Back Link --}}
                    <a href="{{ route('careers.index') }}" class="career-back-link">
                        <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
                        {{ __('Back to All Positions') }}
                    </a>
                </div>

                {{-- Right: Application Form --}}
                <div class="career-detail-aside">
                    <div class="career-apply-panel">
                        <div class="career-apply-panel-header">
                            <div class="career-apply-icon">
                                <i class="fa-solid fa-paper-plane" aria-hidden="true"></i>
                            </div>
                            <div>
                                <h3>{{ __('Apply for this Position') }}</h3>
                                <p>{{ __('Complete the form below to submit your application.') }}</p>
                            </div>
                        </div>

                        {{-- Success Alert --}}
                        @if(session('success'))
                            <div class="career-alert career-alert-success">
                                <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
                                <div>
                                    <strong>{{ __('Application Submitted!') }}</strong>
                                    <p>{{ session('success') }}</p>
                                </div>
                            </div>
                        @endif

                        {{-- Validation Errors --}}
                        @if($errors->any())
                            <div class="career-alert career-alert-error">
                                <i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>
                                <div>
                                    <strong>{{ __('Please check the form for errors:') }}</strong>
                                    <ul>
                                        @foreach($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        @endif

                        {{-- Form --}}
                        <form action="{{ route('careers.apply', $career->getSlug()) }}"
                              method="POST"
                              enctype="multipart/form-data"
                              class="career-form"
                              id="career-apply-form"
                              x-data="{
                                submitting: false,
                                fileName: '',
                                fileSize: '',
                                fileError: '',
                                handleFileChange(event) {
                                    const file = event.target.files[0];
                                    if (!file) { this.fileName = ''; this.fileSize = ''; this.fileError = ''; return; }
                                    const allowedExtensions = ['pdf','doc','docx'];
                                    const ext = file.name.split('.').pop().toLowerCase();
                                    if (!allowedExtensions.includes(ext)) {
                                        this.fileError = '{{ __('Only PDF, DOC, or DOCX files are allowed.') }}';
                                        this.fileName = ''; event.target.value = ''; return;
                                    }
                                    if (file.size > 5 * 1024 * 1024) {
                                        this.fileError = '{{ __('File size exceeds 5MB limit.') }}';
                                        this.fileName = ''; event.target.value = ''; return;
                                    }
                                    this.fileError = '';
                                    this.fileName = file.name;
                                    this.fileSize = (file.size / (1024 * 1024)).toFixed(2) + ' MB';
                                }
                              }"
                              @submit="submitting = true"
                              @career-recaptcha-error.window="submitting = false">
                            @csrf
                            @if(config('recaptcha.site_key'))
                                <input type="hidden" name="recaptcha_token" id="recaptcha_token">
                            @endif

                            {{-- Full Name --}}
                            <div class="career-field">
                                <label for="name" class="career-label">
                                    {{ __('Full Name') }} <span class="career-required">*</span>
                                </label>
                                <input type="text"
                                       id="name"
                                       name="name"
                                       value="{{ old('name') }}"
                                       required
                                       placeholder="{{ __('e.g. John Doe') }}"
                                       class="career-input @error('name') career-input-error @enderror">
                                @error('name')
                                    <p class="career-field-error">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Email --}}
                            <div class="career-field">
                                <label for="email" class="career-label">
                                    {{ __('Email Address') }} <span class="career-required">*</span>
                                </label>
                                <input type="email"
                                       id="email"
                                       name="email"
                                       value="{{ old('email') }}"
                                       required
                                       placeholder="{{ __('e.g. name@example.com') }}"
                                       class="career-input @error('email') career-input-error @enderror">
                                @error('email')
                                    <p class="career-field-error">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Phone --}}
                            <div class="career-field">
                                <label for="phone" class="career-label">
                                    {{ __('Phone Number') }} <span class="career-required">*</span>
                                </label>
                                <input type="tel"
                                       id="phone"
                                       name="phone"
                                       value="{{ old('phone') }}"
                                       required
                                       placeholder="{{ __('e.g. +62 812 3456 7890') }}"
                                       class="career-input @error('phone') career-input-error @enderror">
                                @error('phone')
                                    <p class="career-field-error">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- LinkedIn --}}
                            <div class="career-field">
                                <label for="linkedin" class="career-label">
                                    {{ __('LinkedIn Profile') }}
                                    <span class="career-optional">({{ __('Optional') }})</span>
                                </label>
                                <input type="url"
                                       id="linkedin"
                                       name="linkedin"
                                       value="{{ old('linkedin') }}"
                                       placeholder="https://linkedin.com/in/username"
                                       class="career-input @error('linkedin') career-input-error @enderror">
                                @error('linkedin')
                                    <p class="career-field-error">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- CV Upload --}}
                            <div class="career-field">
                                <label class="career-label">
                                    {{ __('CV / Resume') }} <span class="career-required">*</span>
                                </label>
                                <div class="career-upload-zone" :class="fileError ? 'career-upload-zone--error' : ''">
                                    <div class="career-upload-inner">
                                        <i class="fa-solid fa-cloud-arrow-up career-upload-icon" aria-hidden="true"></i>
                                        <label for="cv" class="career-upload-label">
                                            {{ __('Upload CV / Resume') }}
                                            <input id="cv"
                                                   name="cv"
                                                   type="file"
                                                   required
                                                   accept=".pdf,.doc,.docx"
                                                   @change="handleFileChange($event)"
                                                   class="career-upload-input">
                                        </label>
                                        <p class="career-upload-hint">{{ __('PDF, DOC, DOCX — max 5 MB') }}</p>
                                    </div>
                                </div>

                                <template x-if="fileName">
                                    <div class="career-file-selected">
                                        <div class="career-file-name">
                                            <i class="fa-solid fa-file-check" aria-hidden="true"></i>
                                            <span x-text="fileName"></span>
                                        </div>
                                        <span class="career-file-size" x-text="fileSize"></span>
                                    </div>
                                </template>

                                <template x-if="fileError">
                                    <p class="career-field-error" x-text="fileError"></p>
                                </template>

                                @error('cv')
                                    <p class="career-field-error">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Submit --}}
                            <button type="submit"
                                    class="career-submit-btn"
                                    :disabled="submitting"
                                    :class="{ 'career-submit-btn--loading': submitting }">
                                <span x-text="submitting ? '{{ __('Sending...') }}' : '{{ __('Submit Application') }}'"></span>
                                <span class="career-submit-icon" aria-hidden="true">
                                    {{-- Spinner saat loading --}}
                                    <svg x-show="submitting"
                                         class="career-submit-spinner"
                                         viewBox="0 0 24 24" fill="none"
                                         xmlns="http://www.w3.org/2000/svg">
                                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" stroke-dasharray="56" stroke-dashoffset="14" stroke-linecap="round"/>
                                    </svg>
                                    {{-- Icon panah saat normal --}}
                                    <i class="fa-solid fa-arrow-right" x-show="!submitting"></i>
                                </span>
                            </button>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </section>

    @push('scripts')
        @if(config('recaptcha.site_key'))
            <script src="https://www.google.com/recaptcha/api.js?render={{ config('recaptcha.site_key') }}" defer></script>
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const form = document.getElementById('career-apply-form');
                    const siteKey = '{{ config('recaptcha.site_key') }}';

                    if (! siteKey || ! form) {
                        return;
                    }

                    // Override the Alpine submit handler to inject reCAPTCHA token first.
                    form.addEventListener('submit', function (e) {
                        // If token already filled (retry), let it through.
                        const tokenInput = document.getElementById('recaptcha_token');
                        if (tokenInput && tokenInput.value) {
                            return;
                        }

                        e.preventDefault();

                        grecaptcha.ready(function () {
                            grecaptcha.execute(siteKey, { action: 'career_apply' }).then(function (token) {
                                tokenInput.value = token;
                                form.submit();
                            }).catch(function () {
                                // Reset loading state on reCAPTCHA error.
                                window.dispatchEvent(new CustomEvent('career-recaptcha-error'));
                            });
                        });
                    });
                });
            </script>
        @endif
    @endpush
</x-layouts.public>

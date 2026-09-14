@push('structured-data')
@php
    $contactSchema = [
        '@context'    => 'https://schema.org',
        '@type'       => 'ContactPage',
        'name'        => __('Contact Us'),
        'url'         => route('contact'),
        'description' => __('Have a question or feedback? Contact us through our form. We will get back to you as soon as possible.'),
    ];
    $sitePhone   = \App\Models\Setting::get('contact_phone', '');
    $siteEmail   = \App\Models\Setting::get('contact_email', '');
    $siteName    = \App\Models\Setting::get('site_name', config('app.name'));
    if ($sitePhone || $siteEmail) {
        $contactSchema['mainEntity'] = [
            '@type'       => 'Organization',
            'name'        => $siteName,
            'url'         => config('app.url'),
        ];
        if ($sitePhone) {
            $contactSchema['mainEntity']['telephone'] = $sitePhone;
        }
        if ($siteEmail) {
            $contactSchema['mainEntity']['email'] = $siteEmail;
        }
    }
@endphp
<script type="application/ld+json">
{!! json_encode($contactSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_TAG) !!}
</script>
@endpush

<x-layouts.public
    :title="__('Contact Us')"
    :metaDescription="__('Have a question or feedback? Contact us through our form. We will get back to you as soon as possible.')"
    :canonicalUrl="route('contact')"
>
    <div class="home-header-background" aria-hidden="true"></div>

    {{-- Hero Section --}}
    <section class="careers-hero-section" aria-labelledby="contact-heading">
        <div class="container container-2">
            <nav class="news-breadcrumb" aria-label="{{ __('Breadcrumb') }}">
                <a href="{{ route('home') }}">{{ __('Home') }}</a>
                <span aria-hidden="true">/</span>
                <span aria-current="page">{{ __('Contact Us') }}</span>
            </nav>

            <div class="careers-hero-badge">
                <span class="badge-dot"></span>
                <span>{{ __('Get In Touch') }}</span>
            </div>

            {{-- Title: gunakan seluruh phrase agar bisa diterjemahkan dengan benar --}}
            <h1 id="contact-heading">
                {{ __('Contact') }} <span>{{ __('Us') }}</span>
            </h1>

            <p class="careers-hero-lead">
                {{ __('Have a question, feedback, or just want to say hello? Fill out the form below and we will get back to you as soon as possible.') }}
            </p>
        </div>
    </section>

    {{-- Main Section --}}
    <section class="careers-list-section" style="padding-top: 56px;">
        <div class="container container-2">
            <div class="contact-form-layout">

                {{-- Kiri: Info Kontak --}}
                <div class="contact-info-col">
                    @php
                        $siteName = \App\Models\Setting::get('site_name', config('app.name'));
                        $address  = \App\Models\Setting::get('contact_address', '');
                        $email    = \App\Models\Setting::get('contact_email', '');
                        $phone    = \App\Models\Setting::get('contact_phone', '');
                        $maps     = \App\Models\Setting::get('contact_maps_url', '');
                    @endphp

                    <div class="career-apply-panel">
                        <div class="career-apply-panel-header">
                            <div class="career-apply-icon">
                                <i class="fa-solid fa-location-dot" aria-hidden="true"></i>
                            </div>
                            <div>
                                <h3>{{ __('Our Office') }}</h3>
                                <p>{{ $siteName }}</p>
                            </div>
                        </div>

                        <div style="padding: 24px 28px; display: flex; flex-direction: column; gap: 20px;">

                            @if($address)
                                <div class="contact-info-item">
                                    <span class="contact-info-icon">
                                        <i class="fa-solid fa-map-pin" aria-hidden="true"></i>
                                    </span>
                                    <div>
                                        <span class="contact-info-label">{{ __('Address') }}</span>
                                        {{-- Tampilkan alamat persis seperti footer: pre-line tanpa nl2br --}}
                                        <span class="contact-info-value" style="white-space: pre-line;">{{ $address }}</span>
                                    </div>
                                </div>
                            @endif

                            @if($email)
                                <div class="contact-info-item">
                                    <span class="contact-info-icon">
                                        <i class="fa-solid fa-envelope" aria-hidden="true"></i>
                                    </span>
                                    <div>
                                        <span class="contact-info-label">{{ __('Email') }}</span>
                                        <a href="mailto:{{ $email }}" class="contact-info-value contact-info-link">{{ $email }}</a>
                                    </div>
                                </div>
                            @endif

                            @if($phone)
                                <div class="contact-info-item">
                                    <span class="contact-info-icon">
                                        <i class="fa-solid fa-phone" aria-hidden="true"></i>
                                    </span>
                                    <div>
                                        <span class="contact-info-label">{{ __('Phone') }}</span>
                                        <a href="tel:{{ preg_replace('/\s+/', '', $phone) }}" class="contact-info-value contact-info-link">{{ $phone }}</a>
                                    </div>
                                </div>
                            @endif

                            @if($maps)
                                <a href="{{ $maps }}" target="_blank" rel="noopener noreferrer" class="tl-primary-btn" style="margin-top: 4px;">
                                    <span>{{ __('Open in Maps') }}</span>
                                    <span class="icon">
                                        <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                                    </span>
                                </a>
                            @endif

                        </div>
                    </div>
                </div>

                {{-- Kanan: Form --}}
                <div class="contact-form-col">
                    <div class="career-apply-panel">

                        <div class="career-apply-panel-header">
                            <div class="career-apply-icon">
                                <i class="fa-solid fa-paper-plane" aria-hidden="true"></i>
                            </div>
                            <div>
                                <h3>{{ __('Send Us a Message') }}</h3>
                                <p>{{ __('We will reply within 1–2 business days.') }}</p>
                            </div>
                        </div>

                        {{-- Success --}}
                        @if(session('success'))
                            <div class="career-alert career-alert-success">
                                <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
                                <div>
                                    <strong>{{ __('Message Sent!') }}</strong>
                                    <p>{{ session('success') }}</p>
                                </div>
                            </div>
                        @endif

                        {{-- Validation Errors --}}
                        @if($errors->any())
                            <div class="career-alert career-alert-error">
                                <i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>
                                <div>
                                    <strong>{{ __('Please correct the following errors:') }}</strong>
                                    <ul>
                                        @foreach($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        @endif

                        {{-- Form --}}
                        <form method="POST"
                              action="{{ route('contact.store') }}"
                              class="career-form"
                              id="contact-form"
                              x-data="{ submitting: false }"
                              @submit="submitting = true"
                              @contact-recaptcha-error.window="submitting = false">
                            @csrf
                            @if(config('recaptcha.site_key'))
                                <input type="hidden" name="recaptcha_token" id="recaptcha_token">
                            @endif

                            {{-- Nama --}}
                            <div class="career-field">
                                <label for="c_name" class="career-label">
                                    {{ __('Name') }} <span class="career-required">*</span>
                                </label>
                                <input type="text"
                                       id="c_name"
                                       name="name"
                                       value="{{ old('name') }}"
                                       required
                                       placeholder="{{ __('Your full name') }}"
                                       class="career-input @error('name') career-input-error @enderror">
                                @error('name')
                                    <p class="career-field-error">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Email --}}
                            <div class="career-field">
                                <label for="c_email" class="career-label">
                                    {{ __('Email') }} <span class="career-required">*</span>
                                </label>
                                <input type="email"
                                       id="c_email"
                                       name="email"
                                       value="{{ old('email') }}"
                                       required
                                       placeholder="{{ __('your@email.com') }}"
                                       class="career-input @error('email') career-input-error @enderror">
                                @error('email')
                                    <p class="career-field-error">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Subjek --}}
                            <div class="career-field">
                                <label for="c_subject" class="career-label">
                                    {{ __('Subject') }} <span class="career-required">*</span>
                                </label>
                                <input type="text"
                                       id="c_subject"
                                       name="subject"
                                       value="{{ old('subject') }}"
                                       required
                                       placeholder="{{ __('What is this about?') }}"
                                       class="career-input @error('subject') career-input-error @enderror">
                                @error('subject')
                                    <p class="career-field-error">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Pesan --}}
                            <div class="career-field">
                                <label for="c_message" class="career-label">
                                    {{ __('Message') }} <span class="career-required">*</span>
                                </label>
                                <textarea id="c_message"
                                          name="message"
                                          rows="5"
                                          required
                                          placeholder="{{ __('Write your message here...') }}"
                                          class="career-input @error('message') career-input-error @enderror"
                                          style="resize: vertical; min-height: 120px;">{{ old('message') }}</textarea>
                                @error('message')
                                    <p class="career-field-error">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Submit --}}
                            <button type="submit"
                                    class="career-submit-btn"
                                    :disabled="submitting"
                                    :class="{ 'career-submit-btn--loading': submitting }">
                                <span x-text="submitting ? '{{ __('Sending...') }}' : '{{ __('Send Message') }}'"></span>
                                <span class="career-submit-icon" aria-hidden="true">
                                    <svg x-show="submitting"
                                         class="career-submit-spinner"
                                         viewBox="0 0 24 24" fill="none">
                                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" stroke-dasharray="56" stroke-dashoffset="14" stroke-linecap="round"/>
                                    </svg>
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
                    const form    = document.getElementById('contact-form');
                    const siteKey = '{{ config('recaptcha.site_key') }}';

                    if (! siteKey || ! form) return;

                    form.addEventListener('submit', function (e) {
                        const tokenInput = document.getElementById('recaptcha_token');
                        if (tokenInput && tokenInput.value) return;

                        e.preventDefault();

                        grecaptcha.ready(function () {
                            grecaptcha.execute(siteKey, { action: 'contact_submit' }).then(function (token) {
                                tokenInput.value = token;
                                form.submit();
                            }).catch(function () {
                                window.dispatchEvent(new CustomEvent('contact-recaptcha-error'));
                            });
                        });
                    });
                });
            </script>
        @endif
    @endpush

</x-layouts.public>

@push('structured-data')
@php
    $contactSiteName = \App\Models\Setting::get('site_name', config('app.name'));
    $contactAddr     = \App\Models\Setting::get('contact_address', '');
    $contactCity     = \App\Models\Setting::get('contact_city', '');
    $contactEmail    = \App\Models\Setting::get('contact_email', '');
    $contactPhone    = \App\Models\Setting::get('contact_phone', '');

    $orgEntity = ['@type' => 'Organization', 'name' => $contactSiteName];
    if ($contactAddr || $contactCity) {
        $orgEntity['address'] = [
            '@type'           => 'PostalAddress',
            'streetAddress'   => $contactAddr,
            'addressLocality' => $contactCity,
        ];
    }
    if ($contactEmail) { $orgEntity['email']     = $contactEmail; }
    if ($contactPhone) { $orgEntity['telephone'] = $contactPhone; }

    $contactPageSchema = [
        '@context'    => 'https://schema.org',
        '@type'       => 'ContactPage',
        'name'        => __('Contact Us') . ' — ' . $contactSiteName,
        'url'         => route('contact'),
        'description' => __('Have a question or feedback? Contact us through our form. We will get back to you as soon as possible.'),
        'mainEntity'  => $orgEntity,
    ];
@endphp
<script type="application/ld+json">
{!! json_encode($contactPageSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
</script>
@endpush

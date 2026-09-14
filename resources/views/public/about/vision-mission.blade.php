<x-layouts.public
    :title="__('Vision & Mission')"
    :metaDescription="__('Discover our vision for the future and the mission that guides our operations. Our core values shape every decision we make in the energy industry.')"
    :canonicalUrl="route('about.vision-mission')"
>
    <div class="home-header-background" aria-hidden="true"></div>

    @include('partials.home-vision-mission-section', ['headingTag' => 'h1'])
    @include('partials.home-core-values-section')
</x-layouts.public>

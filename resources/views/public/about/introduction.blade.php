<x-layouts.public
    :title="__('Company Introduction')"
    :metaDescription="__('Learn about our company history, background, and what drives us to deliver excellence in the energy and coal industry.')"
    :canonicalUrl="route('about.introduction')"
>
    <div class="home-header-background" aria-hidden="true"></div>

    @include('partials.home-company-section', ['headingTag' => 'h1'])
</x-layouts.public>

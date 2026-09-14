<footer class="h-12 bg-white border-t border-slate-200 px-6 flex items-center justify-between text-xs text-slate-500 shrink-0">
    <div>
        <span>&copy; {{ date('Y') }} {{ config('app.name', 'Hanara CMS') }}. All rights reserved.</span>
    </div>
    <div class="flex items-center gap-4">
        <span>Laravel v{{ Illuminate\Foundation\Application::VERSION }} (PHP v{{ PHP_VERSION }})</span>
    </div>
</footer>

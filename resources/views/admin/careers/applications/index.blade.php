@php
    $jobTitle = $career->getTranslation('title', app()->getLocale());
    $canDelete = auth()->user()->can('career-applications.delete');
    $statusLabels = ['sent' => 'Sent', 'failed' => 'Failed', 'pending' => 'Pending', 'legacy' => 'Not sent'];
    $rows = $applications->map(fn ($application) => [
        'id' => $application->id,
        'name' => $application->name,
        'initials' => mb_strtoupper(mb_substr($application->name, 0, 2)),
        'email' => $application->email,
        'phone' => $application->phone,
        'linkedin' => $application->linkedin,
        'ip' => $application->ip_address ?: '—',
        'date' => \App\Support\LocalTime::format($application->created_at, 'd M Y, H:i T'),
        'status' => $application->email_status,
        'statusLabel' => $statusLabels[$application->email_status] ?? $application->email_status,
        'attempts' => $application->email_attempts,
        'recipient' => $application->email_recipient ?: '—',
        'sentAt' => \App\Support\LocalTime::format($application->email_sent_at),
        'error' => $application->email_last_error ? __($application->email_last_error, [], 'en') : null,
        'filename' => $application->cv_original_name,
        'cvUrl' => $application->cv_path && $application->email_status !== 'sent'
            ? route('admin.careers.applications.cv', [$career, $application]) : null,
        'canDelete' => auth()->user()->can('delete', $application),
    ])->values();
@endphp

<x-layouts.admin :title="'Applicants · '.$jobTitle">
    <div class="ca-page" x-data="careerApplications({{ Js::from($rows) }})">
        <nav class="ca-breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('admin.careers.index') }}">Careers</a><span>/</span><span>Applicants</span>
        </nav>

        <header class="ca-heading">
            <div>
                <p class="ca-eyebrow">RECRUITMENT</p>
                <h1>Applications</h1>
                <div class="ca-subtitle">
                    <x-career-icon name="briefcase" />
                    <span>{{ $jobTitle }}</span>
                    <span class="ca-job-state {{ $career->is_closed || ! $career->is_active ? 'is-closed' : '' }}">{{ ! $career->is_active ? 'Unpublished' : ($career->is_closed ? 'Applications closed' : 'Accepting applications') }}</span>
                </div>
            </div>
            <a href="{{ route('admin.careers.index') }}" class="ca-button"><x-career-icon name="arrow" /> Back to jobs</a>
        </header>

        <section class="ca-stats" aria-label="Application summary for this job">
            @foreach([
                ['total', 'Total applicants', 'users'],
                ['sent', 'Emails sent', 'check'],
                ['failed', 'Emails failed', 'alert'],
                ['pending', 'Not sent', 'clock'],
            ] as [$key, $label, $icon])
                <div class="ca-stat ca-stat--{{ $key }}">
                    <span class="ca-stat-icon"><x-career-icon :name="$icon" /></span>
                    <div><p>{{ $label }}</p><strong>{{ number_format($stats[$key]) }}</strong></div>
                </div>
            @endforeach
        </section>

        <section class="ca-card" aria-labelledby="applicants-heading">
            <div class="ca-card-title"><h2 id="applicants-heading">Applicants</h2><span class="ca-count">{{ $applications->total() }}</span></div>
            <form method="GET" action="{{ route('admin.careers.applications.index', $career) }}" class="ca-filters">
                <label class="ca-search">
                    <x-career-icon name="search" />
                    <span class="sr-only">Search name or email</span>
                    <input name="search" value="{{ request('search') }}" placeholder="Search applicant name or email…" maxlength="255">
                </label>
                <label class="sr-only" for="application-status">Email status</label>
                <select id="application-status" name="status">
                    <option value="">All email statuses</option>
                    @foreach($statusLabels as $value => $label)
                        <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <button class="ca-button ca-button--primary" type="submit">Apply</button>
                @if(request()->filled('search') || request()->filled('status'))
                    <a href="{{ route('admin.careers.applications.index', $career) }}" class="ca-reset">Reset filters</a>
                @endif
            </form>

            @if($canDelete && $applications->isNotEmpty())
                <div class="ca-selection" :class="{ 'has-selection': selected.length }">
                    <div class="ca-selection-summary" aria-live="polite">
                        <span x-show="!selected.length">Select applications to manage them in bulk.</span>
                        <strong x-show="selected.length" x-cloak><span x-text="selected.length"></span> selected</strong>
                        <button type="button" x-show="selected.length" x-cloak @click="selected = []">Clear selection</button>
                    </div>
                    <button type="button" class="ca-button ca-button--quiet-danger" :disabled="!selected.length" @click="confirmDelete(selected)">
                        <x-career-icon name="trash" />Delete selected
                    </button>
                </div>
            @endif

            @if($applications->isEmpty())
                <div class="ca-empty">
                    <span class="ca-stat-icon"><x-career-icon name="{{ request()->filled('search') || request()->filled('status') ? 'search' : 'users' }}" /></span>
                    <h3>{{ $stats['total'] ? 'No matching applications' : 'No applicants yet' }}</h3>
                    <p>{{ $stats['total'] ? 'Try another search term or change the email status filter.' : 'Applications for this position will appear here.' }}</p>
                </div>
            @else
                <div class="ca-table-wrap">
                    <table class="ca-table">
                        <thead><tr>
                            @if($canDelete)
                                <th class="ca-check-cell"><input type="checkbox" class="ca-checkbox" aria-label="Select all applications on this page" :checked="allSelected" x-effect="$el.indeterminate = someSelected" @change="toggleAll()"></th>
                            @endif
                            <th>Applicant</th><th>Contact</th><th>Email status</th><th>CV document</th><th>Received</th><th><span class="sr-only">Actions</span></th>
                        </tr></thead>
                        <tbody>
                            @foreach($applications as $application)
                                <tr :class="{ 'is-selected': selected.includes('{{ $application->id }}') }">
                                    @if($canDelete)
                                        <td class="ca-check-cell">
                                            @can('delete', $application)
                                                <input type="checkbox" class="ca-checkbox" value="{{ $application->id }}" x-model="selected" aria-label="Select application from {{ $application->name }}">
                                            @endcan
                                        </td>
                                    @endif
                                    <td><div class="ca-person">
                                        <span class="ca-avatar">{{ mb_strtoupper(mb_substr($application->name, 0, 2)) }}</span>
                                        <div><button type="button" @click="openDetail({{ $application->id }})">{{ $application->name }}</button><small>Applicant #{{ str_pad($application->id, 4, '0', STR_PAD_LEFT) }}</small></div>
                                    </div></td>
                                    <td><a href="mailto:{{ $application->email }}" class="ca-contact">{{ $application->email }}</a><span class="ca-secondary">{{ $application->phone }}</span></td>
                                    <td><span class="ca-badge ca-badge--{{ $application->email_status }}">{{ $statusLabels[$application->email_status] ?? $application->email_status }}</span><span class="ca-secondary">{{ $application->email_attempts }} {{ Str::plural('delivery attempt', $application->email_attempts) }}</span></td>
                                    <td>
                                        @if($application->email_status === 'sent')
                                            <span class="ca-file"><x-career-icon name="mail" />Attached to email</span>
                                        @elseif($application->cv_path)
                                            <a href="{{ route('admin.careers.applications.cv', [$career, $application]) }}" class="ca-file"><x-career-icon name="download" />Download temporary CV</a>
                                        @else
                                            <span class="ca-secondary">Unavailable</span>
                                        @endif
                                    </td>
                                    <td><span class="ca-contact whitespace-nowrap">{{ \App\Support\LocalTime::format($application->created_at, 'd M Y') }}</span><span class="ca-secondary">{{ \App\Support\LocalTime::format($application->created_at, 'H:i T') }}</span></td>
                                    <td><div class="ca-actions">
                                        <button type="button" class="ca-icon-button" title="View details" aria-label="View details {{ $application->name }}" @click="openDetail({{ $application->id }})"><x-career-icon name="eye" /></button>
                                        @can('delete', $application)
                                            <button type="button" class="ca-icon-button is-danger" title="Delete application" aria-label="Delete application {{ $application->name }}" @click="confirmDelete(['{{ $application->id }}'])"><x-career-icon name="trash" /></button>
                                        @endcan
                                    </div></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="ca-footer">
                    <p>Showing {{ $applications->firstItem() }}–{{ $applications->lastItem() }} of {{ $applications->total() }} applications. @if($canDelete)Selection applies to this page.@endif</p>
                    @if($applications->hasPages()) {{ $applications->links() }} @endif
                </div>
            @endif
        </section>
        <p class="ca-note"><x-career-icon name="file" />Delivered CVs are available in the recipient’s inbox. Applicant details remain available here.</p>

        <dialog x-ref="detailDialog" class="ca-dialog ca-dialog--detail" aria-labelledby="applicant-detail-title" @click="if ($event.target === $el) closeDialog($el)">
            <div class="ca-dialog-body">
                <div class="ca-detail-top">
                    <div class="ca-person"><span class="ca-avatar" x-text="detail?.initials"></span><div><h2 id="applicant-detail-title" x-text="detail?.name"></h2><span class="ca-secondary">Application details #<span x-text="detail?.id"></span></span></div></div>
                    <button type="button" class="ca-icon-button" aria-label="Close details" @click="closeDialog($refs.detailDialog)"><x-career-icon name="close" /></button>
                </div>
                <dl class="ca-detail-grid">
                    <div><dt>Applicant email</dt><dd x-text="detail?.email"></dd></div>
                    <div><dt>Phone number</dt><dd x-text="detail?.phone"></dd></div>
                    <div class="ca-detail-wide"><dt>LinkedIn</dt><dd x-text="detail?.linkedin || 'Not provided'"></dd></div>
                    <div><dt>Received</dt><dd x-text="detail?.date"></dd></div>
                    <div><dt>IP address</dt><dd x-text="detail?.ip"></dd></div>
                    <div><dt>Delivery status</dt><dd><span class="ca-badge" :class="'ca-badge--' + detail?.status" x-text="detail?.statusLabel"></span></dd></div>
                    <div><dt>Delivery attempts</dt><dd x-text="detail?.attempts"></dd></div>
                    <div><dt>Recipient email</dt><dd x-text="detail?.recipient"></dd></div>
                    <div><dt>Sent at</dt><dd x-text="detail?.sentAt"></dd></div>
                </dl>
                <div class="ca-detail-file">
                    <span class="ca-file"><x-career-icon name="file" /><strong>Curriculum vitae</strong></span>
                    <p x-text="detail?.filename"></p>
                    <a x-show="detail?.cvUrl" :href="detail?.cvUrl" class="ca-button"><x-career-icon name="download" />Download temporary CV</a>
                    <span x-show="!detail?.cvUrl" class="ca-secondary" x-text="detail?.status === 'sent' ? 'The CV is available in the recipient’s inbox.' : 'The CV file is unavailable.'"></span>
                </div>
                <div x-show="detail?.error" class="ca-notice ca-notice--error" style="margin-top:16px;margin-bottom:0"><x-career-icon name="alert" /><span x-text="detail?.error"></span></div>
            </div>
            <div class="ca-dialog-footer"><button type="button" class="ca-button" @click="closeDialog($refs.detailDialog)">Close details</button></div>
        </dialog>

        @if($canDelete)
            <dialog x-ref="deleteDialog" class="ca-dialog" aria-labelledby="delete-applications-title" aria-describedby="delete-applications-description" @cancel="cancelDialog($event)" @click="if ($event.target === $el) closeDialog($el)">
                <form method="POST" action="{{ route('admin.careers.applications.bulk-destroy', $career) }}" @submit="submitDeletion($event)">
                    @csrf
                    @method('DELETE')
                    <template x-for="id in pendingIds" :key="id"><input type="hidden" name="ids[]" :value="id"></template>
                    <div class="ca-dialog-body">
                        <span class="ca-dialog-mark"><x-career-icon name="trash" /></span>
                        <h2 id="delete-applications-title" x-text="pendingIds.length > 1 ? 'Delete ' + pendingIds.length + ' applications?' : 'Delete this application?'"></h2>
                        <p id="delete-applications-description" class="ca-dialog-copy" x-text="deleteDescription"></p>
                        <div class="ca-dialog-warning"><x-career-icon name="alert" />This action cannot be undone.</div>
                    </div>
                    <div class="ca-dialog-footer">
                        <button type="button" class="ca-button" x-ref="cancelDelete" :disabled="deleting" @click="closeDialog($refs.deleteDialog)">Cancel</button>
                        <button type="submit" class="ca-button ca-button--danger" :disabled="deleting || !pendingIds.length"><x-career-icon name="trash" /><span x-text="deleting ? 'Deleting…' : 'Yes, delete'"></span></button>
                    </div>
                </form>
            </dialog>
        @endif
    </div>
</x-layouts.admin>

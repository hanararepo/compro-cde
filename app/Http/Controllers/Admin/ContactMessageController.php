<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactMessageController extends Controller
{
    /**
     * Display a paginated, filterable list of contact messages.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', ContactMessage::class);

        $messages = ContactMessage::query()
            ->when($request->search, function ($query, $search) {
                $term = "%{$search}%";
                $query->where(function ($q) use ($term) {
                    $q->where('name', 'like', $term)
                        ->orWhere('email', 'like', $term)
                        ->orWhere('subject', 'like', $term);
                });
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                if ($request->status === 'read') {
                    $query->where('is_read', true);
                } elseif ($request->status === 'unread') {
                    $query->where('is_read', false);
                }
            })
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.contact-messages.index', compact('messages'));
    }

    /**
     * Display a single contact message and mark it as read.
     */
    public function show(ContactMessage $contactMessage): View
    {
        $this->authorize('view', $contactMessage);

        if (! $contactMessage->is_read) {
            $contactMessage->update(['is_read' => true]);
        }

        return view('admin.contact-messages.show', compact('contactMessage'));
    }

    /**
     * Delete a contact message.
     */
    public function destroy(ContactMessage $contactMessage): RedirectResponse
    {
        $this->authorize('delete', $contactMessage);

        $contactMessage->delete();

        return redirect()
            ->route('admin.contact-messages.index')
            ->with('success', __('Message deleted successfully.'));
    }
}

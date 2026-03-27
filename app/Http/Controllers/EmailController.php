<?php

namespace App\Http\Controllers;

use App\Jobs\SendGmailMessage;
use App\Jobs\SyncGmailMessages;
use App\Models\EmailMessage;
use App\Services\EmailSyncService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EmailController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', EmailMessage::class);

        $query = $request->user()->emailMessages()
            ->with('customer:id,name')
            ->latest('received_at');

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->query('customer_id'));
        }

        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                    ->orWhere('from_address', 'like', "%{$search}%")
                    ->orWhere('from_name', 'like', "%{$search}%")
                    ->orWhere('snippet', 'like', "%{$search}%");
            });
        }

        $emails = $query->paginate(50);

        $selectedThread = null;
        if ($request->filled('thread_id')) {
            $selectedThread = $request->user()->emailMessages()
                ->where('gmail_thread_id', $request->query('thread_id'))
                ->with('customer:id,name', 'attachments')
                ->oldest('received_at')
                ->get();

            $request->user()->emailMessages()
                ->where('gmail_thread_id', $request->query('thread_id'))
                ->where('is_read', false)
                ->update(['is_read' => true]);
        }

        $customerOptions = $request->user()
            ->customers()
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return Inertia::render('emails/index', [
            'emails' => $emails,
            'selectedThread' => $selectedThread,
            'customerOptions' => $customerOptions,
            'filters' => [
                'search' => $request->query('search', ''),
                'customer_id' => $request->query('customer_id', ''),
                'thread_id' => $request->query('thread_id', ''),
            ],
            'hasGmailAccount' => $request->user()->gmailAccount !== null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', EmailMessage::class);

        $validated = $request->validate([
            'to' => 'required|email',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'thread_id' => 'nullable|string',
            'customer_id' => 'nullable|exists:customers,id',
        ]);

        SendGmailMessage::dispatch(
            $request->user()->gmailAccount->id,
            $validated['to'],
            $validated['subject'],
            $validated['body'],
            $validated['thread_id'] ?? null,
            $validated['customer_id'] ?? null,
        );

        return back()->with('success', 'Email queued for sending.');
    }

    public function reply(Request $request, EmailMessage $emailMessage): RedirectResponse
    {
        $this->authorize('view', $emailMessage);

        $validated = $request->validate([
            'body' => 'required|string',
        ]);

        $replyTo = $emailMessage->direction === 'inbound'
            ? $emailMessage->from_address
            : ($emailMessage->to_addresses[0] ?? '');

        $subject = str_starts_with($emailMessage->subject ?? '', 'Re: ')
            ? $emailMessage->subject
            : 'Re: '.($emailMessage->subject ?? '');

        SendGmailMessage::dispatch(
            $request->user()->gmailAccount->id,
            $replyTo,
            $subject,
            $validated['body'],
            $emailMessage->gmail_thread_id,
            $emailMessage->customer_id,
        );

        return back()->with('success', 'Reply queued for sending.');
    }

    public function associate(Request $request, EmailMessage $emailMessage): RedirectResponse
    {
        $this->authorize('update', $emailMessage);

        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
        ]);

        // Associate all messages in the thread
        EmailMessage::where('user_id', $request->user()->id)
            ->where('gmail_thread_id', $emailMessage->gmail_thread_id)
            ->update(['customer_id' => $validated['customer_id']]);

        return back()->with('success', 'Email linked to customer.');
    }

    public function createInquiry(Request $request, EmailMessage $emailMessage): RedirectResponse
    {
        $this->authorize('view', $emailMessage);

        $syncService = app(EmailSyncService::class);
        $inquiry = $syncService->importAsInquiry($emailMessage);

        return to_route('inquiries.show', $inquiry);
    }

    public function sync(Request $request): RedirectResponse
    {
        $gmailAccount = $request->user()->gmailAccount;

        if (! $gmailAccount) {
            return back()->with('error', 'No Gmail account connected.');
        }

        SyncGmailMessages::dispatch($gmailAccount->id);

        return back()->with('success', 'Sync started in the background.');
    }
}

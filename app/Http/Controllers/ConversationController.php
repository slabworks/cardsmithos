<?php

namespace App\Http\Controllers;

use App\Enums\CommunicationMethod;
use App\Enums\ConversationStatus;
use App\Enums\MessageSenderType;
use App\Events\MessageSent;
use App\Http\Requests\StoreConversationRequest;
use App\Http\Requests\UpdateConversationRequest;
use App\Models\Conversation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ConversationController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Conversation::class);

        $conversations = $request->user()
            ->conversations()
            ->with(['customer:id,name', 'latestMessage'])
            ->latest('last_message_at')
            ->get()
            ->map(fn (Conversation $conversation) => [
                ...$conversation->toArray(),
                'participant_name' => $conversation->participantName(),
                'unread_count' => $conversation->messages()
                    ->where('sender_type', MessageSenderType::Customer)
                    ->whereNull('read_at')
                    ->count(),
            ]);

        return Inertia::render('conversations/index', [
            'conversations' => $conversations,
            'statusOptions' => array_map(
                fn (ConversationStatus $case) => [
                    'value' => $case->value,
                    'label' => $case->label(),
                    'color' => $case->color(),
                ],
                ConversationStatus::cases()
            ),
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', Conversation::class);

        return Inertia::render('conversations/create', [
            'customerOptions' => $request->user()
                ->customers()
                ->select('id', 'name', 'email')
                ->orderBy('name')
                ->get()
                ->toArray(),
        ]);
    }

    public function store(StoreConversationRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $body = $data['body'];
        unset($data['body']);

        $customer = $request->user()->customers()->findOrFail($data['customer_id']);

        $conversation = $request->user()->conversations()->create([
            ...$data,
            'access_token' => Str::random(64),
            'guest_name' => $customer->name,
            'guest_email' => $customer->email ?? '',
        ]);

        $message = $conversation->messages()->create([
            'sender_type' => MessageSenderType::User,
            'body' => $body,
        ]);

        broadcast(new MessageSent($message))->toOthers();

        return to_route('conversations.show', $conversation);
    }

    public function show(Request $request, Conversation $conversation): Response
    {
        $this->authorize('view', $conversation);

        // Mark all customer messages as read
        $conversation->messages()
            ->where('sender_type', MessageSenderType::Customer)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $conversation->load(['customer:id,name', 'messages']);

        return Inertia::render('conversations/show', [
            'conversation' => [
                ...$conversation->toArray(),
                'participant_name' => $conversation->participantName(),
                'participant_email' => $conversation->participantEmail(),
            ],
            'customerOptions' => $request->user()
                ->customers()
                ->select('id', 'name', 'email')
                ->orderBy('name')
                ->get()
                ->toArray(),
        ]);
    }

    public function update(UpdateConversationRequest $request, Conversation $conversation): RedirectResponse
    {
        $this->authorize('update', $conversation);

        $conversation->update($request->validated());

        return to_route('conversations.show', $conversation);
    }

    public function destroy(Conversation $conversation): RedirectResponse
    {
        $this->authorize('delete', $conversation);

        $conversation->delete();

        return to_route('conversations.index');
    }

    public function linkCustomer(Request $request, Conversation $conversation): RedirectResponse
    {
        $this->authorize('update', $conversation);

        $validated = $request->validate([
            'customer_id' => ['nullable', 'exists:customers,id'],
            'create_customer' => ['nullable', 'boolean'],
        ]);

        $createCustomer = filter_var($validated['create_customer'] ?? false, FILTER_VALIDATE_BOOLEAN);

        if ($createCustomer) {
            $customerData = ['name' => $conversation->guest_name ?? 'Unknown'];

            if ($conversation->guest_email) {
                $customerData['email'] = $conversation->guest_email;
            }

            $customer = $request->user()->customers()->create($customerData);

            $customer->serviceWaiver()->create([
                'expires_at' => now()->addDays(config('cardsmithos.waiver.expiration_days', 30)),
            ]);

            // Auto-create an inquiry
            $request->user()->inquiries()->create([
                'customer_id' => $customer->id,
                'inquiry_name' => $conversation->guest_name ?? 'Unknown',
                'contact_detail' => $conversation->guest_email ?? 'via messaging',
                'communication_method' => CommunicationMethod::Messaging,
                'inquired_at' => $conversation->created_at->format('Y-m-d'),
                'converted' => true,
            ]);
        } else {
            $customer = $request->user()->customers()->findOrFail($validated['customer_id']);
        }

        $conversation->update(['customer_id' => $customer->id]);

        return to_route('conversations.show', $conversation);
    }
}

<?php

namespace App\Http\Controllers;

use App\Enums\ConversationStatus;
use App\Enums\MessageSenderType;
use App\Events\MessageSent;
use App\Http\Requests\StorePublicConversationRequest;
use App\Http\Requests\StorePublicMessageRequest;
use App\Models\BusinessSettings;
use App\Models\Conversation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class PublicConversationController extends Controller
{
    public function create(string $slug): Response
    {
        $settings = BusinessSettings::where('store_slug', $slug)
            ->where('messaging_enabled', true)
            ->firstOrFail();

        return Inertia::render('public-messages/new', [
            'slug' => $slug,
            'companyName' => $settings->company_name,
        ]);
    }

    public function store(StorePublicConversationRequest $request, string $slug): RedirectResponse
    {
        $settings = BusinessSettings::where('store_slug', $slug)
            ->where('messaging_enabled', true)
            ->firstOrFail();

        $validated = $request->validated();

        $conversation = Conversation::create([
            'user_id' => $settings->user_id,
            'access_token' => Str::random(64),
            'guest_name' => $validated['guest_name'],
            'guest_email' => $validated['guest_email'],
            'status' => ConversationStatus::Open,
        ]);

        $message = $conversation->messages()->create([
            'sender_type' => MessageSenderType::Customer,
            'body' => $validated['body'],
        ]);

        broadcast(new MessageSent($message))->toOthers();

        return to_route('public.conversation.show', [
            'slug' => $slug,
            'accessToken' => $conversation->access_token,
        ]);
    }

    public function show(string $slug, string $accessToken): Response
    {
        $settings = BusinessSettings::where('store_slug', $slug)->firstOrFail();

        $conversation = Conversation::where('access_token', $accessToken)
            ->where('user_id', $settings->user_id)
            ->firstOrFail();

        // Mark owner messages as read by customer
        $conversation->messages()
            ->where('sender_type', MessageSenderType::User)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $conversation->load('messages');

        return Inertia::render('public-messages/show', [
            'slug' => $slug,
            'accessToken' => $accessToken,
            'companyName' => $settings->company_name,
            'conversation' => [
                'id' => $conversation->id,
                'subject' => $conversation->subject,
                'status' => $conversation->status,
                'messages' => $conversation->messages,
            ],
        ]);
    }

    public function storeMessage(StorePublicMessageRequest $request, string $slug, string $accessToken): RedirectResponse
    {
        $settings = BusinessSettings::where('store_slug', $slug)->firstOrFail();

        $conversation = Conversation::where('access_token', $accessToken)
            ->where('user_id', $settings->user_id)
            ->where('status', ConversationStatus::Open)
            ->firstOrFail();

        $validated = $request->validated();

        $message = $conversation->messages()->create([
            'sender_type' => MessageSenderType::Customer,
            'body' => $validated['body'],
        ]);

        broadcast(new MessageSent($message))->toOthers();

        return to_route('public.conversation.show', [
            'slug' => $slug,
            'accessToken' => $accessToken,
        ]);
    }
}

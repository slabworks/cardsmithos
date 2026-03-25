<?php

namespace App\Http\Controllers;

use App\Enums\MessageSenderType;
use App\Events\MessageSent;
use App\Models\Conversation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ConversationMessageController extends Controller
{
    public function store(Request $request, Conversation $conversation): RedirectResponse
    {
        $this->authorize('update', $conversation);

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:5000'],
        ]);

        $message = $conversation->messages()->create([
            'sender_type' => MessageSenderType::User,
            'body' => $validated['body'],
        ]);

        broadcast(new MessageSent($message))->toOthers();

        return to_route('conversations.show', $conversation);
    }
}

<?php

namespace Database\Factories;

use App\Enums\MessageSenderType;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Message>
 */
class MessageFactory extends Factory
{
    private const CUSTOMER_MESSAGES = [
        "Hi! I have a few Pokemon cards I'd like to get restored before sending them to PSA. What's the process?",
        "I've got a 1st Edition Charizard that's seen better days — some whitening on the back and a small crease. Can you help?",
        "What's your turnaround time for a batch of 10 cards? Mostly Yu-Gi-Oh! ultras from LOB.",
        'Do you offer bulk pricing? I have about 30 cards I want cleaned and pressed.',
        'Can you share some before/after photos of cards you\'ve worked on? I want to see the quality.',
        'I shipped my cards yesterday via USPS Priority. Tracking number is on its way!',
        'Just wanted to check — did my package arrive safely?',
        'The last batch you did looked incredible. Ready to send another round!',
        'How do you handle heavily played cards with surface scratches? Worried about my Dark Magician.',
        'Is there a difference between your standard and premium pressing service?',
        "What's the best way to ship cards to you? I want to make sure they're protected.",
        'I have some vintage baseball cards too — do you work on sports cards or just TCGs?',
        'Any chance you can rush my order? I need these back before a local tournament next weekend.',
        'Thanks for the update! The cards look great so far.',
        'Got my cards back today. Absolutely blown away by the results. Thank you!',
    ];

    private const OWNER_MESSAGES = [
        "Thanks for reaching out! I'd be happy to help with your cards. Can you send some photos so I can give you an accurate quote?",
        "Got your package today — everything looks good! I'll start working on them tomorrow.",
        "Here's an update on your cards: the pressing went well, just finishing up the cleaning process.",
        'For a batch of 10 cards, our standard turnaround is about 5-7 business days.',
        'Yes, we do offer bulk pricing! For 30+ cards, we can do a 15% discount on the total.',
        "Great news — your cards are done! I'll send before/after photos shortly.",
        'The Charizard cleaned up beautifully. The whitening is almost completely gone after pressing.',
        'We work on all trading cards — Pokemon, Yu-Gi-Oh!, Magic, and sports cards too.',
        "I'd recommend shipping in a small flat-rate box with cards in top loaders, wrapped in bubble wrap.",
        "Rush orders are possible — there's a small surcharge but I can have them ready by Thursday.",
        "Your cards are packed up and shipping out today. I'll send the tracking number once I have it.",
        'For heavily played cards, we do a multi-step process: gentle cleaning first, then controlled pressing.',
        'Just sent the before/after photos to your email. Let me know what you think!',
        "Happy to help anytime! Don't hesitate to reach out if you have more cards in the future.",
        'The premium service includes an additional micro-cleaning step and individual card assessment.',
    ];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $senderType = fake()->randomElement(MessageSenderType::cases());

        return [
            'conversation_id' => Conversation::factory(),
            'sender_type' => $senderType,
            'body' => fake()->randomElement(
                $senderType === MessageSenderType::Customer
                    ? self::CUSTOMER_MESSAGES
                    : self::OWNER_MESSAGES,
            ),
            'read_at' => fake()->optional(0.7)->dateTimeBetween('-1 month', 'now'),
        ];
    }
}

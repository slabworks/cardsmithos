<?php

namespace Database\Factories;

use App\Models\EmailMessage;
use App\Models\GmailAccount;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EmailMessage>
 */
class EmailMessageFactory extends Factory
{
    private const SUBJECTS = [
        'Charizard Base Set grading inquiry',
        'Re: Your card restoration quote',
        'PSA submission prep — 10 card lot',
        'Question about pressing services',
        'Yu-Gi-Oh! Blue-Eyes restoration',
        'Follow up on Pokemon card cleaning',
        'Bulk order pricing request',
        'Vintage sports card lot — need assessment',
        'Magic: The Gathering foil restoration',
        'Ready to ship my cards — need address',
        'Invoice for card grading prep',
        'Thank you for the amazing restoration!',
        'Shipping update on my cards?',
        'New customer — first time sending cards',
        'Rush order — tournament next weekend',
    ];

    private const SNIPPETS = [
        'Hi, I have a 1st Edition Charizard that needs some work before I send it to PSA...',
        'Thanks for the quote! I\'d like to go ahead with the restoration on all 5 cards...',
        'Just wanted to check on the status of my order. It\'s been about a week since...',
        'I\'m interested in your pressing services. Do you handle modern Pokemon cards...',
        'Great news! The cards came back from grading and they all got 9s or higher...',
        'Could you let me know your current turnaround time? I have some cards I need...',
        'I was referred by a friend who had their vintage lot restored. Looking to send...',
        'Do you offer any discount for bulk orders? I have about 30 cards that need...',
        'The restoration work looks incredible! My Dark Magician looks brand new...',
        'What\'s the best way to package cards for shipping to your shop?',
    ];

    private const NAMES = [
        'Ash Ketchum', 'Seto Kaiba', 'Yugi Muto', 'Brock Harrison',
        'Misty Williams', 'Gary Oak', 'Dawn Palmer', 'May Maple',
        'Professor Oak', 'Joey Wheeler', 'Jace Beleren', 'Liliana Vess',
        'Chandra Nalaar', 'Mike Chen', 'Sarah Johnson',
    ];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $isInbound = fake()->boolean(70);
        $name = fake()->randomElement(self::NAMES);

        return [
            'user_id' => User::factory(),
            'gmail_account_id' => GmailAccount::factory(),
            'customer_id' => null,
            'inquiry_id' => null,
            'gmail_message_id' => fake()->unique()->regexify('[a-f0-9]{16}'),
            'gmail_thread_id' => fake()->regexify('[a-f0-9]{16}'),
            'direction' => $isInbound ? 'inbound' : 'outbound',
            'from_address' => $isInbound ? fake()->safeEmail() : 'shop@cardsmithos.com',
            'from_name' => $isInbound ? $name : null,
            'to_addresses' => $isInbound ? ['shop@cardsmithos.com'] : [fake()->safeEmail()],
            'cc_addresses' => null,
            'subject' => fake()->randomElement(self::SUBJECTS),
            'body_text' => fake()->paragraph(3),
            'body_html' => null,
            'snippet' => fake()->randomElement(self::SNIPPETS),
            'labels' => $isInbound ? ['INBOX', 'UNREAD'] : ['SENT'],
            'is_read' => ! $isInbound,
            'received_at' => fake()->dateTimeBetween('-3 months', 'now'),
        ];
    }
}

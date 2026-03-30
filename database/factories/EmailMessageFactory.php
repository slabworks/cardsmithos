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

    private const BODY_TEXTS = [
        'Hi there, I have a 1st Edition Charizard that has some light whitening on the back edges. I was wondering if your pressing service could help improve the appearance before I send it off to PSA. The card is in pretty good shape overall but I think it could go from an 8 to a 9 with some work.',
        'Thanks for the quote on my Yu-Gi-Oh! lot. I\'d like to go ahead with the restoration on all 5 cards. The Blue-Eyes White Dragon and Dark Magician are the priority — those have the most value. Please let me know the best way to ship them to your shop.',
        'Just wanted to follow up on the status of my Pokemon card order. It\'s been about a week since I dropped them off. The Shadowless Blastoise and the Jungle Pikachu are the ones I\'m most anxious about. Any update on the cleaning process?',
        'I recently pulled a Charizard VMAX Rainbow Rare and noticed a small print line on the front. Do you think pressing could help with that, or is it a surface issue that can\'t be fixed? I\'d hate to send it to Beckett without addressing it first.',
        'Great news! The cards came back from grading and they all got 9s or higher. Your restoration work on that vintage Topps Mickey Mantle was incredible — it went from a 4 to a 7. I\'ll definitely be sending more cards your way soon.',
        'Could you let me know your current turnaround time for a bulk order? I have about 30 cards that need pressing and cleaning before a PSA submission. Mix of modern Pokemon and vintage sports cards. Happy to pay for rush service if available.',
        'I was referred by a friend who had their vintage Magic: The Gathering lot restored by your shop. I have a collection of Revised dual lands that need some TLC. A few have minor bends and one Volcanic Island has some surface grime. What would you recommend?',
        'The restoration work on my Dark Magician looks absolutely incredible — it looks brand new. The whitening on the edges is completely gone and the surface has a beautiful sheen. I\'m confident this will grade at least a 9 now. Thank you!',
        'I\'m new to card collecting and just bought a vintage lot of basketball cards from the 90s. Some of them have soft corners and light creasing. Is this something your pressing service can address? I\'d love to get the Michael Jordan rookie looking its best.',
        'Wanted to reach out about your grading prep service. I have a sealed case of Pokemon Evolving Skies that I\'m planning to open and grade the hits. Would you offer a discount if I commit to sending all the pulls through your shop first?',
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
            'body_text' => fake()->randomElement(self::BODY_TEXTS),
            'body_html' => null,
            'snippet' => fake()->randomElement(self::SNIPPETS),
            'labels' => $isInbound ? ['INBOX', 'UNREAD'] : ['SENT'],
            'is_read' => ! $isInbound,
            'received_at' => fake()->dateTimeBetween('-3 months', 'now'),
        ];
    }
}

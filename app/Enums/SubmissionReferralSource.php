<?php

namespace App\Enums;

enum SubmissionReferralSource: string
{
    case Instagram = 'Instagram';
    case TikTok = 'TikTok';
    case Facebook = 'Facebook';
    case YouTube = 'YouTube';
    case XTwitter = 'X / Twitter';
    case Email = 'Email';
    case Website = 'Website';
    case InPerson = 'In Person';
    case Referral = 'Referral';
    case Other = 'Other';

    public function label(): string
    {
        return $this->value;
    }
}

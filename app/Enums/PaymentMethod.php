<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case ETransfer = 'e_transfer';
    case Venmo = 'venmo';
    case CashApp = 'cash_app';
    case Zelle = 'zelle';
    case Cash = 'cash';
    case Card = 'card';
    case BankTransfer = 'bank_transfer';
    case Check = 'check';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::ETransfer => 'E-transfer',
            self::Venmo => 'Venmo',
            self::CashApp => 'Cash App',
            self::Zelle => 'Zelle',
            self::Cash => 'Cash',
            self::Card => 'Card',
            self::BankTransfer => 'Bank transfer',
            self::Check => 'Check',
            self::Other => 'Other',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::ETransfer => 'emerald',
            self::Venmo => 'blue',
            self::CashApp => 'green',
            self::Zelle => 'violet',
            self::Cash => 'emerald',
            self::Card => 'blue',
            self::BankTransfer => 'violet',
            self::Check => 'amber',
            self::Other => 'slate',
        };
    }
}

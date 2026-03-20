<?php

namespace App\Enums;

enum CardStatus: string
{
    case Backlog = 'backlog';
    case Pending = 'pending';
    case InProgress = 'in_progress';
    case Repaired = 'repaired';

    public function label(): string
    {
        return match ($this) {
            self::Backlog => 'Backlog',
            self::Pending => 'Pending',
            self::InProgress => 'In Progress',
            self::Repaired => 'Repaired',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Backlog => 'slate',
            self::Pending => 'amber',
            self::InProgress => 'blue',
            self::Repaired => 'emerald',
        };
    }

    /**
     * Statuses shown on the dashboard Kanban board.
     *
     * @return self[]
     */
    public static function kanbanStatuses(): array
    {
        return [
            self::Backlog,
            self::Pending,
            self::InProgress,
        ];
    }
}

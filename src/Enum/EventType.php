<?php

namespace App\Enum;

enum EventType: string
{
    case HomeMatch = 'match_domicile';
    case AwayMatch = 'match_exterieur';
    case Tournament = 'tournoi';
    case RugbyForHer = 'rugby_pour_elles';
    case SchoolHoliday = 'vacances_scolaire';

    public function label(): string
    {
        return match ($this) {
            self::HomeMatch => 'Match à domicile',
            self::AwayMatch => "Match à l'extérieur",
            self::Tournament => 'Tournoi',
            self::RugbyForHer => 'Rugby pour elles',
            self::SchoolHoliday => 'Vacances scolaire',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::HomeMatch => '#22c55e',
            self::AwayMatch => '#3b82f6',
            self::Tournament => '#f59e0b',
            self::RugbyForHer => '#ec4899',
            self::SchoolHoliday => '#8b5cf6',
        };
    }
}

<?php

namespace App\Enums;

/**
 * Jenis tindak lanjut yang netral secara medis. Sistem tidak merekomendasikan obat,
 * dosis, atau tindakan medis; detail ditulis manusia pada catatan.
 */
enum FollowUpAction: string
{
    case HomeVisit = 'home_visit';
    case ReferFacility = 'refer_facility';
    case NutritionCounseling = 'nutrition_counseling';
    case StimulationEducation = 'stimulation_education';
    case Remeasure = 'remeasure';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::HomeVisit => 'Kunjungan rumah',
            self::ReferFacility => 'Arahkan ke fasilitas kesehatan',
            self::NutritionCounseling => 'Konseling gizi dan pola asuh',
            self::StimulationEducation => 'Edukasi stimulasi perkembangan',
            self::Remeasure => 'Ukur ulang',
            self::Other => 'Lainnya',
        };
    }
}

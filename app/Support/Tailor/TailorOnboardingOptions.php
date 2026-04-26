<?php

namespace App\Support\Tailor;

class TailorOnboardingOptions
{
    public const SPECIALIZATIONS = [
        'Traditionnel',
        'Haute Couture / Soir'."\u{00E9}",
        'Classique',
        'Moderne',
        'Regular sewing',
    ];

    /**
     * @var array<string, string>
     */
    public const GENDERS = [
        'female' => 'Female',
        'male' => 'Male',
    ];

    public const WILAYAS = [
        'Adrar',
        'Chlef',
        'Laghouat',
        'Oum El Bouaghi',
        'Batna',
        'Bejaia',
        'Biskra',
        'Bechar',
        'Blida',
        'Bouira',
        'Tamanrasset',
        'Tebessa',
        'Tlemcen',
        'Tiaret',
        'Tizi Ouzou',
        'Algiers',
        'Djelfa',
        'Jijel',
        'Setif',
        'Saida',
        'Skikda',
        'Sidi Bel Abbes',
        'Annaba',
        'Guelma',
        'Constantine',
        'Medea',
        'Mostaganem',
        "M'Sila",
        'Mascara',
        'Ouargla',
        'Oran',
        'El Bayadh',
        'Illizi',
        'Bordj Bou Arreridj',
        'Boumerdes',
        'El Tarf',
        'Tindouf',
        'Tissemsilt',
        'El Oued',
        'Khenchela',
        'Souk Ahras',
        'Tipaza',
        'Mila',
        'Ain Defla',
        'Naama',
        'Ain Temouchent',
        'Ghardaia',
        'Relizane',
        'Timimoun',
        'Bordj Badji Mokhtar',
        'Ouled Djellal',
        'Beni Abbes',
        'In Salah',
        'In Guezzam',
        'Touggourt',
        'Djanet',
        "El M'Ghair",
        'El Meniaa',
    ];

    /**
     * @return array<string, string>
     */
    public static function specializationOptions(): array
    {
        return array_combine(self::SPECIALIZATIONS, self::SPECIALIZATIONS);
    }

    /**
     * @return array<string, string>
     */
    public static function genderOptions(): array
    {
        return [
            'female' => __('messages.tailor.genders.female'),
            'male' => __('messages.tailor.genders.male'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function wilayaOptions(): array
    {
        return array_combine(self::WILAYAS, self::WILAYAS);
    }
}

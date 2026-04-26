<?php

namespace App\Filament\Pages;

use App\Models\ShopSetting;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class ShopSettings extends Page implements HasForms
{
    use InteractsWithForms;

    /**
     * @var array<string, string>
     */
    private const SECTION_OPTIONS = [
        'hero' => 'Hero Slider',
        'categories' => 'Category Blocks',
        'new_arrivals' => 'New Arrivals',
        'best_sellers' => 'Best Sellers',
        'category_sections' => 'Featured Category Sections',
        'all_products' => 'All Products Grid',
    ];

    /**
     * @var array<string, string>
     */
    private const HOMEPAGE_SECTION_OPTIONS = [
        'hero' => 'Hero Banner',
        'best_sellers' => 'Best-Selling Products',
        'stats' => 'Live Statistics',
        'testimonials' => 'Customer Testimonials',
        'trust' => 'Trust / Value Blocks',
    ];

    protected static ?string $navigationGroup = 'Settings';

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $navigationLabel = 'Shop Settings';

    protected static ?string $title = 'Shop Settings';

    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.pages.shop-settings';

    public ?array $data = [];

    public function getSubheading(): ?string
    {
        return 'Configure storefront visibility, merchandising controls, and section ordering for the public shop page.';
    }

    public function mount(): void
    {
        $settings = ShopSetting::current();
        $data = $settings->toArray();
        $data['section_order'] = collect($settings->section_order ?? [])
            ->map(fn (string $section): array => ['value' => $section])
            ->values()
            ->all();
        $data['homepage_section_order'] = collect($settings->homepage_section_order ?? [])
            ->map(fn (string $section): array => ['value' => $section])
            ->values()
            ->all();

        $this->form->fill($data);
    }

    public function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->statePath('data')
            ->schema([
                Forms\Components\Section::make('Section Visibility')
                    ->description('Enable or disable each block displayed on the storefront.')
                    ->icon('heroicon-o-eye')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Toggle::make('hero_enabled')
                            ->label('Hero Slider')
                            ->inline(false),
                        Forms\Components\Toggle::make('category_blocks_enabled')
                            ->label('Category Blocks')
                            ->inline(false),
                        Forms\Components\Toggle::make('new_arrivals_enabled')
                            ->label('New Arrivals')
                            ->inline(false),
                        Forms\Components\Toggle::make('best_sellers_enabled')
                            ->label('Best Sellers')
                            ->inline(false),
                        Forms\Components\Toggle::make('category_sections_enabled')
                            ->label('Category Sections')
                            ->inline(false),
                        Forms\Components\Toggle::make('all_products_enabled')
                            ->label('All Products Grid')
                            ->inline(false),
                    ]),
                Forms\Components\Section::make('Homepage Controls')
                    ->description('Control the public homepage hero, premium sections, and bilingual call-to-action copy.')
                    ->icon('heroicon-o-sparkles')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Toggle::make('homepage_hero_enabled')
                            ->label('Homepage Hero')
                            ->inline(false),
                        Forms\Components\Toggle::make('homepage_stats_enabled')
                            ->label('Homepage Statistics')
                            ->inline(false),
                        Forms\Components\Toggle::make('homepage_best_sellers_enabled')
                            ->label('Best Sellers Section')
                            ->inline(false),
                        Forms\Components\Toggle::make('homepage_testimonials_enabled')
                            ->label('Testimonials Section')
                            ->inline(false),
                        Forms\Components\TextInput::make('homepage_badge_en')
                            ->label('Hero Badge (English)')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('homepage_badge_ar')
                            ->label('Hero Badge (Arabic)')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('homepage_title_en')
                            ->label('Hero Title (English)')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('homepage_title_ar')
                            ->label('Hero Title (Arabic)')
                            ->maxLength(255),
                        Forms\Components\Textarea::make('homepage_subtitle_en')
                            ->label('Hero Subtitle (English)')
                            ->rows(3)
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('homepage_subtitle_ar')
                            ->label('Hero Subtitle (Arabic)')
                            ->rows(3)
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('homepage_primary_cta_label_en')
                            ->label('Primary CTA Label (English)')
                            ->maxLength(120),
                        Forms\Components\TextInput::make('homepage_primary_cta_label_ar')
                            ->label('Primary CTA Label (Arabic)')
                            ->maxLength(120),
                        Forms\Components\TextInput::make('homepage_primary_cta_url')
                            ->label('Primary CTA URL')
                            ->default('/shop')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('homepage_secondary_cta_label_en')
                            ->label('Secondary CTA Label (English)')
                            ->maxLength(120),
                        Forms\Components\TextInput::make('homepage_secondary_cta_label_ar')
                            ->label('Secondary CTA Label (Arabic)')
                            ->maxLength(120),
                        Forms\Components\TextInput::make('homepage_secondary_cta_url')
                            ->label('Secondary CTA URL')
                            ->default('/how-it-works')
                            ->maxLength(255),
                    ]),
                Forms\Components\Section::make('Display Controls')
                    ->description('Configure carousel timing and product counts per storefront block.')
                    ->icon('heroicon-o-adjustments-horizontal')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('products_per_section')
                            ->label('Products per Section')
                            ->integer()
                            ->minValue(1)
                            ->maxValue(24)
                            ->required(),
                        Forms\Components\TextInput::make('all_products_per_page')
                            ->label('Products per All Grid Page')
                            ->integer()
                            ->minValue(6)
                            ->maxValue(60)
                            ->required(),
                        Forms\Components\TextInput::make('featured_categories_limit')
                            ->label('Featured Categories Limit')
                            ->integer()
                            ->minValue(1)
                            ->maxValue(12)
                            ->required(),
                        Forms\Components\Toggle::make('hero_autoplay')
                            ->label('Hero Autoplay')
                            ->inline(false),
                        Forms\Components\TextInput::make('hero_autoplay_delay_ms')
                            ->label('Hero Autoplay Delay (ms)')
                            ->integer()
                            ->minValue(2000)
                            ->maxValue(20000)
                            ->required(),
                    ]),
                Forms\Components\Section::make('Section Order')
                    ->description('Drag and drop to reorder storefront blocks.')
                    ->icon('heroicon-o-bars-3')
                    ->schema([
                        Forms\Components\Repeater::make('section_order')
                            ->schema([
                                Forms\Components\Select::make('value')
                                    ->label('Section')
                                    ->options(self::SECTION_OPTIONS)
                                    ->required(),
                            ])
                            ->reorderableWithButtons()
                            ->defaultItems(0)
                            ->addActionLabel('Add Section')
                            ->itemLabel(fn (array $state): ?string => self::SECTION_OPTIONS[$state['value'] ?? ''] ?? null),
                    ]),
                Forms\Components\Section::make('Homepage Section Order')
                    ->description('Drag and drop to reorder the premium public homepage blocks.')
                    ->icon('heroicon-o-queue-list')
                    ->schema([
                        Forms\Components\Repeater::make('homepage_section_order')
                            ->schema([
                                Forms\Components\Select::make('value')
                                    ->label('Section')
                                    ->options(self::HOMEPAGE_SECTION_OPTIONS)
                                    ->required(),
                            ])
                            ->reorderableWithButtons()
                            ->defaultItems(0)
                            ->addActionLabel('Add Homepage Section')
                            ->itemLabel(fn (array $state): ?string => self::HOMEPAGE_SECTION_OPTIONS[$state['value'] ?? ''] ?? null),
                    ]),
            ]);
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $data['section_order'] = collect($data['section_order'] ?? [])
            ->pluck('value')
            ->filter()
            ->unique()
            ->values()
            ->all();
        $data['homepage_section_order'] = collect($data['homepage_section_order'] ?? [])
            ->pluck('value')
            ->filter()
            ->unique()
            ->values()
            ->all();

        ShopSetting::current()->update($data);

        Notification::make()
            ->title('Shop settings saved')
            ->success()
            ->send();
    }
}

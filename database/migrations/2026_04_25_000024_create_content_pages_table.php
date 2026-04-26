<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('content_pages', function (Blueprint $table): void {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title_en');
            $table->string('title_ar')->nullable();
            $table->string('excerpt_en')->nullable();
            $table->string('excerpt_ar')->nullable();
            $table->longText('body_en')->nullable();
            $table->longText('body_ar')->nullable();
            $table->string('placement')->default('footer')->index();
            $table->boolean('show_in_footer')->default(true)->index();
            $table->boolean('is_published')->default(false)->index();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamps();
        });

        DB::table('content_pages')->insert([
            [
                'slug' => 'privacy-policy',
                'title_en' => 'Privacy Policy',
                'title_ar' => 'سياسة الخصوصية',
                'excerpt_en' => 'How Makasouk handles customer, tailor, and order data.',
                'excerpt_ar' => 'كيف تتعامل مقصوك مع بيانات العملاء والخياطين والطلبات.',
                'body_en' => '<p>This page is managed from the Filament admin panel. Replace this starter text with your full privacy policy before production launch.</p>',
                'body_ar' => '<p>تتم إدارة هذه الصفحة من لوحة Filament. استبدل هذا النص التمهيدي بسياسة الخصوصية الكاملة قبل الإطلاق.</p>',
                'placement' => 'footer',
                'show_in_footer' => true,
                'is_published' => true,
                'sort_order' => 10,
                'published_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'slug' => 'terms-and-conditions',
                'title_en' => 'Terms & Conditions',
                'title_ar' => 'الشروط والأحكام',
                'excerpt_en' => 'Marketplace usage rules for customers, tailors, and administrators.',
                'excerpt_ar' => 'قواعد استخدام السوق للعملاء والخياطين والإدارة.',
                'body_en' => '<p>This page is managed from the Filament admin panel. Replace this starter text with your final terms and conditions before production launch.</p>',
                'body_ar' => '<p>تتم إدارة هذه الصفحة من لوحة Filament. استبدل هذا النص التمهيدي بالشروط والأحكام النهائية قبل الإطلاق.</p>',
                'placement' => 'footer',
                'show_in_footer' => true,
                'is_published' => true,
                'sort_order' => 20,
                'published_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'slug' => 'refund-policy',
                'title_en' => 'Refund / Return Policy',
                'title_ar' => 'سياسة الاسترجاع والإرجاع',
                'excerpt_en' => 'Starter page for refund, return, and cancellation policy details.',
                'excerpt_ar' => 'صفحة تمهيدية لتفاصيل سياسة الاسترجاع والإرجاع والإلغاء.',
                'body_en' => '<p>This page is managed from the Filament admin panel. Replace this starter text with your final refund and return policy before production launch.</p>',
                'body_ar' => '<p>تتم إدارة هذه الصفحة من لوحة Filament. استبدل هذا النص التمهيدي بسياسة الاسترجاع والإرجاع النهائية قبل الإطلاق.</p>',
                'placement' => 'footer',
                'show_in_footer' => true,
                'is_published' => true,
                'sort_order' => 30,
                'published_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('content_pages');
    }
};

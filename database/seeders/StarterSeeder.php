<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Meta\AdminCore\Models\MenuItem;
use Meta\AdminCore\Models\PageBlock;
use Meta\AdminCore\Models\Setting;
use Spatie\Permission\Models\Role;

/**
 * One-shot starter seed — runs once on `composer create-project`.
 *
 * Idempotent (firstOrCreate everywhere) so re-running it doesn't dupe data.
 *
 * Creates:
 *   - role: admin
 *   - user: admin@localhost / password=admin12345 (CHANGE ON FIRST LOGIN)
 *   - settings: brand name + default contacts
 *   - menu: «Главная» root item
 *   - page-block: hero on `home` so / renders something out of the box
 */
class StarterSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedRolesAndAdmin();
        $this->seedSettings();
        $this->seedMenu();
        $this->seedHomeBlock();

        $this->command->newLine();
        $this->command->info('✔ Starter seed complete.');
        $this->command->line('  Admin: admin@localhost / admin12345');
        $this->command->line('  Visit /admin to log in. CHANGE THE PASSWORD.');
    }

    protected function seedRolesAndAdmin(): void
    {
        // Reset cached roles + permissions so the fresh role is visible
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

        $user = User::firstOrCreate(
            ['email' => 'admin@localhost'],
            [
                'name'              => 'Admin',
                'password'          => Hash::make('admin12345'),
                'email_verified_at' => now(),
            ]
        );

        if (! $user->hasRole('admin')) {
            $user->assignRole($admin);
        }
    }

    protected function seedSettings(): void
    {
        $defaults = [
            ['key' => 'brand_name',    'value' => 'My Site',           'group' => 'general'],
            ['key' => 'brand_tagline', 'value' => 'Powered by meta',   'group' => 'general'],
            ['key' => 'contact_email', 'value' => 'info@example.com',  'group' => 'contacts'],
            ['key' => 'contact_phone', 'value' => '+7 000 000 00 00',  'group' => 'contacts'],
        ];

        foreach ($defaults as $row) {
            Setting::firstOrCreate(
                ['key' => $row['key']],
                ['value' => ['ru' => $row['value'], 'kk' => $row['value'], 'en' => $row['value']],
                 'type'  => 'text',
                 'group' => $row['group']]
            );
        }
    }

    protected function seedMenu(): void
    {
        // Translatable title lives in translations table. Insert raw to
        // stay schema-agnostic — package models may layer translations
        // differently per consumer.
        $home = MenuItem::firstOrCreate(
            ['slug' => 'home'],
            [
                'parent_id'    => null,
                'content_type' => 'url',  // custom URL, not a polymorphic ref
                'content_id'   => 0,
                'is_published' => true,
                'menu_order'   => 1,
            ]
        );

        DB::table('translations')->updateOrInsert(
            ['translatable_type' => MenuItem::class, 'translatable_id' => $home->id, 'locale' => 'ru', 'field' => 'title'],
            ['value' => 'Главная', 'created_at' => now(), 'updated_at' => now()]
        );
        DB::table('translations')->updateOrInsert(
            ['translatable_type' => MenuItem::class, 'translatable_id' => $home->id, 'locale' => 'ru', 'field' => 'url'],
            ['value' => '/', 'created_at' => now(), 'updated_at' => now()]
        );
    }

    protected function seedHomeBlock(): void
    {
        // Base columns stay scalar; trilingual title/subtitle live in
        // the `translations` morph table (the admin-core Translatable
        // pattern). Same with menu_items above.
        $hero = PageBlock::firstOrCreate(
            ['page_name' => 'home', 'block_key' => 'hero'],
            [
                'block_type' => 'hero',
                'title'      => 'Добро пожаловать',
                'subtitle'   => 'Это стартовый сайт',
                'data'       => [
                    'background' => 'red',
                    'buttons' => [
                        ['text' => 'Начать', 'url' => '/admin', 'style' => 'primary'],
                    ],
                ],
                'settings'   => [],
                'is_active'  => true,
                'status'     => 'published',
                'sort_order' => 0,
            ]
        );

        $translations = [
            ['ru' => 'Добро пожаловать',    'kk' => 'Қош келдіңіз',  'en' => 'Welcome',                 'field' => 'title'],
            ['ru' => 'Это стартовый сайт',  'kk' => 'Бұл бастапқы сайт', 'en' => 'This is a starter site', 'field' => 'subtitle'],
        ];
        foreach ($translations as $t) {
            foreach (['ru', 'kk', 'en'] as $locale) {
                DB::table('translations')->updateOrInsert(
                    ['translatable_type' => PageBlock::class, 'translatable_id' => $hero->id, 'locale' => $locale, 'field' => $t['field']],
                    ['value' => $t[$locale], 'created_at' => now(), 'updated_at' => now()]
                );
            }
        }
    }
}

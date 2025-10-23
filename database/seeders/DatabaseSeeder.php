<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\UserLevel;
use App\Models\EventProgram;
use App\Models\Template;
use App\Models\Budget;
use App\Models\Item;
use App\Models\BudgetApproval;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create User Levels
        UserLevel::insert([
            ['id_level' => 1, 'level_name' => 'Admin'],
            ['id_level' => 2, 'level_name' => 'Staff'],
            ['id_level' => 3, 'level_name' => 'Manager'],
            ['id_level' => 4, 'level_name' => 'Director'],
        ]);

        // Create Users
        User::create([
            'id_user' => 1,
            'name' => 'Administrator',
            'email' => 'admin@metrotv.com',
            'username' => 'admin123',
            'password' => Hash::make('password123'),
            'user_level_id' => 1,
        ]);

        User::create([
            'id_user' => 2,
            'name' => 'John Doe',
            'email' => 'john@metrotv.com',
            'username' => 'john123',
            'password' => Hash::make('password123'),
            'user_level_id' => 2,
        ]);

        User::create([
            'id_user' => 3,
            'name' => 'Jane Smith',
            'email' => 'jane@metrotv.com',
            'username' => 'jane123',
            'password' => Hash::make('password123'),
            'user_level_id' => 3,
        ]);

        // Create Event Programs
        EventProgram::insert([
            [
                'id_program' => 1,
                'name' => 'Event Akhir Tahun',
                'type' => 'ON AIR',
                'description' => 'Program spesial akhir tahun MetroTV'
            ],
            [
                'id_program' => 2,
                'name' => 'Merdeka Concert',
                'type' => 'OFF AIR',
                'description' => 'Konser kemerdekaan Indonesia'
            ],
            [
                'id_program' => 3,
                'name' => 'Talkshow Ramadan',
                'type' => 'ON AIR',
                'description' => 'Program talk show khusus bulan ramadan'
            ],
        ]);

        // Create Templates
        Template::insert([
            [
                'id_template' => 1,
                'name' => 'Template Event Standard',
                'description' => 'Template standar untuk event MetroTV',
                'program_id' => 1,
                'created_by' => 1
            ],
            [
                'id_template' => 2,
                'name' => 'Template Konser',
                'description' => 'Template khusus untuk konser musik',
                'program_id' => 2,
                'created_by' => 1
            ],
            [
                'id_template' => 3,
                'name' => 'Template Talkshow',
                'description' => 'Template untuk program talkshow',
                'program_id' => 3,
                'created_by' => 1
            ],
        ]);

        // Create Items
        Item::insert([
            [
                'id_item' => 1,
                'item_name' => 'Sound System',
                'bottom_price' => 5000000.00,
                'top_price' => 8000000.00,
                'unit' => 'Set',
                'margin' => 15.00,
                'me' => 'Audio equipment untuk acara'
            ],
            [
                'id_item' => 2,
                'item_name' => 'Lighting Equipment',
                'bottom_price' => 3000000.00,
                'top_price' => 6000000.00,
                'unit' => 'Set',
                'margin' => 20.00,
                'me' => 'Peralatan pencahayaan panggung'
            ],
            [
                'id_item' => 3,
                'item_name' => 'Catering',
                'bottom_price' => 50000.00,
                'top_price' => 100000.00,
                'unit' => 'Pax',
                'margin' => 10.00,
                'me' => 'Konsumsi untuk crew dan talent'
            ],
            [
                'id_item' => 4,
                'item_name' => 'Talent Fee',
                'bottom_price' => 10000000.00,
                'top_price' => 50000000.00,
                'unit' => 'Orang',
                'margin' => 5.00,
                'me' => 'Honor untuk talent atau pembicara'
            ],
            [
                'id_item' => 5,
                'item_name' => 'Venue Rental',
                'bottom_price' => 15000000.00,
                'top_price' => 30000000.00,
                'unit' => 'Hari',
                'margin' => 8.00,
                'me' => 'Sewa venue untuk acara'
            ],
        ]);

        // Create Sample Budgets
        $budgets = [
            [
                'id_budget' => 1,
                'master_name' => 'Budget Event Akhir Tahun 2024',
                'description' => 'Budget untuk event spesial akhir tahun',
                'periode_from' => '2024-12-20',
                'periode_to' => '2024-12-31',
                'pic' => 'John Doe',
                'dept' => 'Production',
                'template_id' => 1,
                'created_by' => 2
            ],
            [
                'id_budget' => 2,
                'master_name' => 'Budget Merdeka Concert 2024',
                'description' => 'Budget untuk konser kemerdekaan',
                'periode_from' => '2024-08-10',
                'periode_to' => '2024-08-17',
                'pic' => 'Jane Smith',
                'dept' => 'Entertainment',
                'template_id' => 2,
                'created_by' => 3
            ],
            [
                'id_budget' => 3,
                'master_name' => 'Budget Talkshow Ramadan 2024',
                'description' => 'Budget untuk program talkshow ramadan',
                'periode_from' => '2024-03-10',
                'periode_to' => '2024-04-10',
                'pic' => 'Ahmad Rahman',
                'dept' => 'News',
                'template_id' => 3,
                'created_by' => 2
            ],
            [
                'id_budget' => 4,
                'master_name' => 'Budget Workshop Video Editing',
                'description' => 'Budget untuk workshop internal',
                'periode_from' => '2024-09-01',
                'periode_to' => '2024-09-03',
                'pic' => 'Sarah Wilson',
                'dept' => 'Creative',
                'template_id' => 1,
                'created_by' => 2
            ],
            [
                'id_budget' => 5,
                'master_name' => 'Budget Peluncuran Produk Baru',
                'description' => 'Budget untuk event peluncuran produk',
                'periode_from' => '2024-10-15',
                'periode_to' => '2024-10-16',
                'pic' => 'Michael Chen',
                'dept' => 'Marketing',
                'template_id' => 2,
                'created_by' => 3
            ],
            [
                'id_budget' => 6,
                'master_name' => 'Budget Pelatihan Jurnalis Muda',
                'description' => 'Budget untuk program pelatihan jurnalis',
                'periode_from' => '2024-11-05',
                'periode_to' => '2024-11-07',
                'pic' => 'Lisa Rodriguez',
                'dept' => 'Training',
                'template_id' => 3,
                'created_by' => 2
            ],
        ];

        foreach ($budgets as $budget) {
            Budget::create($budget);
        }

        // Create Budget Approvals with different statuses
        $approvals = [
            [
                'budget_id' => 1,
                'approved_by' => 1,
                'status' => 'Approved',
                'comment' => 'Budget telah disetujui sesuai proposal',
                'approved_at' => now()->subDays(5)->format('Y-m-d H:i:s')
            ],
            [
                'budget_id' => 2,
                'approved_by' => 1,
                'status' => 'Pending',
                'comment' => null,
                'approved_at' => now()->subDays(2)->format('Y-m-d H:i:s')
            ],
            [
                'budget_id' => 3,
                'approved_by' => 1,
                'status' => 'Approved',
                'comment' => 'Disetujui dengan revisi minor',
                'approved_at' => now()->subDays(10)->format('Y-m-d H:i:s')
            ],
            [
                'budget_id' => 4,
                'approved_by' => 1,
                'status' => 'Pending',
                'comment' => null,
                'approved_at' => now()->subDays(1)->format('Y-m-d H:i:s')
            ],
            [
                'budget_id' => 5,
                'approved_by' => 1,
                'status' => 'SendBack',
                'comment' => 'Perlu revisi pada item catering',
                'approved_at' => now()->subDays(3)->format('Y-m-d H:i:s')
            ],
            [
                'budget_id' => 6,
                'approved_by' => 1,
                'status' => 'Approved',
                'comment' => 'Budget disetujui penuh',
                'approved_at' => now()->subDays(7)->format('Y-m-d H:i:s')
            ],
        ];

        foreach ($approvals as $approval) {
            BudgetApproval::create($approval);
        }
    }
}
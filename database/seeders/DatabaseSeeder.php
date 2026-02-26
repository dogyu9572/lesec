<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(AdminUserSeeder::class);
        $this->call(AdminMenuSeeder::class);
        $this->call(AdminGroupSeeder::class);
        $this->call(UserSeeder::class);
        $this->call(UserMenuPermissionsSeeder::class);

        $this->call(BoardSkinSeeder::class);
        $this->call(BoardTemplateSeeder::class);
        $this->call(CategorySeeder::class);
        $this->call(BoardSeeder::class);

        $this->call(SettingSeeder::class);
        $this->call(BannerSeeder::class);
        $this->call(PopupSeeder::class);

        $this->call(ProgramSeeder::class);
        $this->call(ScheduleSeeder::class);
        $this->call(SchoolSeeder::class);
        $this->call(SidoSggCodeSeeder::class);

        $this->call(BoardNoticesSeeder::class);
        $this->call(BoardLibrarySeeder::class);
        $this->call(BoardFaqSeeder::class);
        $this->call(BoardGreetingsSeeder::class);
        $this->call(BoardContactsSeeder::class);
        $this->call(BoardPrivacyPolicySeeder::class);
        $this->call(BoardPurposeSeeder::class);
        $this->call(BoardCommentsSeeder::class);

        $this->call(RevenueStatisticsSeeder::class);
        $this->call(RevenueStatisticsItemsSeeder::class);
        $this->call(MailSmsMessagesSeeder::class);
    }
}

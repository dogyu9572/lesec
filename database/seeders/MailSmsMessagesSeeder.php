<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MailSmsMessagesSeeder extends Seeder
{
    /**
     * 메일/문자 메시지 템플릿. data/mail_sms_messages.json 이 있으면 시딩 (현재 DB와 동일).
     */
    public function run(): void
    {
        $path = database_path('seeders/data/mail_sms_messages.json');
        if (!is_file($path)) {
            return;
        }
        $rows = json_decode(file_get_contents($path), true);
        $validMemberGroupIds = DB::table('member_groups')->pluck('id')->flip()->all();
        DB::table('mail_sms_messages')->delete();
        foreach ($rows as $row) {
            if (isset($row['member_group_id']) && $row['member_group_id'] !== null && !isset($validMemberGroupIds[$row['member_group_id']])) {
                $row['member_group_id'] = null;
            }
            DB::table('mail_sms_messages')->insert($row);
        }
    }
}

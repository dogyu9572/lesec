# 시더 데이터 (현재 DB = 이관 후 시드 데이터)

**현재 서버**에서 한 번 실행한 뒤 생성된 JSON을 이 폴더에 두고 이관하면, 새 서버에서 `migrate` + `db:seed` 만으로 동일 데이터로 복원됩니다.

```bash
php artisan db:export-seed-data
```

## 제외 테이블 (export/시드 안 함)

- **회원/신청**: `members`, `member_groups`, `program_reservations`, `group_applications`, `group_application_participants`, `individual_applications`
- **접속·로그**: `admin_access_logs`, `user_access_logs`, `visitor_logs`, `daily_visitor_stats`, `mail_sms_logs`, `mail_sms_message_member`, `phone_verifications`
- **시스템**: `cache`, `cache_locks`, `password_reset_tokens`, `sessions`, `jobs`, `job_batches`, `failed_jobs`

## 테이블 ↔ JSON ↔ 시더 매칭

| 테이블 | JSON 파일 | 시더 |
|--------|-----------|------|
| admin_menus | admin_menus.json | AdminMenuSeeder |
| admin_groups | admin_groups.json | AdminGroupSeeder |
| admin_group_menu_permissions | admin_group_menu_permissions.json | AdminGroupSeeder |
| users | users.json | UserSeeder |
| user_menu_permissions | user_menu_permissions.json | UserMenuPermissionsSeeder |
| board_skins | board_skins.json | BoardSkinSeeder |
| board_templates | board_templates.json | BoardTemplateSeeder |
| categories | categories.json | CategorySeeder |
| boards | boards.json | BoardSeeder |
| settings | settings.json | SettingSeeder |
| banners | banners.json | BannerSeeder |
| popups | popups.json | PopupSeeder |
| programs | programs.json | ProgramSeeder |
| schedules | schedules.json | ScheduleSeeder |
| schools | schools.json | SchoolSeeder |
| sido_sgg_codes | sido_sgg_codes.json | SidoSggCodeSeeder |
| board_notices | board_notices.json | BoardNoticesSeeder |
| board_library | board_library.json | BoardLibrarySeeder |
| board_faq | board_faq.json | BoardFaqSeeder |
| board_greetings | board_greetings.json | BoardGreetingsSeeder |
| board_contacts | board_contacts.json | BoardContactsSeeder |
| board_privacy-policy | board_privacy_policy.json | BoardPrivacyPolicySeeder |
| board_purpose | board_purpose.json | BoardPurposeSeeder |
| board_comments | board_comments.json | BoardCommentsSeeder |
| revenue_statistics | revenue_statistics.json | RevenueStatisticsSeeder |
| revenue_statistics_items | revenue_statistics_items.json | RevenueStatisticsItemsSeeder |
| mail_sms_messages | mail_sms_messages.json | MailSmsMessagesSeeder |

위 제외 테이블을 뺀 **나머지 테이블 전부**가 export 대상이며, 각 JSON이 있으면 해당 시더가 그대로 넣습니다.

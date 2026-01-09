<?php

namespace App\Services\Member;

use App\Models\Member;
use App\Support\Formatting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class MemberRegisterService
{
    public function createMember(array $data, array $additional, string $memberType): Member
    {
        $contact = $this->normalizeContact($data['contact'] ?? '');
        $parentContact = $this->normalizeContact($additional['parent_contact'] ?? '');
        $birthDate = Carbon::createFromFormat('Ymd', $data['birth_date'])->format('Y-m-d');
        $notificationAgreed = (bool) ($data['notification_agree'] ?? false);
        $hashedPassword = Hash::make($data['password']);

        return DB::transaction(function () use ($data, $additional, $memberType, $contact, $parentContact, $birthDate, $notificationAgreed, $hashedPassword) {
            return Member::create([
                'login_id' => $data['login_id'],
                'password' => $hashedPassword,
                'member_type' => $memberType,
                'member_group_id' => null,
                'name' => $data['name'],
                'email' => $data['email'],
                'birth_date' => $birthDate,
                'gender' => $data['gender'],
                'contact' => $contact,
                'parent_contact' => $parentContact ?: null,
                'city' => $data['city'] ?? null,
                'district' => $data['district'] ?? null,
                'school_name' => $data['school_name'] ?? null,
                'school_id' => $data['school_id'] ?? null,
                'grade' => $additional['grade'] ?? null,
                'class_number' => $additional['class_number'] ?? null,
                'address' => null,
                'zipcode' => null,
                'emergency_contact' => null,
                'emergency_contact_relation' => null,
                'profile_image' => null,
                'email_consent' => $notificationAgreed,
                'sms_consent' => $notificationAgreed,
                'kakao_consent' => $notificationAgreed,
                'memo' => null,
                'is_active' => true,
                'joined_at' => now(),
                'last_login_at' => null,
                'withdrawal_at' => null,
                'withdrawal_reason' => null,
            ]);
        });
    }

    public function normalizeEmailForCheck(string $email): ?string
    {
        $email = trim($email);
        if ($email === '' || !str_contains($email, '@')) {
            return null;
        }
        $parts = explode('@', $email);
        if (count($parts) !== 2) {
            return null;
        }
        [$id, $domain] = $parts;
        if ($id === '' || $domain === '') {
            return null;
        }
        $normalized = $id . '@' . $domain;
        return filter_var($normalized, FILTER_VALIDATE_EMAIL) ? $normalized : null;
    }

    public function normalizeContact(?string $number): ?string
    {
        if (empty($number)) {
            return null;
        }
        $digits = preg_replace('/[^0-9]/', '', $number);
        return $digits !== '' ? $digits : null;
    }

    public function formatContactForDisplay(?string $digits): ?string
    {
        return Formatting::formatPhone($digits);
    }
}



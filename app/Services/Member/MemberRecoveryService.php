<?php

namespace App\Services\Member;

use App\Models\Member;
use App\Support\Formatting;
use Illuminate\Support\Facades\Hash;

class MemberRecoveryService
{
    public function normalizeContact(?string $number): ?string
    {
        if (empty($number)) {
            return null;
        }
        $digits = preg_replace('/[^0-9]/', '', $number);
        return $digits !== '' ? $digits : null;
    }

    public function formatContact(?string $digits): ?string
    {
        return Formatting::formatPhone($digits);
    }

    public function findMemberByNameAndContact(string $name, string $contact): ?Member
    {
        return Member::where('name', $name)
            ->where(function ($query) use ($contact) {
                $query->where('contact', $contact)
                    ->orWhere('contact', Formatting::formatPhone($contact) ?? $contact);
            })
            ->first();
    }

    public function findMemberForPassword(string $loginId, string $name, string $contact): ?Member
    {
        return Member::where('login_id', $loginId)
            ->where('name', $name)
            ->where(function ($query) use ($contact) {
                $query->where('contact', $contact)
                    ->orWhere('contact', Formatting::formatPhone($contact) ?? $contact);
            })
            ->first();
    }

    public function updatePassword(Member $member, string $plainPassword): void
    {
        $member->password = Hash::make($plainPassword);
        $member->save();
    }
}



<?php

namespace App\Policies;

use App\Models\BankQuestion;
use App\Models\User;

class BankQuestionPolicy
{
    public function view(User $user, BankQuestion $bankQuestion): bool
    {
        return $user->id === $bankQuestion->user_id;
    }

    public function delete(User $user, BankQuestion $bankQuestion): bool
    {
        return $user->id === $bankQuestion->user_id;
    }
}

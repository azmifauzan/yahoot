<?php

namespace App\Policies;

use App\Enums\QuizVisibility;
use App\Models\Quiz;
use App\Models\User;

class QuizPolicy
{
    /**
     * Determine whether the user can practice the quiz solo.
     *
     * Public + published quizzes are open to everyone (including guests);
     * private quizzes only to their owner.
     */
    public function practice(?User $user, Quiz $quiz): bool
    {
        if ($quiz->is_published && $quiz->visibility === QuizVisibility::Public) {
            return true;
        }

        return $user !== null && $user->id === $quiz->user_id;
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Quiz $quiz): bool
    {
        return $user->id === $quiz->user_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Quiz $quiz): bool
    {
        return $user->id === $quiz->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Quiz $quiz): bool
    {
        return $user->id === $quiz->user_id;
    }

    /**
     * Determine whether the user can duplicate the model.
     */
    public function duplicate(User $user, Quiz $quiz): bool
    {
        if ($quiz->is_published && $quiz->visibility === QuizVisibility::Public) {
            return true;
        }

        return $user->id === $quiz->user_id;
    }

    /**
     * Determine whether the user can host a multiplayer game session for the quiz.
     */
    public function host(User $user, Quiz $quiz): bool
    {
        if ($quiz->is_published && $quiz->visibility === QuizVisibility::Public) {
            return true;
        }

        return $user->id === $quiz->user_id;
    }

    /**
     * Determine whether the user can publish/unpublish the model.
     */
    public function publish(User $user, Quiz $quiz): bool
    {
        return $user->id === $quiz->user_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Quiz $quiz): bool
    {
        return $user->id === $quiz->user_id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Quiz $quiz): bool
    {
        return false;
    }
}

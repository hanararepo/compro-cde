<?php

namespace App\Policies;

use App\Models\ContactMessage;
use App\Models\User;

class ContactMessagePolicy
{
    /**
     * Only users with contact-messages.view permission may list messages.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('contact-messages.view');
    }

    /**
     * Only users with contact-messages.view permission may view a message.
     */
    public function view(User $user, ContactMessage $contactMessage): bool
    {
        return $user->can('contact-messages.view');
    }

    /**
     * Only users with contact-messages.delete permission may delete a message.
     */
    public function delete(User $user, ContactMessage $contactMessage): bool
    {
        return $user->can('contact-messages.delete');
    }

    /**
     * Only users with contact-messages.reply permission may reply to a message.
     */
    public function reply(User $user, ContactMessage $contactMessage): bool
    {
        return $user->can('contact-messages.reply');
    }
}

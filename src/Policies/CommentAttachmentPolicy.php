<?php

namespace Kirschbaum\Commentions\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Kirschbaum\Commentions\CommentAttachment;
use Kirschbaum\Commentions\Contracts\Commenter;

class CommentAttachmentPolicy
{
    use HandlesAuthorization;

    public function view(Commenter $user, CommentAttachment $attachment): bool
    {
        // Users can view attachments if they can view the comment
        return $user->can('view', $attachment->comment);
    }

    public function download(Commenter $user, CommentAttachment $attachment): bool
    {
        // Users can download attachments if they can view the comment
        return $user->can('view', $attachment->comment);
    }

    public function delete(Commenter $user, CommentAttachment $attachment): bool
    {
        // Users can delete attachments if they can delete the comment
        return $user->can('delete', $attachment->comment);
    }
}

<?php
declare(strict_types=1);

namespace Powernic\Bot\Framework\Handler;

use Powernic\Bot\Framework\Bot\Types\ChatMemberUpdated;

abstract class ChatMemberHandler extends Handler
{
    protected ChatMemberUpdated $member;

    public function setMember(ChatMemberUpdated $member): void
    {
        $this->member = $member;
    }

    public function getMember(): ChatMemberUpdated
    {
        return $this->member;
    }
}

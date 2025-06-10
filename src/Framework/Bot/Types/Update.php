<?php
declare(strict_types=1);

namespace Powernic\Bot\Framework\Bot\Types;

use TelegramBot\Api\Types\CallbackQuery;
use TelegramBot\Api\Types\Inline\ChosenInlineResult;
use TelegramBot\Api\Types\Inline\InlineQuery;
use TelegramBot\Api\Types\Message;
use TelegramBot\Api\Types\Payments\Query\PreCheckoutQuery;
use TelegramBot\Api\Types\Payments\Query\ShippingQuery;
use TelegramBot\Api\Types\Poll;
use TelegramBot\Api\Types\PollAnswer;
use TelegramBot\Api\Types\Update as UpdateBase;

class Update extends UpdateBase
{
    protected static $map = [
        'update_id' => true,
        'message' => Message::class,
        'edited_message' => Message::class,
        'channel_post' => Message::class,
        'edited_channel_post' => Message::class,
        'inline_query' => InlineQuery::class,
        'chosen_inline_result' => ChosenInlineResult::class,
        'callback_query' => CallbackQuery::class,
        'shipping_query' => ShippingQuery::class,
        'pre_checkout_query' => PreCheckoutQuery::class,
        'poll_answer' => PollAnswer::class,
        'poll' => Poll::class,
        'my_chat_member' => ChatMemberUpdated::class,
    ];
    /**
     * Optional. The bot's chat member status was updated in a chat. For private chats, this update is received only
     * when the bot is blocked or unblocked by the user.
     *
     * @var ChatMemberUpdated|null
     */
    protected $myChatMember;

    /**
     * @return ChatMemberUpdated|null
     */
    public function getMyChatMember()
    {
        return $this->myChatMember;
    }

    /**
     * @param ChatMemberUpdated|null $myChatMember
     * @return void
     */
    public function setMyChatMember($myChatMember)
    {
        $this->myChatMember = $myChatMember;
    }
}

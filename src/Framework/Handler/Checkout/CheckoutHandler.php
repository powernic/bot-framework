<?php
declare(strict_types=1);

namespace Powernic\Bot\Framework\Handler\Checkout;
use Powernic\Bot\Framework\Handler\Handler;
use TelegramBot\Api\Types\Message;

abstract class CheckoutHandler extends Handler
{
    protected int $totalAmount;
    private bool $isSuccessful = false;
    protected ?string $preCheckoutQueryId = null;
    private ?Message $message = null;
    protected string $userId;

    public function setUserId(string $userId): self
    {
        $this->userId = $userId;
        return $this;
    }

    /**
     * @param Message $message
     * @return self
     */
    public function setMessage(Message $message): self
    {
        $this->message = $message;

        return $this;
    }

    public function getMessage(): ?Message
    {
        return $this->message;
    }

    public function setSuccessful(): self
    {
        $this->isSuccessful = true;
        return $this;
    }

    public function handle(): void
    {
        if($this->isSuccessful){
            $this->onSuccessful($this->message);
        }{
            $this->onPreCheckout($this->totalAmount);
        }
    }

    public function setPreCheckoutQueryId(string $preCheckoutQueryId): self
    {
        $this->preCheckoutQueryId = $preCheckoutQueryId;
        return $this;
    }



    public function setTotalAmount(int $totalAmount): self
    {
        $this->totalAmount = $totalAmount;
        return $this;
    }

    protected function onPreCheckout(int $totalAmount): void
    {
    }

    protected function onSuccessful(Message $message): void
    {

    }
}

<?php
namespace App\Service\Notification;

use App\Event\ProductCreatedEvent;

interface NotificationHandlerInterface
{
    public function handle(ProductCreatedEvent $event): void;
}

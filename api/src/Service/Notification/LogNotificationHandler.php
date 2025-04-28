<?php
namespace App\Service\Notification;

use App\Event\ProductCreatedEvent;
use Psr\Log\LoggerInterface;

class LogNotificationHandler implements NotificationHandlerInterface
{
    public function __construct(
        private LoggerInterface $logger
    ) {}

    public function handle(ProductCreatedEvent $event): void
    {
        $product = $event->getProduct();

        $this->logger->info('Product created', [
            'id'         => $product->getId(),
            'name'       => $product->getName(),
            'categories' => $product->getCategories()->map(fn($c) => $c->getCode())->toArray(),
        ]);
    }
}

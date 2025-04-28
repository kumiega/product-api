<?php
namespace App\EventSubscriber;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\Product;
use App\Event\ProductCreatedEvent;
use App\Service\Notification\NotificationHandlerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ProductNotificationSubscriber implements EventSubscriberInterface
{
    /**
     * @param iterable<NotificationHandlerInterface> $handlers
     */
    public function __construct(
        private iterable $handlers,
        private LoggerInterface $logger

    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => ['onProductCreated', EventPriorities::POST_WRITE],
        ];
    }

    public function onProductCreated(ViewEvent $event): void
    {
        $this->logger->info('ProductNotificationSubscriber triggered');

        $product = $event->getControllerResult();

        if (! $product instanceof Product) {
            $this->logger->info('Controller result is not a Product');
            return;
        }

        if (! $event->getRequest()->isMethod('POST')) {
            $this->logger->info('Request method is not POST');
            return;
        }

        foreach ($this->handlers as $handler) {
            try {
                $handler->handle(new ProductCreatedEvent($product));
            } catch (\Throwable $e) {
                error_log("Handler " . get_class($handler) . " failed: " . $e->getMessage());
            }
        }

        $this->logger->info('Handlers count: ' . iterator_count($this->handlers));
    }
}

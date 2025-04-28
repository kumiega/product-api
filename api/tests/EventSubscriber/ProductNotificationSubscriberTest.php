<?php
namespace App\Tests\EventSubscriber;

use App\Entity\Product;
use App\EventSubscriber\ProductNotificationSubscriber;
use App\Event\ProductCreatedEvent;
use App\Service\Notification\NotificationHandlerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class ProductNotificationSubscriberTest extends TestCase
{
    public function testHandlersAreCalledOnProductCreation()
    {
        $product = new Product();
        $product->setName('Test Product');
        $product->setPrice('10.00');

        $handler1 = $this->createMock(NotificationHandlerInterface::class);
        $handler2 = $this->createMock(NotificationHandlerInterface::class);

        $handler1->expects($this->once())
            ->method('handle')
            ->with($this->isInstanceOf(ProductCreatedEvent::class));
        $handler2->expects($this->once())
            ->method('handle')
            ->with($this->isInstanceOf(ProductCreatedEvent::class));

        $logger = $this->createMock(LoggerInterface::class);

        $subscriber = new ProductNotificationSubscriber([$handler1, $handler2], $logger);

        $request = new Request([], [], [], [], [], ['REQUEST_METHOD' => 'POST']);
        $kernel  = $this->createMock(HttpKernelInterface::class);
        $event   = new ViewEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST, $product);

        $subscriber->onProductCreated($event);
    }
}

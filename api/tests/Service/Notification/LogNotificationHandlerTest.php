<?php
namespace App\Tests\Service\Notification;

use App\Entity\Product;
use Psr\Log\LoggerInterface;
use App\Event\ProductCreatedEvent;
use App\Service\Notification\LogNotificationHandler;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class LogNotificationHandlerTest extends KernelTestCase
{
    public function testHandleLogsProductCreation()
    {
        $product = new Product();
        $product->setName('Test Product');
        $product->setPrice('10.00');

        $logger = $this->createMock(LoggerInterface::class);

        $logger->expects($this->once())
            ->method('info')
            ->with(
                'Product created',
                $this->arrayHasKey('name')
            );

        $handler = new LogNotificationHandler($logger);
        $event   = new ProductCreatedEvent($product);

        $handler->handle($event);
    }
}

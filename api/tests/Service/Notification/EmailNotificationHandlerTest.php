<?php
namespace App\Tests\Service\Notification;

use App\Entity\Product;
use App\Event\ProductCreatedEvent;
use App\Service\Notification\EmailNotificationHandler;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Mailer\MailerInterface;

class EmailNotificationHandlerTest extends KernelTestCase
{
    public function testHandleSendsEmail()
    {
        $mailer = $this->createMock(MailerInterface::class);
        $logger = $this->createMock(LoggerInterface::class);

        $handler = new EmailNotificationHandler($mailer, $logger, 'admin@example.com');

        $product = new Product();
        $product->setName('Test Product');
        $event = new ProductCreatedEvent($product);

        // Setup expectations on $mailer mock
        $mailer->expects($this->once())
            ->method('send');

        $handler->handle($event);
    }

}

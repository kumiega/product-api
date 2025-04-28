<?php
namespace App\Service\Notification;

use App\Entity\Product;
use App\Event\ProductCreatedEvent;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class EmailNotificationHandler implements NotificationHandlerInterface
{
    public function __construct(
        private MailerInterface $mailer,
        private LoggerInterface $logger,
        private string $adminEmail = 'admin@example.com'
    ) {}

    public function handle(ProductCreatedEvent $event): void
    {
        $product = $event->getProduct();

        $email = (new Email())
            ->from($this->adminEmail)
            ->to($this->adminEmail)
            ->subject('New product created')
            ->html($this->createEmailContent($product));

        try {
            $this->mailer->send($email);
        } catch (TransportExceptionInterface $e) {
            $this->logger->error("Email sending failed: " . $e->getMessage());
        }
    }

    private function createEmailContent(Product $product): string
    {
        return sprintf(
            "<h1>New product: %s</h1>\n<p>Categories: %s</p>",
            $product->getName(),
            implode(', ', $product->getCategories()->map(fn($c) => $c->getCode())->toArray())
        );
    }
}

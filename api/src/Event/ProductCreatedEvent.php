<?php
namespace App\Event;

use App\Entity\Product;
use Symfony\Contracts\EventDispatcher\Event;

class ProductCreatedEvent extends Event
{
    public const NAME = 'product.created';

    public function __construct(
        private Product $product,
        private array $originalCategories = []
    ) {}

    public function getProduct(): Product
    {
        return $this->product;
    }

    public function getOriginalCategories(): array
    {
        return $this->originalCategories;
    }
}

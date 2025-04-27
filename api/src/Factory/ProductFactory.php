<?php
namespace App\Factory;

use App\Entity\Product;
use App\Factory\CategoryFactory;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Product>
 */
final class ProductFactory extends PersistentProxyObjectFactory
{

    public function __construct()
    {
    }

    public static function class (): string
    {
        return Product::class;
    }

    protected function defaults(): array | callable
    {
        return [
            'name'       => self::faker()->words(3, true),
            'price'      => \sprintf('%.2f', self::faker()->randomFloat(2, 5, 500)),
            'categories' => CategoryFactory::new ()->many(1, 3),
        ];
    }

    protected function initialize(): static
    {
        return $this;
    }
}

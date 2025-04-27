<?php
namespace App\Factory;

use App\Entity\Category;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Category>
 */
final class CategoryFactory extends PersistentProxyObjectFactory
{
    public function __construct()
    {
    }

    public static function class (): string
    {
        return Category::class;
    }

    protected function defaults(): array | callable
    {
        return [
            'code' => strtoupper(self::faker()->unique()->lexify('CAT????')),
        ];
    }

    protected function initialize(): static
    {
        return $this;
    }
}

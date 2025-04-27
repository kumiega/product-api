<?php
namespace App\Story;

use App\Factory\CategoryFactory;
use App\Factory\ProductFactory;
use Zenstruck\Foundry\Story;

final class DefaultStory extends Story
{
    public function build(): void
    {
        CategoryFactory::createMany(10);

        ProductFactory::createMany(100, function () {
            return [
                'categories' => CategoryFactory::randomRange(1, 3),
            ];
        });
    }
}

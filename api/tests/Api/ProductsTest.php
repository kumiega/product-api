<?php
namespace App\Tests;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\Category;
use App\Entity\Product;
use App\Factory\CategoryFactory;
use App\Factory\ProductFactory;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class ProductsTest extends ApiTestCase
{
    use ResetDatabase, Factories;

    public function testGetCollection(): void
    {
        $client = static::createClient();

        ProductFactory::createMany(100);

        $response = $client->request('GET', '/products');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $this->assertJsonContains([
            '@context'   => '/contexts/Product',
            '@id'        => '/products',
            '@type'      => 'Collection',
            'totalItems' => 100,
            'view'       => [
                '@id'   => '/products?page=1',
                '@type' => 'PartialCollectionView',
                'first' => '/products?page=1',
                'last'  => '/products?page=10',
                'next'  => '/products?page=2',
            ],
        ]);

        $this->assertCount(10, $response->toArray()['member']);
        $this->assertMatchesResourceCollectionJsonSchema(Product::class);
    }

    public function testGetCollectionWithPagination(): void
    {
        $client = static::createClient();

        ProductFactory::createMany(100);

        $response = $client->request('GET', '/products?page=2');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $this->assertJsonContains([
            '@context'   => '/contexts/Product',
            '@id'        => '/products',
            '@type'      => 'Collection',
            'totalItems' => 100,
            'view'       => [
                '@id'   => '/products?page=2',
                '@type' => 'PartialCollectionView',
                'first' => '/products?page=1',
                'last'  => '/products?page=10',
                'next'  => '/products?page=3',
            ],
        ]);

        $this->assertCount(10, $response->toArray()['member']);
        $this->assertMatchesResourceCollectionJsonSchema(Product::class);
    }

    public function testPaginationBeyondLimit(): void
    {
        $client = static::createClient();

        ProductFactory::createMany(100);

        $response = $client->request('GET', '/products?page=11');

        $this->assertResponseIsSuccessful();
        $this->assertCount(0, $response->toArray()['member']);
        $this->assertJsonContains([
            'view' => [
                '@id'      => '/products?page=11',
                'previous' => '/products?page=10',
            ],
        ]);
    }

    public function testInvalidPriceFormat(): void
    {
        $client = static::createClient();

        $client->request('POST', '/products', [
            'json' => ['price' => 'not-a-number'],
        ]);

        $this->assertResponseStatusCodeSame(422);
    }

    public function testCreateProduct(): void
    {
        $client = static::createClient();

        $category = CategoryFactory::createOne(['code' => 'CATKNKX']);

        $response = $client->request('POST', '/products', ['json' => [
            'name'       => 'Super Product',
            'price'      => '21.37',
            'categories' => ['/categories/' . $category->getId()],
        ]]);

        $this->assertResponseStatusCodeSame(201);
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $this->assertJsonContains([
            '@context'   => '/contexts/Product',
            '@type'      => 'Product',
            'name'       => 'Super Product',
            'price'      => '21.37',
            'categories' => [
                ['code' => 'CATKNKX'],
            ],
        ]);

        $this->assertMatchesRegularExpression('~^/products/\d+$~', $response->toArray()['@id']);
        $this->assertMatchesResourceItemJsonSchema(Product::class);
    }

    public function testCreateInvalidProduct(): void
    {
        $client = static::createClient();

        $client->request('POST', '/products', ['json' => [
            'name'       => '',
            'price'      => '-10',
            'categories' => [],
        ]]);

        $this->assertResponseStatusCodeSame(422);
        $this->assertResponseHeaderSame('content-type', 'application/problem+json; charset=utf-8');

        $this->assertJsonContains([
            '@context' => '/contexts/ConstraintViolation',
            '@type'    => 'ConstraintViolation',
            'title'    => 'An error occurred',
        ]);
    }

    public function testUpdateProduct(): void
    {
        $client = static::createClient();

        $product = ProductFactory::new ()->create();

        $category = CategoryFactory::createOne();

        $categoryIri = $this->findIriBy(Category::class, ['id' => $category->getId()]);

        $iri = $this->findIriBy(Product::class, ['id' => $product->getId()]);

        $client->request('PATCH', $iri, [
            'json'    => [
                'name'       => 'Updated Product Name',
                'categories' => [$categoryIri],
            ],
            'headers' => [
                'Content-Type' => 'application/merge-patch+json',
            ],
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            '@id'        => $iri,
            'name'       => 'Updated Product Name',
            'categories' => [
                ['@id' => $categoryIri],
            ],
        ]);
    }

    public function testDeleteProduct(): void
    {
        $client = static::createClient();

        $category = CategoryFactory::createOne();

        $product = ProductFactory::createOne([
            'categories' => [$category],
        ]);
        $productId = $product->getId();

        $iri = $this->findIriBy(Product::class, ['id' => $productId]);

        $client->request('DELETE', $iri);

        $this->assertResponseStatusCodeSame(204);

        static::getContainer()->get('doctrine')->getManager()->clear();

        $this->assertNull(
            static::getContainer()->get('doctrine')->getRepository(Product::class)->find($productId)
        );
    }
}

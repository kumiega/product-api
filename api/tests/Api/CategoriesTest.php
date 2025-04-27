<?php
namespace App\Tests\Api;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\Category;
use App\Factory\CategoryFactory;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class CategoriesTest extends ApiTestCase
{
    use ResetDatabase, Factories;

    public function testGetCollection(): void
    {
        CategoryFactory::createMany(30);

        $response = static::createClient()->request('GET', '/categories');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $this->assertJsonContains([
            '@context'   => '/contexts/Category',
            '@id'        => '/categories',
            '@type'      => 'Collection',
            'totalItems' => 30,
            'view'       => [
                '@id'   => '/categories?page=1',
                '@type' => 'PartialCollectionView',
                'first' => '/categories?page=1',
                'last'  => '/categories?page=3',
                'next'  => '/categories?page=2',
            ],
        ]);
        $this->assertCount(10, $response->toArray()['member']);
        $this->assertMatchesResourceCollectionJsonSchema(Category::class);
    }

    public function testGetCollectionWithPagination(): void
    {
        $client = static::createClient();

        CategoryFactory::createMany(100);

        $response = $client->request('GET', '/categories?page=2');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $this->assertJsonContains([
            '@context'   => '/contexts/Category',
            '@id'        => '/categories',
            '@type'      => 'Collection',
            'totalItems' => 100,
            'view'       => [
                '@id'   => '/categories?page=2',
                '@type' => 'PartialCollectionView',
                'first' => '/categories?page=1',
                'last'  => '/categories?page=10',
                'next'  => '/categories?page=3',
            ],
        ]);

        $this->assertCount(10, $response->toArray()['member']);
        $this->assertMatchesResourceCollectionJsonSchema(Category::class);
    }

    public function testPaginationBeyondLimit(): void
    {
        $client = static::createClient();

        CategoryFactory::createMany(100);

        $response = $client->request('GET', '/categories?page=11');

        $this->assertResponseIsSuccessful();
        $this->assertCount(0, $response->toArray()['member']);
        $this->assertJsonContains([
            'view' => [
                '@id'      => '/categories?page=11',
                'previous' => '/categories?page=10',
            ],
        ]);
    }

    public function testCreateCategory(): void
    {
        $response = static::createClient()->request('POST', '/categories', ['json' => [
            'code' => 'CAT123',
        ]]);

        $this->assertResponseStatusCodeSame(201);
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $this->assertJsonContains([
            '@context' => '/contexts/Category',
            '@type'    => 'Category',
            'code'     => 'CAT123',
        ]);

        $this->assertMatchesRegularExpression('~^/categories/\d+$~', $response->toArray()['@id']);
        $this->assertMatchesResourceItemJsonSchema(Category::class);
    }

    public function testCreateInvalidCategory(): void
    {
        $client = static::createClient();

        $client->request('POST', '/categories', ['json' => []]);

        $this->assertResponseStatusCodeSame(422);
        $this->assertJsonContains([
            'violations' => [
                ['propertyPath' => 'code', 'message' => 'This value should not be blank.'],
            ],
        ]);

        $client->request('POST', '/categories', ['json' => [
            'code' => 'LONGCODE123',
        ]]);

        $this->assertResponseStatusCodeSame(422);
        $this->assertJsonContains([
            'violations' => [
                ['propertyPath' => 'code', 'message' => 'This value is too long. It should have 10 characters or less.'],
            ],
        ]);

        CategoryFactory::createOne(['code' => 'CAT123']);

        $client->request('POST', '/categories', ['json' => [
            'code' => 'CAT123',
        ]]);

        $this->assertResponseStatusCodeSame(422);
        $this->assertJsonContains([
            'violations' => [
                ['propertyPath' => 'code', 'message' => 'Provided category code is already in use.'],
            ],
        ]);
    }

    public function testUpdateCategory(): void
    {
        $client = static::createClient();

        $category   = CategoryFactory::createOne(['code' => 'OLDCODE']);
        $categoryId = $category->getId();

        $client->request('PATCH', '/categories/' . $categoryId, [
            'headers' => ['Content-Type' => 'application/merge-patch+json'],
            'json'    => ['code' => 'NEWCODE'],
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            '@id'  => '/categories/' . $categoryId,
            'code' => 'NEWCODE',
        ]);
    }

    public function testDeleteCategory(): void
    {
        $client = static::createClient();

        $category   = CategoryFactory::createOne();
        $categoryId = $category->getId();

        $client->request('DELETE', '/categories/' . $categoryId);

        $this->assertResponseStatusCodeSame(204);

        static::getContainer()->get('doctrine')->getManager()->clear();

        $this->assertNull(
            static::getContainer()->get('doctrine')->getRepository(Category::class)->find($categoryId)
        );
    }

    public function testTimestampsAutomaticallySet(): void
    {
        $client = static::createClient();

        $response = $client->request('POST', '/categories', ['json' => [
            'code' => 'TIMETEST',
        ]]);

        $categoryId = $response->toArray()['@id'];

        $client->request('PATCH', $categoryId, [
            'headers' => ['Content-Type' => 'application/merge-patch+json'],
            'json'    => ['code' => 'UPDATED'],
        ]);

        $updatedResponse = static::createClient()->request('GET', $categoryId);
        $this->assertNotNull($updatedResponse->toArray()['updatedAt']);
    }
}

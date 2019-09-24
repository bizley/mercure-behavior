<?php

declare(strict_types=1);

namespace bizley\tests;

use PHPUnit\Framework\TestCase;
use Throwable;
use Yii;
use yii\base\InvalidConfigException;
use yii\db\Connection;
use yii\db\Exception;
use yii\db\sqlite\Schema;
use yii\db\StaleObjectException;
use yii\helpers\Json;
use yii\web\Application;

class BehaviorOwnerTest extends TestCase
{
    /**
     * @throws InvalidConfigException
     * @throws Exception
     */
    public static function setUpBeforeClass(): void
    {
        new Application([
            'id' => 'MercureBehaviorTest',
            'basePath' => __DIR__,
            'vendorPath' => __DIR__ . '/../vendor/',
            'components' => [
                'db' => [
                    'class' => Connection::class,
                    'dsn' => 'sqlite::memory:'
                ],
            ],
        ]);

        Yii::$app->db->createCommand()->createTable(
            'resource',
            [
                'id' => Schema::TYPE_PK,
                'one' => Schema::TYPE_STRING . '(45)',
                'two' => Schema::TYPE_STRING . '(45)'
            ]
        )->execute();
        Yii::$app->db->createCommand()->insert(
            'resource',
            [
                'id' => 1,
                'one' => 'a',
                'two' => 'b'
            ]
        )->execute();
    }

    public static function tearDownAfterClass(): void
    {
        Yii::$app = null;
    }

    /**
     * @test
     */
    public function shouldPublishUpdateAfterInsert(): void
    {
        $resource = new MockResource([
            'one' => 'c',
            'two' => 'd',
        ]);

        $resource->save(false);

        $this->assertSame(
            ['https://example.com/resources/2'],
            MockMercurePublisher::$updateReceived->getTopics()
        );
        $this->assertEquals(
            ['id' => 2, 'one' => 'c', 'two' => 'd'],
            Json::decode(MockMercurePublisher::$updateReceived->getData())
        );
        $this->assertSame(
            ['target1', 'target2'],
            MockMercurePublisher::$updateReceived->getTargets()
        );
    }

    /**
     * @test
     */
    public function shouldPublishUpdateAfterUpdate(): void
    {
        $resource = MockResource::findOne(1);
        $resource->one = 'updated-value';

        $resource->save(false);

        $this->assertSame(
            ['https://example.com/resources/1'],
            MockMercurePublisher::$updateReceived->getTopics()
        );
        $this->assertEquals(
            ['id' => 1, 'one' => 'updated-value', 'two' => 'b'],
            Json::decode(MockMercurePublisher::$updateReceived->getData())
        );
        $this->assertSame(
            ['target1', 'target2'],
            MockMercurePublisher::$updateReceived->getTargets()
        );
    }

    /**
     * @test
     * @throws Throwable
     * @throws StaleObjectException
     */
    public function shouldPublishUpdateAfterDelete(): void
    {
        $resource = MockResource::findOne(1);
        $resource->delete();

        $this->assertSame(
            ['https://example.com/resources/1'],
            MockMercurePublisher::$updateReceived->getTopics()
        );
        $this->assertEquals(
            ['@id' => 1],
            Json::decode(MockMercurePublisher::$updateReceived->getData())
        );
        $this->assertSame(
            ['target1', 'target2'],
            MockMercurePublisher::$updateReceived->getTargets()
        );
    }
}

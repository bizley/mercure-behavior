<?php

declare(strict_types=1);

namespace bizley\tests;

use bizley\yii2\behaviors\mercure\MercureableInterface;
use bizley\yii2\behaviors\mercure\MercureBehavior;
use bizley\yii2\behaviors\mercure\Response;
use bizley\yii2\mercure\Publisher;
use bizley\yii2\mercure\Update;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use stdClass;
use yii\base\InvalidConfigException;
use yii\db\BaseActiveRecord;

class MercureBehaviorTest extends TestCase
{
    /**
     * @var MockObject|Publisher
     */
    private $publisher;

    /**
     * @var MockObject|MercureableInterface
     */
    private $owner;

    /**
     * @var MercureBehavior
     */
    private $behavior;

    protected function setUp(): void
    {
        $this->publisher = $this->createMock(Publisher::class);
        $this->publisher->useYii2Client = false;
        $this->publisher->method('publish')->willReturnCallback(static function (Update $arg) {
            return $arg->getData();
        });

        $this->owner = $this->createMock(MercureableInterface::class);
        $this->owner->method('toArray')->willReturn(['property' => 1]);
        $this->owner->method('getTopic')->willReturn('');
        $this->owner->method('getId')->willReturn(99);
        $this->owner->method('getMercureTarget')->willReturn([]);

        $this->behavior = new MercureBehavior([
            'publisher' => $this->publisher,
            'owner' => $this->owner
        ]);
        $this->behavior->setResponse(new Response([
            'format' => Response::FORMAT_JSON,
            'charset' => 'UTF-8'
        ]));
    }

    /**
     * @test
     */
    public function shouldReturnProperLIstOfEvents(): void
    {
        $this->assertSame([
            BaseActiveRecord::EVENT_AFTER_INSERT => 'publishUpdate',
            BaseActiveRecord::EVENT_AFTER_UPDATE => 'publishUpdate',
            BaseActiveRecord::EVENT_AFTER_DELETE => 'publishDelete',
        ], $this->behavior->events());
    }

    /**
     * @test
     * @throws InvalidConfigException
     */
    public function shouldReturnNullWhenOwnerNotImplementingMercureableInterface(): void
    {
        $behavior = new MercureBehavior(['owner' => new stdClass()]);

        $this->assertNull($behavior->publishUpdate());
    }

    /**
     * @test
     * @throws InvalidConfigException
     */
    public function shouldReturnNullWhenPublisherNotConfiguredProperly(): void
    {
        $this->owner->method('hasErrors')->willReturn(false);
        $behavior = new MercureBehavior(['owner' => $this->owner]);

        $this->assertNull($behavior->publishUpdate());
    }

    /**
     * @test
     * @throws InvalidConfigException
     */
    public function shouldReturnNullWhenOwnerHasErrors(): void
    {
        $this->owner->method('hasErrors')->willReturn(true);
        $behavior = new MercureBehavior(['owner' => $this->owner]);

        $this->assertNull($behavior->publishUpdate());
    }

    /**
     * @test
     * @throws InvalidConfigException
     */
    public function shouldReturnJsonDataForUpdate(): void
    {
        $this->owner->method('hasErrors')->willReturn(false);

        $this->assertSame('{"property":1}', $this->behavior->publishUpdate());
    }

    /**
     * @test
     * @throws InvalidConfigException
     */
    public function shouldReturnJsonDataForDelete(): void
    {
        $this->owner->method('hasErrors')->willReturn(false);

        $this->assertSame('{"@id":99}', $this->behavior->publishDelete());
    }

    /**
     * @test
     * @throws InvalidConfigException
     */
    public function shouldReturnXmlDataForUpdate(): void
    {
        $this->owner->method('hasErrors')->willReturn(false);
        $this->behavior->setResponse(new Response([
            'format' => Response::FORMAT_XML,
            'charset' => 'UTF-8'
        ]));

        $this->assertContains('<response><property>1</property></response>', $this->behavior->publishUpdate());
    }
}

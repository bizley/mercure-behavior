<?php

declare(strict_types=1);

namespace bizley\tests;

use bizley\yii2\behaviors\mercure\MercureableInterface;
use bizley\yii2\behaviors\mercure\MercureBehavior;
use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property string $one
 * @property string $two
 */
class MockResource extends ActiveRecord implements MercureableInterface
{
    public function behaviors(): array
    {
        return [
            'mercure' => [
                'class' => MercureBehavior::class,
                'publisher' => MockMercurePublisher::class,
            ]
        ];
    }

    public static function tableName(): string
    {
        return 'resource';
    }

    public function getTopic(): string
    {
        return 'https://example.com/resources/' . $this->id;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getMercureTarget(): array
    {
        return ['target1', 'target2'];
    }
}

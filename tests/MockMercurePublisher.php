<?php

declare(strict_types=1);

namespace bizley\tests;

use bizley\yii2\mercure\Publisher;
use bizley\yii2\mercure\Update;

class MockMercurePublisher extends Publisher
{
    /**
     * @var bool
     */
    public $useYii2Client = false;

    /**
     * @var Update|null
     */
    public static $updateReceived;

    public function publish(Update $update): string
    {
        static::$updateReceived = $update;

        return '';
    }
}

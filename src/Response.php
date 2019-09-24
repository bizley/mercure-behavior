<?php

declare(strict_types=1);

namespace bizley\yii2\behaviors\mercure;

use yii\base\InvalidConfigException;

/**
 * Helper class for benefits of Yii 2 Response formatting utilities.
 *
 * @package bizley\yii2\behaviors\mercure
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 */
class Response extends \yii\web\Response
{
    public function init(): void
    {
        parent::init();

        $this->clear();
    }

    /**
     * @return string
     * @throws InvalidConfigException
     */
    public function prepareContent(): string
    {
        $this->prepare();

        return $this->content;
    }
}

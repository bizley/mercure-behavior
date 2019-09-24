<?php

declare(strict_types=1);

namespace bizley\yii2\behaviors\mercure;

use bizley\yii2\mercure\Publisher;
use bizley\yii2\mercure\Update;
use Yii;
use yii\base\Behavior;
use yii\base\InvalidConfigException;
use yii\db\BaseActiveRecord;
use yii\di\Instance;
use yii\rest\Serializer;
use yii\web\Request;
use function array_key_exists;
use function is_string;

/**
 * MercureBehavior automatically dispatches resource updates to Mercure Hub and specified targets.
 *
 * To use MercureBehavior, insert the following code to your class implementing
 * \bizley\yii2\behaviors\mercure\MercureableInterface:
 *
 * ```php
 * use \bizley\yii2\behaviors\mercure\MercureBehavior;
 *
 * public function behaviors()
 * {
 *     return [
 *         MercureBehavior::class,
 *     ];
 * }
 * ```
 *
 * By default MercureBehavior will dispatch update to Mercure Hub in JSON format after the resource has been
 * successfully created, updated, or deleted, using the Mercure publisher component registered under the 'publisher'
 * name.
 *
 * You can customize the configuration according to your needs, for example:
 *
 * ```php
 * public function behaviors()
 * {
 *     return [
 *         [
 *             'class' => MercureBehavior::class,
 *             'publisher' => \bizley\yii2\mercure\Publisher::class,
 *             'format' => \yii\web\Response::FORMAT_XML
 *         ]
 *     ];
 * }
 * ```
 *
 * @see https://github.com/dunglas/mercure to learn more about Mercure
 *
 * @package bizley\yii2\behaviors\mercure
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 * @see https://github.com/bizley/mercure-behavior
 */
class MercureBehavior extends Behavior
{
    /**
     * @var string|array|Publisher Name of the registered Mercure publisher component, its class name, or configuration
     * array, or publisher object itself.
     */
    public $publisher = 'publisher';

    /**
     * @var MercureableInterface the owner of this behavior.
     */
    public $owner;

    /**
     * @var string|array Serializer class name or the configuration for creating the serializer that prepares the update
     * data. By default it uses \yii\rest\Serializer.
     */
    public $serializer = Serializer::class;

    /**
     * @var string The format in which the data should be prepared. See \yii\web\Response for available formats
     * (you can use any format as long as it outputs string data). By default it is JSON.
     */
    public $format = Response::FORMAT_JSON;

    /**
     * {@inheritdoc}
     */
    public function events(): array
    {
        return [
            BaseActiveRecord::EVENT_AFTER_INSERT => 'publishUpdate',
            BaseActiveRecord::EVENT_AFTER_UPDATE => 'publishUpdate',
            BaseActiveRecord::EVENT_AFTER_DELETE => 'publishDelete',
        ];
    }

    /**
     * @var Response
     */
    private $_response;

    /**
     * Returns response object used only to format the serialized data by its formatter.
     * @return Response
     */
    public function getResponse(): Response
    {
        if ($this->_response === null) {
            $this->_response = new Response(['format' => $this->format]);
        }

        return $this->_response;
    }

    /**
     * Sets response object used only to format the serialized data.
     * @param Response $response
     */
    public function setResponse(Response $response): void
    {
        $this->_response = $response;
    }

    /**
     * @param mixed $data
     * @return mixed
     * @throws InvalidConfigException
     */
    protected function serializeData($data)
    {
        if (is_string($this->serializer)) {
            $this->serializer = ['class' => $this->serializer];
        }

        if (array_key_exists('response', $this->serializer) === false) {
            $this->serializer['response'] = new Response(['charset' => '']);
        }
        if (array_key_exists('request', $this->serializer) === false) {
            $this->serializer['request'] = new Request();
        }

        return Yii::createObject($this->serializer)->serialize($data);
    }

    /**
     * Publishes an update about resource change.
     * @throws InvalidConfigException
     */
    public function publishUpdate(): ?string
    {
        if ($this->owner instanceof MercureableInterface === false) {
            Yii::error('Owner component must implement MercureableInterface.');
            return null;
        }

        if ($this->owner->hasErrors() === false) {
            return $this->publishToMercure($this->serializeData($this->owner));
        }

        return null;
    }

    /**
     * Publishes an update about resource deletion.
     * By default it sends only ID of resource.
     * @throws InvalidConfigException
     */
    public function publishDelete(): ?string
    {
        if ($this->owner instanceof MercureableInterface === false) {
            Yii::error('Owner component must implement MercureableInterface.');
            return null;
        }

        if ($this->owner->hasErrors() === false) {
            return $this->publishToMercure($this->serializeData(['@id' => $this->owner->getId()]));
        }

        return null;
    }

    /**
     * Publishes an update to Mercure hub.
     * @param mixed $data
     * @return string|null
     */
    protected function publishToMercure($data): ?string
    {
        try {
            $this->publisher = Instance::ensure($this->publisher, Publisher::class);

            $this->getResponse()->data = $data;

            return $this->publisher->publish(
                new Update(
                    $this->owner->getTopic(),
                    $this->getResponse()->prepareContent(),
                    $this->owner->getMercureTarget()
                )
            );
        } catch (InvalidConfigException $exception) {
            Yii::error($exception->getMessage());
        }

        return null;
    }
}

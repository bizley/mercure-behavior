<?php

namespace bizley\yii2\behaviors\mercure;

use yii\base\Arrayable;

/**
 * Resource must implement this interface in order to be the subject of Mercure Hub updates.
 *
 * @package bizley\yii2\behaviors\mercure
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 */
interface MercureableInterface extends Arrayable
{
    /**
     * Returns topic being updated.
     * This topic should be an IRI (Internationalized Resource Identifier, RFC 3987): a unique identifier of the
     * resource being dispatched. Usually, this parameter contains the original URL of the resource transmitted to
     * the client, but it can be any valid IRI, it doesn't have to be an URL that exists (similarly to XML namespaces).
     * @see https://tools.ietf.org/html/rfc3987
     * @return string
     */
    public function getTopic();

    /**
     * Returns the ID that can uniquely identify a resource.
     * @return string|int
     */
    public function getId();

    /**
     * Returns the list of Mercure publisher targets.
     * For public updates set to ['*'] - otherwise provide specific targets to dispatch updates only to authorized
     * clients.
     * @return string[]
     */
    public function getMercureTarget();

    /**
     * Returns a value indicating whether there is any validation error.
     * When this method returns true update is not dispatched to Mercure in order to prevent publishing invalid data.
     * @param string|null $attribute attribute name. Use null to check all attributes.
     * @return bool whether there is any error.
     * @see \yii\base\Model::hasErrors
     */
    public function hasErrors($attribute = null);
}

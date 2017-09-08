<?php declare(strict_types=1);

namespace DOMWrap\Traits;

use DOMWrap\NodeList;

/**
 * Node Trait
 *
 * @package DOMWrap\Traits
 * @license http://opensource.org/licenses/BSD-3-Clause BSD 3 Clause
 * @property \DOMDocument $ownerDocument
 */
trait NodeTrait
{
    /** @see TraversalTrait::newNodeList() */
    abstract public function newNodeList(iterable $nodes = null): NodeList;

    /** @see CommonTrait::isRemoved() */
    abstract public function isRemoved(): bool;

    /**
     * @return NodeList
     */
    public function collection(): NodeList {
        return $this->newNodeList([$this]);
    }

    /**
     * @return \DOMDocument
     */
    public function document(): ?\DOMDocument {
        if ($this->isRemoved()) {
            return null;
        }

        return $this->ownerDocument;
    }

    /**
     * @param NodeList $nodeList
     *
     * @return NodeList|\DOMNode|null
     */
    public function result(NodeList $nodeList) {
        if ($nodeList->count()) {
            return $nodeList->first();
        }

        return null;
    }
}
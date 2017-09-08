<?php

namespace DOMWrap;

use DOMWrap\Traits\{
    CommonTrait,
    TraversalTrait,
    ManipulationTrait
};

/**
 * Document Node
 *
 * @package DOMWrap
 * @license http://opensource.org/licenses/BSD-3-Clause BSD 3 Clause
 */
class Document extends \DOMDocument
{
    use CommonTrait;
    use TraversalTrait;
    use ManipulationTrait;

    public function __construct(string $version = null, string $encoding = null) {
        parent::__construct($version, $encoding);

        $this->registerNodeClass('DOMText', 'DOMWrap\\Text');
        $this->registerNodeClass('DOMElement', 'DOMWrap\\Element');
        $this->registerNodeClass('DOMComment', 'DOMWrap\\Comment');
        $this->registerNodeClass('DOMDocumentType', 'DOMWrap\\DocumentType');
        $this->registerNodeClass('DOMProcessingInstruction', 'DOMWrap\\ProcessingInstruction');
    }

    /**
     * {@inheritdoc}
     */
    public function document(): ?\DOMDocument {
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function collection(): NodeList {
        return $this->newNodeList([$this]);
    }

    /**
     * {@inheritdoc}
     */
    public function result(NodeList $nodeList) {
        if ($nodeList->count()) {
            return $nodeList->first();
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function parent() {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function parents() {
        return $this->newNodeList();
    }

    /**
     * {@inheritdoc}
     */
    public function replaceWith($newNode): self {
        $this->replaceChild($newNode, $this);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function _clone() {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getHtml(): string {
        return $this->getOuterHtml();
    }

    /**
     * {@inheritdoc}
     */
    public function setHtml($html): self {
        if (!is_string($html) || trim($html) == '') {
            return $this;
        }

        $internalErrors = libxml_use_internal_errors(true);
        $disableEntities = libxml_disable_entity_loader(true);

        if (mb_detect_encoding($html, mb_detect_order(), true) !== 'UTF-8') {
            if (preg_match('@<meta.*?charset=["\']?([^\'"\s]+)@im', $html, $matches)) {
                $charset = strtoupper($matches[1]);

                $html = preg_replace('@(charset=["\']?)([^\'"\s]+)([^\'"]*[\'"]?)@im', '$1UTF-8$3', $html);
                $html = mb_convert_encoding($html, 'UTF-8', $charset);
            } else {
                $html = mb_convert_encoding($html, 'UTF-8', 'auto');
            }
        }

        $this->loadHTML('<?xml encoding="utf-8"?>' . $html);

        libxml_use_internal_errors($internalErrors);
        libxml_disable_entity_loader($disableEntities);

        return $this;
    }
}

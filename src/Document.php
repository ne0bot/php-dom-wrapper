<?php

namespace DOMWrap;

use DOMWrap\Traits\CommonTrait;
use DOMWrap\Traits\TraversalTrait;
use DOMWrap\Traits\ManipulationTrait;

/**
 * Document Node
 *
 * @package DOMWrap
 * @license http://opensource.org/licenses/BSD-3-Clause BSD 3 Clause
 */
class Document extends \DOMDocument {

    use CommonTrait;
    use TraversalTrait;
    use ManipulationTrait;

    /**
     * Conversion between UTF-8 and UTF-16 imported from Services_JSON
     * http://pear.php.net/pepr/pepr-proposal-show.php?id=198
     * 
     * @return 
     * @param object $str
     * @param object $to_encoding
     * @param object $from_encoding[optional]
     */
    function mb_convert_encoding($str, $to_encoding, $from_encoding = null) {
        if ($from_encoding == 'UTF-16' && $to_encoding == 'UTF-8') {
            $bytes = (ord($str{0}) << 8) | ord($str{1});
            switch (true) {
                case ((0x7F & $bytes) == $bytes):
                    // this case should never be reached, because we are in ASCII range
                    // see: http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                    return chr(0x7F & $bytes);
                case (0x07FF & $bytes) == $bytes:
                    // return a 2-byte UTF-8 character
                    // see: http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                    return chr(0xC0 | (($bytes >> 6) & 0x1F))
                            . chr(0x80 | ($bytes & 0x3F));
                case (0xFFFF & $bytes) == $bytes:
                    // return a 3-byte UTF-8 character
                    // see: http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                    return chr(0xE0 | (($bytes >> 12) & 0x0F))
                            . chr(0x80 | (($bytes >> 6) & 0x3F))
                            . chr(0x80 | ($bytes & 0x3F));
            }
            // ignoring UTF-32 for now, sorry
            return '';
        } else if ($from_encoding == 'UTF-8' && $to_encoding == 'UTF-16') {
            switch (strlen($str)) {
                case 1:
                    // this case should never be reached, because we are in ASCII range
                    // see: http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                    return $str;
                case 2:
                    // return a UTF-16 character from a 2-byte UTF-8 char
                    // see: http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                    return chr(0x07 & (ord($str{0}) >> 2))
                            . chr((0xC0 & (ord($str{0}) << 6)) | (0x3F & ord($str{1})));
                case 3:
                    // return a UTF-16 character from a 3-byte UTF-8 char
                    // see: http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                    return chr((0xF0 & (ord($str{0}) << 4)) | (0x0F & (ord($str{1}) >> 2)))
                            . chr((0xC0 & (ord($str{1}) << 6)) | (0x7F & ord($str{2})));
            }
            // ignoring UTF-32 for now, sorry
            return '';
        }
        return iconv($from_encoding, $to_encoding, $str);
    }

    public function __construct($version = null, $encoding = null) {
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
    public function document() {
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function collection() {
        return $this->newNodeList([$this]);
    }

    /**
     * {@inheritdoc}
     */
    public function result($nodeList) {
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
    public function

parents() {
    return $this->newNodeList();





    }

/**
 * {@inheritdoc}
 */
public function replaceWith($newNode) {
    $this->replaceChild($newNode, $this);

    return $this;
}

/**
 * {@inheritdoc}
 */
public function getHtml() {
    return $this->getOuterHtml();
}

function mb_detect_encoding($string, $enc = null, $ret = null) {

    static $enclist = array(
        'UTF-8', 'ASCII',
        'ISO-8859-1', 'ISO-8859-2', 'ISO-8859-3', 'ISO-8859-4', 'ISO-8859-5',
        'ISO-8859-6', 'ISO-8859-7', 'ISO-8859-8', 'ISO-8859-9', 'ISO-8859-10',
        'ISO-8859-13', 'ISO-8859-14', 'ISO-8859-15', 'ISO-8859-16',
        'Windows-1251', 'Windows-1252', 'Windows-1254',
    );

    $result = false;

    foreach ($enclist as $item) {
        $sample = iconv($item, $item, $string);
        if (md5($sample) == md5($string)) {
            if ($ret === NULL) {
                $result = $item;
            } else {
                $result = true;
            }
            break;
        }
    }

    return $result;
}

/**
 * {@inheritdoc}
 */
public function setHtml($html) {
    if (!is_string($html) || trim($html) == '') {
        return $this;
    }

    $internalErrors = libxml_use_internal_errors(true);
    $disableEntities = libxml_disable_entity_loader(true);

    if ($this->mb_detect_encoding($html, null, true) !== 'UTF-8') {
        if (preg_match('@<meta.*?charset=["\']?([^\'"\s]+)@im', $html, $matches)) {
            $charset = strtoupper($matches[1]);

            $html = preg_replace('@(charset=["\']?)([^\'"\s]+)([^\'"]*[\'"]?)@im', '$1UTF-8$3', $html);
            $html = $this->mb_convert_encoding($html, 'UTF-8', $charset);
        } else {
            $html = $this->mb_convert_encoding($html, 'UTF-8', 'auto');
        }
    }

    $this->loadHTML('<?xml encoding="utf-8"?>' . $html);

    libxml_use_internal_errors($internalErrors);
    libxml_disable_entity_loader($disableEntities);

    return $this;
}

}

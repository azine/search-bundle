<?php

declare(strict_types=1);

namespace EWZ\Bundle\SearchBundle\Lucene;

use Zend\Search\Lucene\Document\Field as ZendField;

class Field extends ZendField
{
    /**
     * Constructs a stored, indexed, non-tokenized keyword field.
     */
    public static function keyword($name, $value, $encoding = 'UTF-8')
    {
        return new self($name, $value, $encoding, true, true, false);
    }

    /**
     * Constructs a stored field that is neither indexed nor tokenized.
     */
    public static function unIndexed($name, $value, $encoding = 'UTF-8')
    {
        return new self($name, $value, $encoding, true, false, false);
    }

    /**
     * Constructs a stored binary field that is neither indexed nor tokenized.
     */
    public static function binary($name, $value)
    {
        return new self($name, $value, '', true, false, false, true);
    }

    /**
     * Constructs a stored, indexed and tokenized text field.
     */
    public static function text($name, $value, $encoding = 'UTF-8')
    {
        return new self($name, $value, $encoding, true, true, true);
    }

    /**
     * Constructs an indexed and tokenized field that is not stored.
     */
    public static function unStored($name, $value, $encoding = 'UTF-8')
    {
        return new self($name, $value, $encoding, false, true, true);
    }

    public function getType(): string
    {
        if (!$this->isStored) {
            return 'UnStored';
        }

        if ($this->isBinary) {
            return 'Binary';
        }

        if ($this->isIndexed) {
            return $this->isTokenized ? 'Text' : 'Keyword';
        }

        return 'UnIndexed';
    }
}

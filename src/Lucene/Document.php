<?php

declare(strict_types=1);

namespace EWZ\Bundle\SearchBundle\Lucene;

use Zend\Search\Lucene\Document as ZendDocument;

class Document extends ZendDocument
{
    public function getFieldType(string $fieldName): string
    {
        $field = $this->getField($fieldName);

        if (!$field instanceof Field) {
            throw new \LogicException(sprintf(
                'Field "%s" was not created through %s and has no bundle field type.',
                $fieldName,
                Field::class,
            ));
        }

        return $field->getType();
    }
}

<?php declare(strict_types=1);

namespace Kedomingo\DtdParser;

use http\Exception\RuntimeException;

class DTDParser
{
    private const ENTITY_PLACEHOLDER = '%[\w_]+;';
    private const POSSIBLE_VALUES = '(?:CDATA|\([^\)]+\)|' . self::ENTITY_PLACEHOLDER . ')';
    private const VARIABLE_POSSIBLE_VALUES = '(?:(\S+)\s+' . self::POSSIBLE_VALUES . ')';
    private const ANNOTATION = '(#REQUIRED|#IMPLIED|#FIXED)';
    private const DEFAULT = '(\'[^\']+\'|"[^"]+")';
    private const ATT_ENTRY = '(\s*(' . self::ENTITY_PLACEHOLDER . '|' . self::VARIABLE_POSSIBLE_VALUES . ')\s*' . self::ANNOTATION . '?\s*' . self::DEFAULT . '?\s*)';

    private const ELEMENT_PATTERN = '/<!ELEMENT\s+(\S+)\s+(EMPTY|\(.*\)\*?|' . self::ENTITY_PLACEHOLDER . ')\s*>/s';
    private const ENTITY_PATTERN = '/<!ENTITY\s+%\s+(\S+)\s*[\'"](' . self::VARIABLE_POSSIBLE_VALUES . '|' . self::POSSIBLE_VALUES . ')\s*' . self::ANNOTATION . '?\s*' . self::DEFAULT . '?[^>]+>/s';
    private const ATTLIST_PATTERN = '/<!ATTLIST\s+(\S+)\s+' . self::ATT_ENTRY . '+/s';

    public function parse(string $dtdString): void
    {
        $entities = $this->getEntities($dtdString);
        $attributes = $this->getAttributes($dtdString, ...$entities);
        print_r($attributes);exit;

        preg_match_all('/<!ELEMENT [^>]+>/s', $dtdString, $elements);
        foreach ($elements[0] as $element) {
            if (!preg_match_all(self::ELEMENT_PATTERN, $element, $matches)) {
                echo $element;
                exit;
            }
        }

        exit;
    }

    /**
     * @param string $dtdString
     * @return Entity[]
     */
    private function getEntities(string $dtdString): array
    {
        $entityObjects = [];
        preg_match_all('/<!ENTITY [^>]+>/s', $dtdString, $entities);
        $i = 0;
        foreach ($entities[0] as $entity) {
            if (!preg_match_all(self::ENTITY_PATTERN, $entity, $matches)) {
                throw new RuntimeException("Unexpected DTD format. ENTITY format not recognized: " . $entity);
            }

            $name = $matches[1][0];
            $attributeValues = $matches[2][0];
            $annotation = $matches[4][0];
            $default = trim($matches[5][0], '\'"');

            preg_match('/([^\(]*)(\([^\)]+\))/', $attributeValues, $attributeValueMatch);
            $attribute = $attributeValueMatch[1];
            $options = explode('|', trim($attributeValueMatch[2], '()'));
            $options = array_map('trim', $options);

            $entityObjects[] = new Entity(
                   !empty($name) ? $name : null,
                   !empty($attribute) ? $attribute : null,
                   $annotation === '#REQUIRED',
                   !empty($default) || is_numeric($default) ? $default : null,
                ...$options
            );
        }

        return $entityObjects;
    }

    private function getAttributes(string $dtdString, Entity ...$entities): array
    {
        $result = [];
        preg_match_all('/<!ATTLIST [^>]+>/s', $dtdString, $attlists);
        foreach ($attlists[0] as $attlist) {
            if (!preg_match_all(self::ATTLIST_PATTERN, $attlist, $matches)) {
                throw new \RuntimeException("Unexpected DTD format. ATTLIST format not recognized: " . $attlist);
            }
            preg_match_all(self::ATT_ENTRY, $attlist, $entryMatches);
            foreach ($entryMatches[0] as $k => $dummy) {
                $element = $matches[1][0];
                $attributeValues = $entryMatches[1][$k];
                $required = $entryMatches[3][$k] === '#REQUIRED';
                $default = trim($entryMatches[4][$k], '\'"');
                $options = [];
                $type = Attribute::TYPE_STRING;

                // display   (yes|no|range)
                if (preg_match('/([^\(]*)(\([^\)]+\))/s', $attributeValues, $attributeValueMatch)) {
                    $attribute = $attributeValueMatch[1];
                    $options = explode('|', trim($attributeValueMatch[2], '()'));
                    $options = array_map('trim', $options);
                } // value CDATA
                elseif (preg_match('/(\S+)\s+CDATA/s', $attributeValues, $attributeValueMatch)) {
                    $attribute = $attributeValueMatch[1];
                    $type = Attribute::TYPE_CDATA;
                } // %ATT_TAX;
                elseif (preg_match(
                    '/(\S+)*\s*(' . self::ENTITY_PLACEHOLDER . ')/s',
                    $attributeValues,
                    $attributeValueMatch
                )) {
                    $entity = $this->findEntityByName(trim($attributeValueMatch[2], '%;'), ...$entities);
                    if ($entity === null) {
                        throw new \RuntimeException(
                            "Failed to assign entity {($attributeValueMatch[2]} to attlist $element"
                        );
                    }
                    $attribute = !empty(trim($entity->getAttribute() ?? ''))
                        ? $entity->getAttribute()
                        : $attributeValueMatch[1];

                    $options = $entity->getOptions();
                    if (empty(trim($attribute))) {
                        throw new \RuntimeException(
                            "Failed to find attribute name from {$attributeValues} of element $element"
                        );
                    }
                } else {
                    throw new \RuntimeException(
                        "Failed to parse ATTLIST entry {$attributeValues} of element $element"
                    );
                }

                if (!empty($options) && $this->isAllFloat($options)) {
                    $type = Attribute::TYPE_FLOAT;
                }

                if (!empty($options) && $this->isAllNumeric($options)) {
                    $type = Attribute::TYPE_INT;
                }

                $result[] = new Attribute(
                       $element,
                       $type,
                       $attribute,
                       $required,
                       $default,
                    ...$options
                );
            }
        }

        return $result;
    }

    private function findEntityByName(string $name, Entity ...$entities)
    {
        foreach ($entities as $entity) {
            if ($entity->getName() === $name) {
                return $entity;
            }
        }
        return null;
    }

    private function isAllNumeric(array $arr): bool {
        foreach ($arr as $v) {
            if (!is_numeric($v)) {
                return false;
            }
        }
        return true;
    }

    private function isAllFloat(array $arr): bool {
        foreach ($arr as $v) {
            if (!is_float($v)) {
                return false;
            }
        }
        return true;
    }

}

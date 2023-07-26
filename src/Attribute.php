<?php declare(strict_types=1);

namespace Kedomingo\DtdParser;

class Attribute
{
    public const TYPE_CDATA = 'cdata';
    public const TYPE_STRING = 'string';
    public const TYPE_INT = 'int';
    public const TYPE_FLOAT = 'float';

    private string $elementName;
    private string $type;
    private string $attribute;
    private bool $isRequired;

    /**
     * @var Element|string|int
     */
    private $default;

    /**
     * @var mixed[] [Element|string|int]
     */
    private array $options;

    public function __construct(
        string $elementName,
        string $type,
        string $attribute,
        bool $isRequired = false,
        $default = null,
        string ...$options
    ) {
        $this->elementName = $elementName;
        $this->type = $type;
        $this->attribute = $attribute;
        $this->isRequired = $isRequired;
        $this->default = $default;
        foreach ($options as $option) {
            $this->options[$option] = $option;
        }
    }

    public function getElementName(): string
    {
        return $this->elementName;
    }

    public function getAttribute(): ?string
    {
        return $this->attribute;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function isRequired(): bool
    {
        return $this->isRequired;
    }

    public function getDefault()
    {
        return $this->default;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @param Element|string|int $replacement
     * @return void
     */
    public function setOption(string $option, $replacement): void
    {
        $this->options[$option] = $replacement;
    }
}

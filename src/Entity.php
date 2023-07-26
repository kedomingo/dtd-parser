<?php declare(strict_types=1);

namespace Kedomingo\DtdParser;

class Entity
{
    private ?string $name;
    private ?string $attribute;
    private bool $isRequired;

    /**
     * @var Element|string|int
     */
    private $default;

    /**
     * @var mixed[] [Element|string|int]
     */
    private array $options;

    public function __construct(?string $name = null, ?string $attribute = null, bool $isRequired = false, $default = null, string ...$options)
    {
        $this->name = $name;
        $this->attribute = $attribute;
        $this->isRequired = $isRequired;
        $this->default = $default;
        foreach ($options as $option) {
            $this->options[$option] = $option;
        }
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getAttribute(): ?string
    {
        return $this->attribute;
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
    public function setOption(string $option, $replacement): void {
        $this->options[$option] = $replacement;
    }
}

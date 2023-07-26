<?php declare(strict_types=1);

namespace Kedomingo\DtdParser;

class Element
{
    private string $name;

    /**
     * @var Element[]
     */
    private array $children;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function addChild(Element $e): void
    {
        $this->children[$e->getName()] = $e;
    }

    public function getChildren(): array
    {
        return $this->children;
    }
}

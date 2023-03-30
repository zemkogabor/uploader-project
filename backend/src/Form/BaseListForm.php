<?php

declare(strict_types = 1);

namespace App\Form;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class BaseListForm
{
    protected static array $orderByColumnMap = [];

    #[Assert\NotNull(message: 'Current page should not be blank.')]
    #[Assert\Positive]
    public mixed $currentPage;

    #[Assert\NotNull(message: 'Page size should not be blank.')]
    #[Assert\Range(
        notInRangeMessage: 'Page size value should be between {{ min }} and {{ max }}.',
        min: 1,
        max: 1000
    )]
    public mixed $pageSize;

    #[Assert\NotBlank(message: 'Order should not be blank.')]
    public mixed $orderBy;

    #[Assert\NotNull(message: 'Sort should not be blank.')]
    #[Assert\Choice(choices: ['0','1'], message: 'Not valid sort direction.')]
    public mixed $sortDesc;

    public function getOffset(): int
    {
        return (int) ($this->currentPage - 1) * $this->pageSize;
    }

    public function getLimit(): int
    {
        return (int) $this->pageSize;
    }

    /**
     * @param ExecutionContextInterface $context
     * @return void
     */
    #[Assert\Callback]
    public function validateOrderBy(ExecutionContextInterface $context): void
    {
        if (!array_key_exists($this->orderBy, static::$orderByColumnMap)) {
            $context->buildViolation('Invalid order key.')->addViolation();
        }
    }

    /**
     * Get real order by db column.
     *
     * @return string|null
     */
    public function getOrderByColumn(): ?string
    {
        if ($this->orderBy === null) {
            return null;
        }

        return static::$orderByColumnMap[$this->orderBy];
    }

    /**
     * Return SQL sort direction by form.
     *
     * @return string
     */
    public function getSortDirection(): string
    {
        return (int) $this->sortDesc === 1 ? 'DESC' : 'ASC';
    }
}

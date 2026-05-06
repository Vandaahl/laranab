<?php declare(strict_types=1);

namespace App\DTO;

use ArrayIterator;
use Traversable;

final class ApiResponseItemCollection
{
    /**
     * @param ApiResponseItem[] $items
     */
    public function __construct(public array $items) {
        foreach ($items as $item) {
            if (!$item instanceof ApiResponseItem) {
                throw new \InvalidArgumentException(
                    'All items must be instances of ApiResponseItem'
                );
            }
        }
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }

    public function toArray(): array
    {
        return array_map(fn ($item) => $item->toArray(), $this->items);
    }

    public static function fromArray(array $data): self
    {
        return new self(
            array_map(fn ($item) => ApiResponseItem::fromArray($item), $data)
        );
    }
}

<?php declare(strict_types=1);

namespace App\DTO;

use Illuminate\Support\Collection;

/**
 * @extends Collection<int, NzbData>
 */
final class NzbCollection extends Collection
{
    /**
     * @param array<int, array<string, mixed>> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            array_map(
                fn (array $itemData): NzbData => NzbData::fromArray($itemData),
                $data
            )
        );
    }
}

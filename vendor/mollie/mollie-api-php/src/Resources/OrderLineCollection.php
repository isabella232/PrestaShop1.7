<?php

namespace _PhpScoper5ea00cc67502b\Mollie\Api\Resources;

class OrderLineCollection extends \_PhpScoper5ea00cc67502b\Mollie\Api\Resources\BaseCollection
{
    /**
     * @return string|null
     */
    public function getCollectionResourceName()
    {
        return null;
    }
    /**
     * Get a specific order line.
     * Returns null if the order line cannot be found.
     *
     * @param  string $lineId
     * @return OrderLine|null
     */
    public function get($lineId)
    {
        foreach ($this as $line) {
            if ($line->id === $lineId) {
                return $line;
            }
        }
        return null;
    }
}

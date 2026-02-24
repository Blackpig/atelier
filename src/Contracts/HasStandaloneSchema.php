<?php

namespace BlackpigCreatif\Atelier\Contracts;

interface HasStandaloneSchema
{
    /**
     * Whether this block generates its own standalone schema.org schema.
     */
    public function hasStandaloneSchema(): bool;

    /**
     * Return a complete schema.org schema array for this block.
     *
     * @return array<string, mixed>|null
     */
    public function toStandaloneSchema(): ?array;
}

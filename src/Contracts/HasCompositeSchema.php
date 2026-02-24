<?php

namespace BlackpigCreatif\Atelier\Contracts;

interface HasCompositeSchema
{
    /**
     * Whether this block contributes content to a composite schema (e.g. Article body).
     */
    public function contributesToComposite(): bool;

    /**
     * Return the data this block contributes to the composite schema.
     * Expected shape: ['type' => 'text'|'image'|..., ...type-specific keys]
     *
     * @return array<string, mixed>|null
     */
    public function getCompositeContribution(): ?array;
}

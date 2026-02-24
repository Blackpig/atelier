<?php

namespace BlackpigCreatif\Atelier\Contracts;

interface BlockSchemaDriverInterface
{
    /**
     * Resolve a schema.org structured data array for a given block.
     * Return null if this driver cannot or should not generate a schema for the block.
     */
    public function resolveSchema(HasSchemaContribution $block): ?array;
}

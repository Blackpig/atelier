<?php

namespace BlackpigCreatif\Atelier\Contracts;

interface HasSchemaContribution
{
    /**
     * Return the schema type this block represents.
     * Return null if this block does not declare a typed schema.
     *
     * Any backed enum is accepted — callers should match on $type->value.
     * When using Sceau, return a BlackpigCreatif\Sceau\Enums\SchemaType case.
     */
    public function getSchemaType(): ?\BackedEnum;

    /**
     * Return the data payload the driver uses to build the schema array.
     * The expected shape is SchemaType-specific.
     *
     * @return array<string, mixed>
     */
    public function getSchemaData(): array;
}

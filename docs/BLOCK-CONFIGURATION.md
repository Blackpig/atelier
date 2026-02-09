# Block Configuration

Atelier provides a cascading configuration system for customising block fields and schemas. Configuration can be applied at two levels:

1. **Global** -- via a service provider, applies to all resources
2. **Per-resource** -- via `BlockManager`, applies to a specific form

Within each level there are two modes:

- **Field configuration** -- tweak properties on existing fields (options, visibility, maxLength, etc.)
- **Schema modification** -- structurally add, remove, reorder, or replace fields

---

## Field Configuration

### Global (Service Provider)

```php
use BlackpigCreatif\Atelier\Support\BlockFieldConfig;
use BlackpigCreatif\Atelier\Blocks\HeroBlock;
use BlackpigCreatif\Atelier\Blocks\TextBlock;

// Single field
BlockFieldConfig::register(HeroBlock::class, 'ctas', ['maxItems' => 3]);

// Multiple fields
BlockFieldConfig::configure(TextBlock::class, [
    'title'    => ['maxLength' => 80, 'required' => true],
    'subtitle' => ['visible' => false],
    'columns'  => ['options' => ['1' => '1 Column', '2' => '2 Columns']],
]);

// Shorthand for select/radio options
BlockFieldConfig::setOptions(TextBlock::class, 'columns', [
    '1' => '1 Column',
    '2' => '2 Columns',
]);
```

### Per-Resource (BlockManager)

```php
use BlackpigCreatif\Atelier\Forms\Components\BlockManager;

// Single field
BlockManager::make('blocks')
    ->configureField(HeroBlock::class, 'ctas', ['maxItems' => 5])

// Multiple fields
BlockManager::make('blocks')
    ->configureBlock(HeroBlock::class, [
        'headline' => ['maxLength' => 60],
        'ctas'     => ['maxItems' => 5],
    ])
```

### Supported Field Properties

Any Filament field method can be called. Common ones:

| Property | Applies to | Example |
|----------|-----------|---------|
| `visible` | Any field | `['visible' => false]` |
| `disabled` | Any field | `['disabled' => true]` |
| `required` | Any field | `['required' => true]` |
| `default` | Any field | `['default' => 'center']` |
| `label` | Any field | `['label' => 'Heading']` |
| `maxLength` / `minLength` | TextInput | `['maxLength' => 120]` |
| `options` | Select / Radio | `['options' => [...]]` |
| `maxItems` / `minItems` | Repeater | `['maxItems' => 3]` |
| `maxFiles` | FileUpload | `['maxFiles' => 5]` |

---

## Schema Modification

Schema modifiers receive the full schema array and must return a modified array.

### Global

```php
// Remove fields
BlockFieldConfig::modifySchema(HeroBlock::class, function ($schema) {
    return BlockFieldConfig::removeFields($schema, ['text_color', 'overlay_opacity']);
});

// Add fields to the end
BlockFieldConfig::modifySchema(HeroBlock::class, function ($schema) {
    return [
        ...$schema,
        Toggle::make('featured')->label('Featured')->default(false),
    ];
});

// Add a section
BlockFieldConfig::modifySchema(HeroBlock::class, function ($schema) {
    return BlockFieldConfig::addFields($schema, [
        Section::make('SEO')
            ->schema([
                TextInput::make('meta_title')->maxLength(60),
                Textarea::make('meta_description')->maxLength(160),
            ])
            ->collapsible()
            ->collapsed(),
    ]);
});

// Insert relative to a field
BlockFieldConfig::modifySchema(HeroBlock::class, function ($schema) {
    return BlockFieldConfig::insertAfter($schema, 'headline', [
        TextInput::make('tagline')->maxLength(100),
    ]);
});
```

### Per-Resource

Pass a closure to `configureBlock()`:

```php
BlockManager::make('blocks')
    ->configureBlock(HeroBlock::class, function ($schema) {
        return BlockFieldConfig::removeFields($schema, 'subtitle');
    })
```

### Schema Helper Methods

All methods on `BlockFieldConfig`:

| Method | Description |
|--------|-------------|
| `removeFields($schema, $names)` | Remove field(s) recursively (searches nested Sections/Groups) |
| `addFields($schema, array $fields)` | Append fields to end |
| `prependFields($schema, array $fields)` | Insert fields at beginning |
| `insertBefore($schema, string $name, array $fields)` | Insert before a named field |
| `insertAfter($schema, string $name, array $fields)` | Insert after a named field |
| `moveField($schema, string $name, int $position)` | Move a field to a specific index |

---

## BlockConfigurator (Fluent API)

`BlockConfigurator` wraps `BlockFieldConfig` in a chainable interface. You must call `->apply()` to commit changes to the registry.

```php
use BlackpigCreatif\Atelier\Support\BlockConfigurator;

BlockConfigurator::for(HeroBlock::class)
    ->hide('overlay_opacity', 'text_color')   // visible => false
    ->show('subtitle')                        // visible => true
    ->remove('height')                        // removes from schema entirely
    ->require('headline')                     // required => true
    ->optional('subheadline')                 // required => false
    ->configure('ctas', ['maxItems' => 2])    // arbitrary field config
    ->insertBefore('headline', [
        TextInput::make('eyebrow')->maxLength(50),
    ])
    ->insertAfter('subheadline', [
        Toggle::make('show_cta')->default(false),
    ])
    ->addFields([
        TextInput::make('footer_text'),
    ])
    ->modifySchema(function ($schema) {
        // Full control over schema array
        return $schema;
    })
    ->apply();
```

Or use the static shorthand:

```php
BlockConfigurator::configureUsing(HeroBlock::class, function ($configurator) {
    $configurator
        ->hide('text_color')
        ->remove('overlay_opacity');
});
```

---

## Configuration Priority

```
1. Block Default Schema (base)
   |
2. Global Schema Modifiers (structure changes)
   |
3. Per-Resource Schema Modifiers (override global structure)
   |
4. Global Field Configs (field property tweaks)
   |
5. Per-Resource Field Configs (highest priority -- wins)
```

- Schema modifiers run before field configs.
- Per-resource always overrides global at the same level.
- You can combine both approaches freely.

---

## Examples

### Override select options globally, then per-resource

```php
// Global
BlockFieldConfig::setOptions(TextBlock::class, 'columns', [
    '1' => '1 Column',
    '2' => '2 Columns',
]);

// Blog resource -- use global defaults (no override needed)
BlockManager::make('blocks')->blocks([TextBlock::class])

// Marketing resource -- allow 3 columns
BlockManager::make('blocks')
    ->blocks([TextBlock::class])
    ->configureBlock(TextBlock::class, [
        'columns' => ['options' => ['1' => 'Single', '2' => 'Double', '3' => 'Triple']],
    ])
```

### Remove + configure + add in one resource

```php
BlockManager::make('blocks')
    ->blocks([HeroBlock::class])

    // Remove unwanted fields
    ->configureBlock(HeroBlock::class, fn ($s) =>
        BlockFieldConfig::removeFields($s, ['overlay_opacity', 'height'])
    )

    // Configure remaining fields
    ->configureBlock(HeroBlock::class, [
        'ctas' => ['maxItems' => 1],
    ])

    // Add a new field
    ->configureBlock(HeroBlock::class, fn ($s) => [
        ...$s,
        Toggle::make('featured')->label('Featured Content'),
    ])
```

---

## Testing

Clear the configuration registry between tests:

```php
use BlackpigCreatif\Atelier\Support\BlockFieldConfig;

beforeEach(function () {
    BlockFieldConfig::clear();
});
```

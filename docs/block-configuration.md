# Block Configuration

Atelier provides a fluent API for customising block fields and schemas without touching block source files. Configuration is registered globally (via a service provider) and applied whenever a block's form is built.

---

## BlockConfigurator (Fluent API)

`BlockConfigurator` is the primary way to configure blocks. Chain methods and call `->apply()` to commit everything to the registry.

### Basic example

```php
use BlackpigCreatif\Atelier\Support\BlockConfigurator;
use BlackpigCreatif\Atelier\Blocks\HeroBlock;

BlockConfigurator::for(HeroBlock::class)
    ->remove('overlay_opacity', 'text_color', 'height')  // remove fields entirely
    ->configure('background_image', ['attribution' => false])
    ->apply();
```

### Adding and inserting fields

`insertBefore` and `insertAfter` search the full schema recursively, including fields nested inside Sections.

```php
use BlackpigCreatif\Atelier\Support\BlockConfigurator;
use BlackpigCreatif\Atelier\Blocks\TextBlock;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;

BlockConfigurator::for(TextBlock::class)
    ->insertBefore('title', [
        TextInput::make('eyebrow')->maxLength(50),
    ])
    ->insertAfter('title', [
        TextInput::make('tagline')
            ->maxLength(100)
            ->translatable(),
    ])
    ->addFields([
        Toggle::make('show_author')->default(false),
    ])
    ->apply();
```

### Removing a Section

Use `removeSection()` to remove an entire named section by its heading:

```php
BlockConfigurator::for(TextBlock::class)
    ->removeSection('Call to Action')
    ->apply();
```

Multiple sections can be removed in a single call:

```php
BlockConfigurator::for(TextBlock::class)
    ->removeSection('Call to Action', 'Settings')
    ->apply();
```

### Full schema control with `modifySchema()`

For anything that doesn't fit the convenience methods — such as inserting a whole Section after another Section, or complex conditional logic — use `modifySchema()`. The closure receives the current schema array and must return the modified array.

```php
use Filament\Schemas\Components\Section;

BlockConfigurator::for(TextBlock::class)
    ->modifySchema(function (array $schema): array {
        $seoSection = Section::make('SEO')
            ->schema([
                TextInput::make('meta_title')->maxLength(60),
                TextInput::make('meta_description')->maxLength(160),
            ])
            ->collapsible()
            ->collapsed();

        // Insert after the Settings section
        foreach ($schema as $index => $component) {
            if ($component instanceof \Filament\Schemas\Components\Section
                && $component->getHeading() === 'Settings') {
                array_splice($schema, $index + 1, 0, [$seoSection]);

                return $schema;
            }
        }

        // Fallback: append at end
        return [...$schema, $seoSection];
    })
    ->apply();
```

`modifySchema()` can be chained multiple times — each closure receives the output of the previous one.

### Full method reference

| Method | Description |
|--------|-------------|
| `->remove(string ...$names)` | Remove field(s) from the schema entirely |
| `->removeSection(string ...$headings)` | Remove container Section(s) by heading label |
| `->hide(string ...$names)` | Set `visible => false` on field(s) |
| `->show(string ...$names)` | Set `visible => true` on field(s) |
| `->require(string ...$names)` | Set `required => true` on field(s) |
| `->optional(string ...$names)` | Set `required => false` on field(s) |
| `->configure(string $name, array $config)` | Set arbitrary field properties |
| `->insertBefore(string $name, array $fields)` | Insert fields before a named field (recursive) |
| `->insertAfter(string $name, array $fields)` | Insert fields after a named field (recursive) |
| `->addFields(array $fields)` | Append fields to the end of the schema |
| `->prependFields(array $fields)` | Prepend fields to the beginning of the schema |
| `->modifySchema(Closure $fn)` | Full schema control — receives and returns `array` |
| `->apply()` | Commit all configuration to the registry |

### Static shorthand

```php
BlockConfigurator::configureUsing(HeroBlock::class, function ($configurator) {
    $configurator
        ->hide('text_color')
        ->remove('overlay_opacity');
});
```

---

## Field Configuration

### Supported field properties

Pass any Filament field method name as an array key to `->configure()`:

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
| `attribution` | FileUpload | `['attribution' => false]` |

---

## Per-Resource Configuration

For one-off overrides scoped to a single resource, use `BlockManager`:

```php
use BlackpigCreatif\Atelier\Forms\Components\BlockManager;
use BlackpigCreatif\Atelier\Support\BlockFieldConfig;

// Field config override
BlockManager::make('blocks')
    ->configureBlock(HeroBlock::class, [
        'ctas' => ['maxItems' => 5],
    ])

// Schema override
BlockManager::make('blocks')
    ->configureBlock(HeroBlock::class, fn ($schema) =>
        BlockFieldConfig::removeFields($schema, 'subtitle')
    )
```

---

## Configuration Priority

```
1. Block default schema (base)
   |
2. Global schema modifiers (BlockConfigurator / BlockFieldConfig)
   |
3. Per-resource schema modifiers (BlockManager)
   |
4. Global field configs (BlockConfigurator / BlockFieldConfig)
   |
5. Per-resource field configs (BlockManager — highest priority)
```

Schema modifiers always run before field configs. Per-resource always overrides global at the same level.

---

## BlockFieldConfig (Low-Level API)

`BlockFieldConfig` is the static registry that `BlockConfigurator` writes to. You can call it directly for advanced cases or when you need the schema helpers standalone.

```php
use BlackpigCreatif\Atelier\Support\BlockFieldConfig;

// Register field config directly
BlockFieldConfig::register(HeroBlock::class, 'ctas', ['maxItems' => 3]);

// Schema helpers
BlockFieldConfig::modifySchema(HeroBlock::class, function ($schema) {
    return BlockFieldConfig::insertAfter($schema, 'headline', [
        TextInput::make('tagline')->maxLength(100),
    ]);
});
```

### Schema helper methods

| Method | Description |
|--------|-------------|
| `removeFields($schema, $names)` | Remove field(s) recursively |
| `removeSections($schema, $headings)` | Remove Section(s) by heading |
| `addFields($schema, array $fields)` | Append fields to end |
| `prependFields($schema, array $fields)` | Insert fields at beginning |
| `insertBefore($schema, string $name, array $fields)` | Insert before a named field (recursive) |
| `insertAfter($schema, string $name, array $fields)` | Insert after a named field (recursive) |
| `moveField($schema, string $name, int $position)` | Move a field to a specific index |

---

## Testing

Clear the registry between tests to prevent state leaking:

```php
use BlackpigCreatif\Atelier\Support\BlockFieldConfig;

beforeEach(function () {
    BlockFieldConfig::clear();
});
```

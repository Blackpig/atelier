# Schema Generation

Atelier provides a contract-based system for blocks to contribute Schema.org structured data. The package defines the contracts; a separate driver — configured in your application — handles construction of the final JSON-LD output. Atelier itself has no dependency on any SEO package.

## Contracts

`BaseBlock` implements all three contracts with no-op defaults. Override only the methods relevant to your block.

---

### `HasSchemaContribution`

Namespace: `BlackpigCreatif\Atelier\Contracts\HasSchemaContribution`

Used for blocks that produce a specific typed schema (FAQPage, VideoObject, etc.). The active driver receives the block's declared type and data, and is responsible for building the schema array.

```php
interface HasSchemaContribution
{
    public function getSchemaType(): ?\BackedEnum;

    /** @return array<string, mixed> */
    public function getSchemaData(): array;
}
```

`getSchemaType()` returns any backed enum value — it does not have to be from Sceau's `SchemaType` enum. The driver matches on `$type->value` (the string representation), so it works with any enum your application or SEO package defines. Return `null` to skip schema generation for this block.

`getSchemaData()` returns a plain array whose shape is a contract between your block and the driver. Document the expected shape with a PHPDoc `@return` tag.

**Example — FaqsBlock:**

```php
use BlackpigCreatif\Sceau\Enums\SchemaType;

public function getSchemaType(): ?SchemaType
{
    $faqs = array_filter(
        $this->get('faqs', []),
        fn (array $pair): bool => ! empty($pair['question']) && ! empty($pair['answer'])
    );

    return ! empty($faqs) ? SchemaType::FAQPage : null;
}

/**
 * @return array{faqs: array<int, array{question: string, answer: string}>}
 */
public function getSchemaData(): array
{
    return [
        'faqs' => array_values(array_filter(
            $this->get('faqs', []),
            fn (array $pair): bool => ! empty($pair['question']) && ! empty($pair['answer'])
        )),
    ];
}
```

---

### `HasCompositeSchema`

Namespace: `BlackpigCreatif\Atelier\Contracts\HasCompositeSchema`

Used for blocks that contribute content or media to a composite schema assembled from multiple blocks — typically an Article or BlogPosting schema.

```php
interface HasCompositeSchema
{
    public function contributesToComposite(): bool;

    /** @return array<string, mixed>|null */
    public function getCompositeContribution(): ?array;
}
```

The contribution array has a loose shape convention understood by `ArticleSchema::fromBlocks()`:

| Key | Type | Description |
|-----|------|-------------|
| `type` | `string` | Identifies the block type (informational) |
| `content` | `string\|null` | Plain text body (HTML stripped). Used for `articleBody`. |
| `url` | `string\|null` | Single image URL. Used for `image`. |
| `urls` | `string[]\|null` | Multiple image URLs. Used for `image`. |

Blocks may include any combination of these. A block may supply both `content` and `url`/`urls`.

**Example — TextWithImageBlock:**

```php
public function contributesToComposite(): bool
{
    return true;
}

/**
 * @return array{type: string, content: string, url: string|null}
 */
public function getCompositeContribution(): array
{
    return [
        'type'    => 'text_with_image',
        'content' => strip_tags($this->getTranslated('content') ?? ''),
        'url'     => $this->getMediaUrl('image', 'large'),
    ];
}
```

**Example — GalleryBlock (multiple images, no text):**

```php
public function contributesToComposite(): bool
{
    return true;
}

/**
 * @return array{type: string, urls: string[]}
 */
public function getCompositeContribution(): array
{
    return [
        'type' => 'gallery',
        'urls' => $this->getMediaUrls('images', 'large'),
    ];
}
```

---

### `HasStandaloneSchema`

Namespace: `BlackpigCreatif\Atelier\Contracts\HasStandaloneSchema`

A legacy escape hatch for blocks that construct the full schema array themselves. Prefer the driver pattern (`HasSchemaContribution`) for new blocks — it separates the data shape from the schema construction and makes the driver extensible.

```php
interface HasStandaloneSchema
{
    public function hasStandaloneSchema(): bool;

    /** @return array<string, mixed>|null */
    public function toStandaloneSchema(): ?array;
}
```

---

## Built-in Block Schema Contributions

| Block | Contract | Output |
|-------|----------|--------|
| `TextBlock` | `HasCompositeSchema` | `content` (plain text) |
| `TextWithImageBlock` | `HasCompositeSchema` | `content` + `url` (large image) |
| `TextWithTwoImagesBlock` | `HasCompositeSchema` | `content` + `urls` (two large images) |
| `GalleryBlock` | `HasCompositeSchema` | `urls` (all images at large size) |
| `CarouselBlock` | `HasCompositeSchema` | `urls` (all images at large size) |
| `VideoBlock` | `HasSchemaContribution` | `SchemaType::VideoObject` with `content_url`, `embed_url`, `name`, `description` |
| `FaqsBlock` | `HasSchemaContribution` | `SchemaType::FAQPage` with `faqs` array |
| `HeroBlock` | — | No schema contribution |
| `ImageBlock` | — | No schema contribution |

---

## The Driver

Atelier defines `BlockSchemaDriverInterface`:

```php
namespace BlackpigCreatif\Atelier\Contracts;

interface BlockSchemaDriverInterface
{
    public function resolveSchema(HasSchemaContribution $block): ?array;
}
```

The service provider resolves the driver from `config('atelier.schema_driver')` and registers it as a singleton bound to `BlockSchemaDriverInterface::class`. If `schema_driver` is `null` or the class does not exist, no binding is registered and schema generation via `HasSchemaContribution` is silently skipped.

```php
// config/atelier.php
'schema_driver' => \BlackpigCreatif\Sceau\Schema\Drivers\SceauBlockSchemaDriver::class,
```

---

## Writing a Custom Block with Schema

A block that generates a HowTo schema using a hypothetical SEO package:

```php
namespace App\Blocks;

use App\Enums\MySchemaType;
use BlackpigCreatif\Atelier\Abstracts\BaseBlock;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Illuminate\Contracts\View\View;

class HowToBlock extends BaseBlock
{
    public static function getLabel(): string
    {
        return 'How To';
    }

    public static function getSchema(): array
    {
        return [
            static::getPublishedField(),

            Section::make('Content')
                ->schema([
                    TextInput::make('name')
                        ->label('Title')
                        ->required()
                        ->translatable(),

                    Repeater::make('steps')
                        ->schema([
                            TextInput::make('name')->required(),
                            Textarea::make('text')->required()->rows(2),
                        ])
                        ->reorderable()
                        ->collapsible()
                        ->addActionLabel('Add Step')
                        ->defaultItems(1),
                ])
                ->collapsible(),

            ...static::getCommonOptionsSchema(),
        ];
    }

    public function render(): View
    {
        return view(static::getViewPath(), $this->getViewData());
    }

    public function getSchemaType(): ?MySchemaType
    {
        return ! empty($this->get('steps')) ? MySchemaType::HowTo : null;
    }

    /**
     * @return array{name: string, steps: array<int, array{name: string, text: string}>}
     */
    public function getSchemaData(): array
    {
        return [
            'name'  => $this->getTranslated('name') ?? '',
            'steps' => $this->get('steps', []),
        ];
    }
}
```

Add a matching case to your driver's `resolveSchema()` method and a `buildHowToSchema(array $data): ?array` handler.

---

## Writing a Custom Driver

Implement `BlockSchemaDriverInterface` if you are not using Sceau:

```php
namespace App\SEO;

use BlackpigCreatif\Atelier\Contracts\BlockSchemaDriverInterface;
use BlackpigCreatif\Atelier\Contracts\HasSchemaContribution;

class MySchemaDriver implements BlockSchemaDriverInterface
{
    public function resolveSchema(HasSchemaContribution $block): ?array
    {
        $type = $block->getSchemaType();

        if ($type === null) {
            return null;
        }

        return match ($type->value) {
            'HowTo'   => $this->buildHowTo($block->getSchemaData()),
            'FAQPage' => $this->buildFaqPage($block->getSchemaData()),
            default   => null,
        };
    }

    protected function buildHowTo(array $data): ?array
    {
        if (empty($data['steps'])) {
            return null;
        }

        return [
            '@context' => 'https://schema.org',
            '@type'    => 'HowTo',
            'name'     => $data['name'],
            'step'     => array_map(fn (array $step): array => [
                '@type' => 'HowToStep',
                'name'  => $step['name'],
                'text'  => $step['text'],
            ], $data['steps']),
        ];
    }

    // ...
}
```

Register it in your app config:

```php
// config/atelier.php
'schema_driver' => App\SEO\MySchemaDriver::class,
```

---

## Integration with Sceau

When Sceau is installed alongside Atelier, no manual controller calls are required. The `<x-sceau::head :model="$page" />` component automatically calls `PageSchemaBuilder::build($page)` for models that have `HasAtelierBlocks`. This:

1. Assembles an Article schema from all composite-contributing blocks
2. Passes each `HasSchemaContribution` block through the configured driver
3. Pushes resulting schemas onto the `SchemaStack` before the `<head>` renders

See [Sceau — Atelier Integration](../../sceau/docs/atelier-integration.md) for the full Sceau-side reference.

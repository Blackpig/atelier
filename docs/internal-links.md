# Internal Links

Atelier can enhance the RichEditor's link action with an internal page search, allowing editors to link to pages, posts, or any other model without manually constructing URLs.

When configured, the link modal presents a radio toggle between "Internal page" and "External website". Selecting "Internal page" reveals a searchable dropdown that queries your application's models via a callback you provide.

---

## Setup

Register the search callback on `AtelierPlugin` in your `PanelProvider`:

```php
use BlackpigCreatif\Atelier\AtelierPlugin;

AtelierPlugin::make()
    ->internalLinks(function (string $search): array {
        // Return an associative array: URL => label
    }),
```

The callback receives the user's search string and must return an associative array where keys are URLs and values are display labels. It is called on every keystroke in the search field (debounced by Filament's Select component).

---

## Examples

### Single model (Pages)

```php
use App\Models\Page;

AtelierPlugin::make()
    ->internalLinks(function (string $search): array {
        return Page::query()
            ->where('title', 'like', '%' . $search . '%')
            ->where('is_published', true)
            ->limit(10)
            ->get()
            ->mapWithKeys(fn (Page $page) => [
                route('pages.show', $page->slug) => $page->title,
            ])
            ->all();
    }),
```

### Multiple models (Pages + Blog Posts)

```php
use App\Models\Page;
use App\Models\BlogPost;

AtelierPlugin::make()
    ->internalLinks(function (string $search): array {
        $term = '%' . $search . '%';

        $pages = Page::query()
            ->where('title', 'like', $term)
            ->where('is_published', true)
            ->limit(5)
            ->get()
            ->mapWithKeys(fn (Page $page) => [
                route('pages.show', $page->slug) => '[Page] ' . $page->title,
            ]);

        $posts = BlogPost::query()
            ->where('title', 'like', $term)
            ->where('is_published', true)
            ->limit(5)
            ->get()
            ->mapWithKeys(fn (BlogPost $post) => [
                route('blog.show', $post->slug) => '[Blog] ' . $post->title,
            ]);

        return $pages->merge($posts)->all();
    }),
```

### Translatable models (JSON columns)

When your model stores titles as JSON (e.g. via Spatie Translatable), MySQL's JSON extract uses binary collation, making `LIKE` case-sensitive. Use `LOWER()` with `whereRaw` for case-insensitive search:

```php
use App\Models\Page;

AtelierPlugin::make()
    ->internalLinks(function (string $search): array {
        $term = '%' . mb_strtolower($search) . '%';

        return Page::query()
            ->whereRaw('LOWER(title->>"$.en") LIKE ?', [$term])
            ->orWhereRaw('LOWER(title->>"$.fr") LIKE ?', [$term])
            ->limit(10)
            ->get()
            ->mapWithKeys(fn (Page $page) => [
                route('pages.show', $page) => $page->getTranslation('title', app()->getLocale()),
            ])
            ->all();
    }),
```

### Fragment links (single-page sites)

For single-page sites using Atelier's scroll navigation, link to fragment IDs:

```php
use App\Models\Section;

AtelierPlugin::make()
    ->internalLinks(function (string $search): array {
        return Section::query()
            ->where('title', 'like', '%' . $search . '%')
            ->limit(10)
            ->get()
            ->mapWithKeys(fn (Section $section) => [
                '/#' . $section->fragment_id => $section->title,
            ])
            ->all();
    }),
```

---

## How It Works

When a search callback is registered, Atelier automatically attaches `InternalLinkPlugin` to every `RichEditor` instance in the block form modal. The plugin overrides Filament's default `link` action with one that includes the internal/external toggle.

The plugin is a standard Filament `RichContentPlugin` implementation. It:

1. Overrides the default `link` action by registering an action with the same name
2. Detects whether an existing link is internal or external when editing (URLs not starting with `http`, `mailto:`, or `tel:` are treated as internal)
3. Populates the correct field based on the link type
4. Reads the URL from the appropriate field (`internalPage` for internal, `url` for external) when saving

No JavaScript extensions or TipTap plugins are needed. The feature works entirely through Filament's action system.

---

## Architecture

```
AtelierPlugin::internalLinks($callback)
    -> stores the callback
    -> BlockFormModal reads it via AtelierPlugin::get()->getInternalLinksSearchCallback()
    -> attaches InternalLinkPlugin to all RichEditor instances in the block schema
    -> InternalLinkPlugin overrides the default 'link' action
```

The callback is registered once at the panel level and applied to all block forms automatically. There is no per-block or per-resource override for internal links.

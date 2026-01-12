# Changelog

All notable changes to `atelier` will be documented in this file.

## 2.0.0 - 2026-01-12

### Changed
- **BREAKING**: Removed Spatie Media Library dependency
- **BREAKING**: Integrated ChambreNoir for responsive image handling
- Updated all block templates for improved consistency and performance
- Reorganized documentation into `/docs` directory

### Added
- **Enhanced Spacing System**: Balanced mode (equal top/bottom) and Individual mode (separate control)
- **Block Dividers**: Decorative SVG dividers with 6 styles (wave, curve up/down, diagonal, triangle)
- **Block Identifiers**: Automatic CSS class generation (e.g., `atelier-hero-block`)
- **Complete Block Library**: 8 production-ready blocks
  - HeroBlock
  - TextBlock
  - TextWithImageBlock
  - TextWithTwoImagesBlock
  - ImageBlock (with lightbox support)
  - VideoBlock (YouTube/Vimeo auto-detection)
  - GalleryBlock (grid-based with lightbox)
  - CarouselBlock (slider with navigation)
- **HasDivider Trait**: Manage block dividers with `getDividerComponent()` and `getDividerToBackground()`
- **Responsive Image Conversions**: Auto-generate thumb, medium, and large sizes
- Comprehensive Block Template Guide documentation
- Dynamic component system for dividers using Laravel's `<x-dynamic-component>`

### Fixed
- Filament v4 namespace compatibility (moved to `Filament\Schemas\Components`)
- Observer file deletion logic to use Laravel Storage instead of Spatie Media
- Import statements for `Get` utility (now `Filament\Schemas\Components\Utilities\Get`)

## 1.0.0 - 2024-12-10

### Added
- Initial release
- Polymorphic block architecture with intelligent attribute storage
- First-class translation support with custom locale switcher
- Built-in Hero and Text with Two Images blocks
- Flexible trait system (HasCommonOptions, HasMedia, HasSpacing, HasWidth, HasBackground)
- Smart caching layer for improved performance
- FilamentPHP v4 integration with BlockManager component
- TranslatableContainer component for managing multilingual content
- Live preview functionality for blocks
- Comprehensive, production-ready blade templates
- Dark mode support
- Accessibility features (ARIA labels, semantic HTML)
- Responsive design with Tailwind CSS
- Publishable templates for designer customization
- Template guide for creating custom blocks

### Features
- Artisanal: Bespoke blocks, not templates
- Architectural: Clean, polymorphic database structure
- Translatable: First-class multi-language support
- Extensible: Traits and abstracts for rapid customization
- Performant: Smart caching and eager loading
- Live Preview: Preview blocks in modal before saving

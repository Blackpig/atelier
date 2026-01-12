# Contributing to Atelier

Thank you for considering contributing to Atelier! We appreciate your help in making this package better.

## Development Setup

1. Fork the repository
2. Clone your fork: `git clone https://github.com/your-username/atelier.git`
3. Install dependencies: `composer install`
4. Create a branch: `git checkout -b feature/your-feature-name`
5. Make your changes
6. Run tests: `composer test`
7. Commit your changes: `git commit -am 'Add some feature'`
8. Push to the branch: `git push origin feature/your-feature-name`
9. Submit a pull request

## Coding Standards

- Follow **PSR-12** coding standards
- Use **PHP 8.2+** type hints and features
- Write tests for new features
- Update documentation as needed
- Keep commits focused and atomic
- Add PHPDoc comments for all public methods

### Code Style

```bash
# Fix code style automatically
composer format

# Check code style
composer lint
```

## Testing

Run the test suite before submitting:

```bash
composer test
```

When adding new features:
- Write unit tests for new block types
- Test translation functionality
- Verify Filament v4 compatibility
- Test with different locale configurations

## Creating New Blocks

When contributing new built-in blocks:

1. Create the block class in `src/Blocks/`
2. Use appropriate traits (`HasCommonOptions`, `HasRetouchMedia`)
3. Create a blade template in `resources/views/blocks/`
4. Follow the template structure from existing blocks
5. Include comprehensive PHPDoc comments
6. Ensure responsive design (mobile-first)
7. Add accessibility features (ARIA labels, semantic HTML)
8. Support dark mode with Tailwind classes
9. Include block identifier and divider support
10. Update documentation with block description

### Example Block Contribution

```php
<?php

namespace BlackpigCreatif\Atelier\Blocks;

use BlackpigCreatif\Atelier\Abstracts\BaseBlock;
use BlackpigCreatif\Atelier\Concerns\HasCommonOptions;
// ... imports

class YourNewBlock extends BaseBlock
{
    use HasCommonOptions;

    // Implementation
}
```

## Documentation

When contributing:

- Update the [README.md](../README.md) if adding new features
- Add entries to [CHANGELOG.md](CHANGELOG.md)
- Update [BLOCK-TEMPLATE-GUIDE.md](BLOCK-TEMPLATE-GUIDE.md) for template changes
- Provide clear code examples in documentation

## Reporting Issues

Please use the GitHub issue tracker to report bugs or request features.

### Bug Reports Should Include:

- PHP version
- Laravel version
- FilamentPHP version
- Atelier version
- Steps to reproduce
- Expected vs actual behavior
- Error messages or stack traces
- Screenshots if applicable

### Feature Requests Should Include:

- Clear description of the feature
- Use case / problem it solves
- Example implementation (if applicable)
- Willingness to contribute the feature

## Pull Request Process

1. Ensure your code follows PSR-12 standards
2. Write or update tests as needed
3. Update the README.md with details of changes if needed
4. Update the CHANGELOG.md with your changes
5. Ensure all tests pass
6. Request review from a maintainer
7. Address any feedback promptly
8. The PR will be merged once you have sign-off

### PR Titles

Use conventional commit format:

- `feat: Add new carousel block`
- `fix: Resolve spacing calculation bug`
- `docs: Update block template guide`
- `refactor: Simplify divider rendering`
- `test: Add tests for translation system`

## Security Vulnerabilities

If you discover a security vulnerability, please send an email to security@blackpig-creatif.com instead of using the issue tracker.

## Code of Conduct

- Be respectful and constructive in all interactions
- Welcome newcomers and help them get started
- Focus on what is best for the community
- Show empathy towards other community members

## Questions?

Feel free to open a discussion on GitHub if you have questions about contributing.

Thank you for your contribution!

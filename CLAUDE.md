# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Development Commands

### Build & Watch Commands
```bash
# Install dependencies
npm install
composer install

# Build all assets for production
npm run build

# Development builds with hot reloading
npm run start:admin    # Admin dashboard development
npm run start:front    # Block/frontend development

# Separate builds
npm run build:admin    # Build admin assets only
npm run build:front    # Build block/frontend assets only
```

### Testing & Quality Assurance
```bash
# PHP unit tests (requires wp-env to be running)
npm run test:unit

# PHP linting and static analysis
composer run lint          # PHP CodeSniffer
composer run phpstan       # PHPStan static analysis

# JavaScript linting
npm run lint:js           # ESLint for JavaScript/TypeScript
npm run lint:css          # Stylelint for CSS
npm run format            # Format code with Prettier
```

### WordPress Environment
```bash
# WordPress environment management
npm run wp-env            # Standard WordPress environment
npm run wp-env:debug      # WordPress with Xdebug enabled

# Logs
npm run log:follow        # Follow debug.log in real-time
npm run log:clear         # Clear debug.log

# Internationalization
npm run make-pot          # Generate .pot translation file
npm run make-json         # Generate JSON translation files
```

### Production & Release
```bash
composer run build-production    # Production build with optimized dependencies
composer run make-zip           # Create distribution ZIP file
composer run release            # Full production build + ZIP creation
```

## Code Architecture

This is a modern WordPress donation plugin with a hybrid PHP/React architecture:

### Backend Architecture (PHP)
- **Namespace**: `IseardMedia\Kudos` (PSR-4 autoloaded from `includes/`)
- **Dependency Injection**: Symfony DI container with service providers
- **Entry Point**: `kudos-donations.php` → `includes/namespace.php` → `Kernel.php`
- **Structure**:
  - `Admin/` - WordPress admin interface classes
  - `Controller/` - HTTP request handlers
  - `Service/` - Business logic services
  - `Domain/` - Domain entities and value objects
  - `Provider/` - DI service providers
  - `Migrations/` - Database migration classes
  - `Enum/` - PHP enums for type safety

### Frontend Architecture (React/TypeScript)
- **Admin Dashboard**: React SPA in `src/admin/` (builds to `build/admin/`)
- **Block Editor**: Gutenberg block in `src/block/` (builds to `build/front/`)
- **Technologies**: React 18, TypeScript, Tailwind CSS, Headless UI
- **Build System**: WordPress Scripts (webpack-based)

### Key Integrations
- **Payment Provider**: Mollie API (namespaced to avoid conflicts)
- **Background Jobs**: WordPress Action Scheduler
- **Logging**: Monolog (namespaced)
- **Templates**: Twig templating engine
- **PDF Generation**: DomPDF for receipts

### Development Environment
- **WordPress Environment**: Uses `@wordpress/env` (wp-env)
- **PHP Version**: 7.4+ (configured in composer.json platform)
- **Node Version**: Specified in `.nvmrc`
- **Hot Reloading**: Available for both admin and frontend development

### Third-Party Dependencies
Dependencies are scoped using PHP-Scoper to prevent conflicts:
- Scoped dependencies go to `third-party/vendor/` 
- Original vendor dependencies are removed post-install
- Custom autoloader modifications ensure proper namespacing

### Database & Storage
- **Storage Directory**: WordPress uploads directory + `/kudos-donations/`
- **Cache Directory**: WP content directory + `/cache/kudos-donations/`
- **Database Migrations**: Version-based migration system in `Migrations/`

## Testing Strategy
- Unit tests use PHPUnit with WordPress test stubs
- Tests are run inside wp-env container for WordPress integration
- Frontend components can be tested with WordPress Scripts testing utilities
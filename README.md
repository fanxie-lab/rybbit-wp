# Rybbit Analytics WordPress Plugin

Privacy-friendly, cookieless analytics for WordPress. Seamless integration with WooCommerce, Gutenberg blocks, and powerful tracking exclusions.

## Features

- **One-Click Setup**: Configure tracking in under 60 seconds
- **Privacy-Focused**: Cookieless tracking that respects user privacy
- **WooCommerce Integration**: Automatic ecommerce event tracking
- **Gutenberg Support**: Add custom events to button blocks
- **Flexible Exclusions**: Exclude pages/posts with patterns or individual selection
- **Modern Admin UI**: Vue.js-powered interface with intuitive controls

## Requirements

- WordPress 6.0 or higher
- PHP 7.4 or higher
- Node.js 18.0 or higher (for development)
- npm 9.0 or higher (for development)

## Installation

### For Users

1. Download the latest release from WordPress.org or GitHub
2. Upload the plugin to your WordPress site via Plugins > Add New > Upload
3. Activate the plugin
4. Go to Rybbit Analytics in the WordPress admin menu
5. Enter your Site ID from your Rybbit dashboard
6. Start tracking!

### For Developers

1. Clone this repository:
   ```bash
   git clone https://github.com/rybbit/rybbit-wp.git
   cd rybbit-wp
   ```

2. Install PHP dependencies (optional, for development tools):
   ```bash
   composer install
   ```

3. Install Node.js dependencies:
   ```bash
   npm install
   ```

4. Build the assets:
   ```bash
   npm run build
   ```

5. Symlink or copy the plugin to your WordPress plugins directory:
   ```bash
   ln -s $(pwd) /path/to/wordpress/wp-content/plugins/rybbit-analytics
   ```

6. Activate the plugin in WordPress admin

### Using wp-env (Recommended for Development)

For the quickest local development setup, use wp-env to spin up a Docker-based WordPress environment:

1. Ensure Docker is installed and running on your machine

2. Install dependencies:
   ```bash
   npm install
   composer install
   ```

3. Start the WordPress environment:
   ```bash
   npm run wp:start
   ```

4. Access your local WordPress site:
   - WordPress: http://localhost:8888
   - Admin: http://localhost:8888/wp-admin
   - Username: `admin`
   - Password: `password`

5. The plugin is automatically installed and available for activation

6. Build assets in watch mode:
   ```bash
   npm run dev
   ```

7. When done, stop the environment:
   ```bash
   npm run wp:stop
   ```

**wp-env Features:**
- WordPress 6.4 with PHP 8.1
- WooCommerce pre-installed for testing ecommerce features
- Debug mode enabled for development
- Automatic plugin mounting

## Development

### Project Structure

```
rybbit-analytics/
├── rybbit-analytics.php       # Main plugin file
├── includes/                  # PHP classes
│   ├── class-plugin.php       # Main plugin class
│   ├── class-settings-manager.php
│   ├── class-rest-controller.php
│   ├── class-script-injector.php
│   ├── class-woocommerce-handler.php
│   └── class-block-extender.php
├── src/                       # Source files (pre-build)
│   ├── admin/                 # Vue.js admin app
│   ├── blocks/                # Gutenberg extensions
│   └── styles/                # CSS source
├── assets/                    # Built assets (generated)
├── tests/                     # Test files
└── languages/                 # Translation files
```

### Development Commands

```bash
# Start development server with hot module replacement
npm run dev

# Build for production
npm run build

# Run tests
npm run test

# Run linting
npm run lint

# Run linting with auto-fix
npm run lint:fix

# Format code
npm run format

# wp-env commands (local WordPress environment)
npm run wp:start    # Start WordPress environment
npm run wp:stop     # Stop WordPress environment
npm run wp:destroy  # Remove WordPress environment
npm run wp:clean    # Clean all wp-env data
npm run wp:logs     # View WordPress logs

# Run PHP tests
composer test

# Run PHP code sniffer
composer phpcs

# Fix PHP code style issues
composer phpcbf
```

### Development Workflow

1. **Start development server**:
   ```bash
   npm run dev
   ```
   This starts Vite in development mode with HMR (Hot Module Replacement).

2. **Make your changes** in the `src/` directory

3. **Test your changes** in a local WordPress installation

4. **Build for production**:
   ```bash
   npm run build
   ```

5. **Test the production build** before committing

### Coding Standards

- **PHP**: Follow WordPress Coding Standards (WPCS)
- **JavaScript/Vue**: ESLint with Vue plugin
- **CSS**: Tailwind CSS with custom configuration

### Testing

#### PHP Tests
```bash
# Run all PHP tests
composer test

# Run specific test file
vendor/bin/phpunit tests/phpunit/test-settings-manager.php
```

#### JavaScript Tests
```bash
# Run all JavaScript tests
npm run test

# Run tests in watch mode
npm run test:watch

# Run tests with coverage
npm run test:coverage

# Open test UI
npm run test:ui
```

## Configuration

### WordPress Constants

You can define these constants in `wp-config.php` for advanced configuration:

```php
// Enable debug mode for Rybbit Analytics
define('RYBBIT_DEBUG', true);

// Custom script URL for self-hosted instances
define('RYBBIT_SCRIPT_URL', 'https://your-domain.com/api/script.js');
```

### Filter Hooks

```php
// Modify plugin settings before save
add_filter('rybbit_settings_before_save', function($settings) {
    // Modify settings here
    return $settings;
});

// Modify script injection output
add_filter('rybbit_script_output', function($script_html) {
    // Modify script HTML
    return $script_html;
});
```

### Action Hooks

```php
// Run code after settings are saved
add_action('rybbit_settings_saved', function($settings) {
    // Your code here
});

// Run code when WooCommerce event is tracked
add_action('rybbit_woocommerce_event_tracked', function($event_name, $event_data) {
    // Your code here
}, 10, 2);
```

## Deployment

### Building for Release

1. Ensure all dependencies are installed:
   ```bash
   npm install
   composer install --no-dev --optimize-autoloader
   ```

2. Build production assets:
   ```bash
   npm run build
   ```

3. Create a release package:
   ```bash
   # Create a clean copy without development files
   rsync -av --exclude-from='.gitignore' ./ ../rybbit-analytics-release/
   cd ../rybbit-analytics-release/

   # Remove development files
   rm -rf node_modules src tests .git .github
   rm package.json composer.json vite.config.js tailwind.config.js

   # Create zip file
   zip -r rybbit-analytics-1.0.0.zip .
   ```

### WordPress.org Submission

Follow the WordPress.org plugin submission guidelines:
https://developer.wordpress.org/plugins/wordpress-org/detailed-plugin-guidelines/

## Documentation

- **User Guide**: See `/docs/user-guide.md`
- **Developer Guide**: See `/docs/developer-guide.md`
- **Hooks & Filters**: See `/docs/hooks-filters.md`
- **Architecture**: See `/docs/architecture.md`

## Support

- **Documentation**: https://rybbit.com/docs/wordpress
- **Issues**: https://github.com/rybbit/rybbit-wp/issues
- **WordPress.org Support**: https://wordpress.org/support/plugin/rybbit-analytics/

## Contributing

Contributions are welcome! Please read our contributing guidelines before submitting pull requests.

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Make your changes
4. Run tests and linting
5. Commit your changes (`git commit -m 'Add amazing feature'`)
6. Push to the branch (`git push origin feature/amazing-feature`)
7. Open a Pull Request

## License

This plugin is licensed under the GPL v2 or later.

```
Copyright (C) 2025 Rybbit

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see https://www.gnu.org/licenses/gpl-2.0.html.
```

## Changelog

### 1.0.0 - 2025-XX-XX
- Initial release
- Core plugin functionality
- WooCommerce integration
- Gutenberg block extensions
- Vue.js admin interface

## Credits

Built with:
- [Vue.js 3](https://vuejs.org/)
- [Tailwind CSS](https://tailwindcss.com/)
- [Vite](https://vitejs.dev/)
- [Pinia](https://pinia.vuejs.org/)

## Roadmap

### V1.1
- Self-hosted Rybbit instance UI
- Additional button block plugin support
- Event template library expansion
- Advanced pattern testing tools

### V2.0
- Built-in analytics dashboard
- Custom dimension management
- Advanced funnel configuration
- A/B test experiment tracking
- Automated reporting and alerts

---

Made with ❤️ by [Rybbit](https://rybbit.com)

Code style/conventions observed:
- .editorconfig enforces UTF-8, LF endings, spaces, indent size 4, final newline, trimmed trailing whitespace.
- YAML files use 2-space indentation.
- Typical Laravel project conventions are expected:
  - PSR-4 autoload namespaces: App\\, Database\\Factories\\, Database\\Seeders\\.
  - Route definitions in routes/*.php with Laravel facades/helpers.
  - Tests organized under tests/Unit and tests/Feature.
- Use Laravel Pint for PHP formatting and keep framework-default project structure and naming conventions.

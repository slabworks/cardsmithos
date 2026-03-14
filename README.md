# Cardsmith OS

Cardsmith OS is an open-source content and operations platform for trading card repair shops.
It is built with Laravel, Inertia, React, and Tailwind to support modern workflows around submissions, job tracking, and shop management.

## Table of Contents

- [Features](#features)
- [Tech Stack](#tech-stack)
- [Getting Started](#getting-started)
- [Development](#development)
- [Testing and Quality](#testing-and-quality)
- [Contributing](#contributing)
- [Support](#support)
- [Authors](#authors)
- [License](#license)

## Features

- Laravel 12 backend with Fortify authentication
- Inertia.js + React frontend with TypeScript
- Tailwind CSS v4 based UI system
- Wayfinder-based typed route helpers
- Pest test suite and lint/format checks

## Tech Stack

### Backend

- PHP 8.5+
- Laravel 12
- Laravel Fortify
- Inertia Laravel

### Frontend

- React 19
- TypeScript
- Inertia React
- Tailwind CSS v4
- Vite 7

### Tooling

- Pest 4 + PHPUnit 12
- Laravel Pint
- ESLint 9
- Prettier 3

## Getting Started

### Prerequisites

- PHP 8.5+
- Composer
- Node.js 20+ and npm
- A local database supported by Laravel

### Installation

```bash
git clone https://github.com/slabworks/cardsmithos.git
cd cardsmithos
composer setup
```

The `composer setup` script installs PHP and JS dependencies, creates `.env`, generates the app key, runs migrations, and builds frontend assets.

## Development

Start the local development processes:

```bash
composer run dev
```

This runs the Laravel server, queue worker, log stream, and Vite dev server together.

To run only the frontend dev server:

```bash
npm run dev
```

## Testing and Quality

Run the default quality gates:

```bash
composer test
```

Run checks individually:

```bash
php artisan test --compact
npm run lint:check
npm run format:check
npm run types:check
vendor/bin/pint
```

Run all checks:

```bash
composer ci:check
```

## Contributing

Contributions are welcome and appreciated.

1. Fork the repository.
2. Create a feature branch from `main`.
3. Make your changes with clear commit messages.
4. Add or update tests for any behavior change.
5. Run quality checks locally (`composer test`).
6. Open a pull request with context and screenshots (if UI-related).

### Contribution Guidelines

- Follow existing code style and project structure.
- Keep pull requests focused and small when possible.
- Document notable behavior changes in your PR description.

## Support

If you'd like to support the project, you can do so here: [Support on Patreon](https://www.patreon.com/c/CardSmithOS)

## License

This project is open source and licensed under the [MIT License](LICENSE).

## Contributors

Thanks to all our contributors!  

[![](https://contrib.rocks/image?repo=slabworks/cardsmithos)](https://github.com/slabworks/cardsmithos/graphs/contributors)

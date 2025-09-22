### Laravel ^12 and SolidJS ^1.9 Starter Kit

_Note: This is a **very basic** starting point, no authentication is included._

This gets you started with Laravel ^12, SolidJS ^1.9, and TailwindCSS ^4.

### Installation

1. Clone the repository:

```zsh
git clone git@github.com:jhavenz/laravel-solid-tmpl.git
```

2. Jump into the project root:

```zsh
cd /path/to/your-repo
```

3. Install the dependencies:

```zsh
composer update && npm install
```

4. Copy the `.env.example` file to `.env`:

```zsh
cp .env.example .env
```

5. Generate the application key:

```zsh
php artisan key:generate
```

6. Run the migrations:

```zsh

# (Optional) Create the database
touch database/database.sqlite

php artisan migrate
# or
php artisan migrate:fresh --seed
```

7. Start the development services:

```zsh
composer dev
```

### Contributing

Contributions are welcome.

If you see potential improvements, please let me know or open a PR/issue.

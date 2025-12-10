# Mediko Healthcare System - Setup Guide

Welcome to the Mediko Healthcare System project! This guide will help you set up your development environment and get started with the project.

## 🚀 Prerequisites

- PHP 7.4 or higher
- Composer (PHP package manager)
- Node.js 16.x or higher (includes npm)
- MySQL 5.7+ or MariaDB 10.3+
- Git

## 🛠️ Development Environment Setup

### 1. Clone the Repository

```bash
git clone [repository-url]
cd Mediko
```

### 2. Install PHP Dependencies

```bash
composer install
```

### 3. Install Node.js Dependencies

```bash
npm install
```

### 4. Environment Configuration

1. Copy the example environment file:

   ```bash
   cp .env.example .env
   ```

2. Update the `.env` file with your database credentials and other settings:

   ```env
   APP_ENV=local
   APP_DEBUG=true
   APP_URL=http://localhost:8000

   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=mediko
   DB_USERNAME=your_db_username
   DB_PASSWORD=your_db_password
   ```

### 5. Generate Application Key

```bash
php -r "file_put_contents('.env', str_replace('APP_KEY=', 'APP_KEY='.bin2hex(random_bytes(32)).'\n', file_get_contents('.env')));"
```

### 6. Database Setup

1. Create a new database:

   ```sql
   CREATE DATABASE mediko;
   ```

2. Run migrations:

   ```bash
   php artisan migrate
   ```

3. (Optional) Seed the database with test data:
   ```bash
   php artisan db:seed
   ```

## 🚦 Running the Application

### Start the Development Servers

1. **Frontend Development Server** (Vite):

   ```bash
   npm run dev
   ```

   - Runs on: http://localhost:3000
   - Features hot module replacement (HMR)

2. **Backend Development Server** (PHP):
   ```bash
   php -S localhost:8000 -t public
   ```
   - Runs on: http://localhost:8000

### Building for Production

When you're ready to deploy:

```bash
# Build assets for production
npm run build

# Optimize the application
php artisan optimize
```

## 🛠 Development Workflow

### Code Style

We follow PSR-12 coding standards. Before committing, run:

```bash
composer check-style  # Check for style issues
composer fix-style   # Automatically fix style issues
```

### Git Workflow

1. Create a new branch for your feature:

   ```bash
   git checkout -b feature/your-feature-name
   ```

2. Commit your changes with a descriptive message:

   ```bash
   git add .
   git commit -m "Add your feature description"
   ```

3. Push your branch and create a pull request

## 📂 Project Structure

```
Mediko/
├── app/                  # Application code
│   ├── Controllers/      # Controller classes
│   ├── Models/           # Database models
│   ├── Views/            # View templates
│   └── ...
├── assets/               # Frontend assets
│   ├── js/               # JavaScript files
│   └── css/              # CSS files
├── config/               # Configuration files
├── database/             # Database migrations and seeders
├── public/               # Publicly accessible files
├── resources/            # Frontend resources
└── tests/                # Test files
```

## 🔒 Environment Variables

| Variable  | Description                  | Default               |
| --------- | ---------------------------- | --------------------- |
| APP_ENV   | Application environment      | local                 |
| APP_DEBUG | Debug mode                   | true                  |
| APP_URL   | Application URL              | http://localhost:8000 |
| DB\_\*    | Database connection settings |                       |

## 🧪 Testing

Run the test suite with:

```bash
composer test
```

## 🆘 Getting Help

If you run into any issues during setup:

1. Check the [Troubleshooting](#troubleshooting) section below
2. Search the project's issue tracker
3. Ask for help in the team's communication channel

## 🔧 Troubleshooting

### Common Issues

#### Vite Dev Server Not Starting

- Ensure no other process is using port 3000
- Try deleting `node_modules` and running `npm install` again

#### Database Connection Issues

- Verify your database credentials in `.env`
- Ensure the database server is running
- Check if the database user has proper permissions

#### PHP Version Mismatch

- Ensure you have PHP 7.4 or higher installed
- Verify the PHP version in your terminal: `php -v`

## 📝 License

This project is proprietary and confidential.

---

Happy coding! 🚀

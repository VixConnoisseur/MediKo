# MediKo - Personal Health Medication and Reminder System

A web-based personal health medication and reminder system built with PHP, Tailwind CSS, and JavaScript.

## Features

- User authentication (login/register)
- Role-based access control (Admin/User)
- Medication management
- Health journal
- Reminder system (Email/SMS)
- Emergency contacts
- Admin dashboard
- Responsive design

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Composer
- Node.js and npm
- Web server (Apache/Nginx)

## Installation

1. **Clone the repository**

   ```bash
   git clone https://github.com/yourusername/mediko.git
   cd mediko
   ```

2. **Install PHP dependencies**

   ```bash
   composer install
   ```

3. **Install Node.js dependencies**

   ```bash
   npm install
   ```

4. **Build assets**

   ```bash
   npm run build
   ```

5. **Configure environment**

   - Copy `.env.example` to `.env`
   - Update database credentials and other settings in `.env`

6. **Generate application key**

   ```bash
   php -r "file_put_contents('.env', str_replace('your_app_key_here', bin2hex(random_bytes(32)), file_get_contents('.env')));"
   ```

7. **Set up the database**

   - Create a new MySQL database
   - Import the database schema from `database/schema.sql`

8. **Set up cron job for reminders** (optional)
   ```bash
   * * * * * cd /path-to-your-project && php cron/check_reminders.php >/dev/null 2>&1
   ```

## First-time Admin Setup

1. Run the admin setup script:
   ```bash
   php scripts/setup_admin.php
   ```
2. Log in with the default admin credentials:
   - Email: admin@mediko.com
   - Password: ChangeThisPassword123!

## Development

- Watch for CSS/JS changes:

  ```bash
  npm run dev
  ```

- Build for production:
  ```bash
  npm run build
  ```

## Security

- Change default admin credentials after first login
- Keep your `.env` file secure
- Regularly update dependencies

## License

This project is open-source and available under the [MIT License](LICENSE).

# InfinityFree database setup

SchoolTrak now targets the database shown in the hosting control panel:

- Host: `sql306.infinityfree.com`
- Port: `3306`
- Database: `if0_43301389_schooltrak`
- Username: `if0_43301389`
- Password: configured privately in the Git-ignored `config.local.php`. Verify it against the control panel if authentication fails.

## Deploy

1. Upload the application files to the domain's `htdocs` directory in InfinityFree, including `app/`, `modules/`, `assets/`, `database/schema.sql`, `config.php`, and your private `config.local.php`. After cloning from GitHub, copy `config.example.php` to `config.local.php` and supply your credentials; GitHub does not contain that private file.
2. Open `https://schooltrak.infinityfree.me/install.php` to create the first administrator and initialize the tables in the existing database. Setup does not issue `CREATE DATABASE`.
3. Alternatively import `database/schema.sql` through the hosting account's phpMyAdmin before running setup.
4. Open the hosted `index.php` and sign in. The Create account link creates staff accounts.

The connection has not been verified from the hosting server. InfinityFree free hosting does not allow connections to this MySQL server from a local XAMPP installation. Local login and registration forms can render, but database operations must run on the hosting account.

To use a separate local database instead, override `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, and `DB_PASS` with local values. This does not repair the previous XAMPP InnoDB corruption.

Do not upload `tests/`, `docs/`, or database backups. Keep the database password out of public repositories.

Provider reference: https://forum.infinityfree.com/t/connecting-to-mysql-from-an-external-application/49339

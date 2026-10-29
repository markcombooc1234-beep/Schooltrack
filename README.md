# SchoolTrak

Modular PHP 8.1+ / MySQL (or MariaDB) school inventory system. No Composer or Node build is required.

Database credentials are loaded from the Git-ignored `config.local.php` or environment variables. After cloning, copy `config.example.php` to `config.local.php` and fill in your details. See [hosted setup instructions](docs/HOSTING.md). Without local settings, the application defaults to XAMPP on port 3307.

## Run on XAMPP

1. Start Apache and MySQL in the XAMPP Control Panel.
2. For local use, override the hosted settings in `config.php`: use host `127.0.0.1`, port `3307`, database `schooltrak`, and your local username/password. Environment variables `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS` can override them.
3. Open http://localhost/SchoolBase/install.php and create your administrator account. Setup creates the database, tables, and four starter categories; it locks once a user exists.
4. Sign in at http://localhost/SchoolBase/. Add suppliers and inventory, then create custodian, teacher, or staff accounts under User accounts.

Use HTTPS and dedicated database credentials for an externally hosted installation. PHP PDO MySQL must be enabled. All tables use InnoDB.

If setup reports a database connection error, update `config.php` with an account supported by the running MySQL server. The bundled older XAMPP MySQL command-line client may report a `caching_sha2_password` plugin error against newer servers; use a compatible server/client and PHP driver or a compatible dedicated database account.

Setup refreshes do not connect to MySQL; only a valid setup submission does. If MySQL stops after a query, see [the local shutdown diagnosis](docs/DATABASE-ISSUE.md). The observed InnoDB storage corruption requires server recovery; changing PHP code cannot repair database files.

## Modules and permissions

Each feature has its own folder in `modules/`, with a `view.php` and feature action files. Shared connection, session, validation, and transaction helpers live in `app/core/` and are loaded by `app/bootstrap.php`. `app/actions.php` dispatches POST commands; shared forms and layout live in `app/views/`. See [the code structure guide](docs/STRUCTURE.md) for the folder map and request flow.

Administrators and custodians manage items, categories, suppliers, all borrowing records, and activity logs. Administrators also create users. Teachers and staff browse inventory, borrow under their own accounts, return their own loans, export their own borrowing reports, and edit their profiles. Inventory reports are available to all signed-in users.

The login page includes **Create account** for public staff registration and a separate link for first-time administrator setup. Registration requires matching passwords of at least 10 characters, fixes the role to `staff` on the server, and commits the account and audit log together. Administrators assign privileged roles through User accounts. Login and registration screens render without a database connection; submitting valid credentials or registration requires a working database.

Dashboard includes quantities, low-stock alerts, and overdue loans. Inventory supports item/category/location search. Reports export CSV (with spreadsheet formula protection), and the report page supports browser printing/PDF.

## Sessions and transactions

Sessions use HttpOnly / SameSite cookies, strict mode, login session ID rotation, and a 30-minute idle timeout. Passwords use PHP password hashing. Every POST requires a CSRF token. Repeated failed logins are limited per email/IP pair.

At least three explicit transaction workflows use `beginTransaction()`, `commit()`, and `rollBack()`:

1. **Borrow:** lock item row with `FOR UPDATE`, verify stock, deduct available quantity, insert loan, and write activity log.
2. **Return:** lock loan row, verify ownership and that it has not already been returned, restore stock, mark returned, and write activity log.
3. **Inventory management:** create/update/delete inventory records and write their activity log atomically. Category, supplier, and account creation operations also use this transaction boundary.
4. **Profile:** update account details and optional password together with the activity log. A wrong current password rolls back even the name/email change.

Commit happens only after the entire operation succeeds. Any exception automatically rolls back; there is no partial save. Borrowed stock is separate from the available item quantity. Items with loan history cannot be deleted, and database foreign keys prevent deleting referenced categories/suppliers.

### Manual acceptance checks

- Create a test item with quantity 5; borrow 2; verify available quantity becomes 3 and a loan/log exists.
- Attempt to borrow 4; verify quantity stays 3 and no loan/log is inserted.
- Return the loan; verify quantity becomes 5. A repeated return must leave it at 5.
- Change profile name and enter a wrong current password with a new password; verify the name/password both stay unchanged.
- Sign in as staff and verify management actions and other users' loan returns are denied server-side.
- Export both CSV reports and verify their content. Sign out and verify protected pages redirect to login.

The app starts with an empty inventory, so dashboard and lists reflect your own data.

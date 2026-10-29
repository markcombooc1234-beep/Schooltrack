# SchoolTrak code structure

The application uses plain PHP with feature folders. Existing URLs (`index.php?page=inventory`, for example) are unchanged.

```text
SchoolBase/
├── index.php                  # Page entry point and access checks
├── install.php                # First administrator setup
├── config.php                 # Database host, port, and credentials
├── app/
│   ├── bootstrap.php          # Loads shared initialization
│   ├── actions.php            # POST dispatcher and shared error handling
│   ├── pages.php              # Allowed page names and navigation titles
│   ├── router.php             # Page routing and access checks
│   ├── core/
│   │   ├── session.php        # Cookie settings and idle timeout
│   │   ├── database.php       # PDO, prepared queries, commit/rollback
│   │   ├── auth.php           # Current user and role checks
│   │   ├── audit.php          # Activity log writer
│   │   ├── helpers.php        # Escaping, CSRF fields, redirects, messages
│   │   └── validation.php     # Required text and integer validation
│   └── views/
│       ├── forms.php          # Reusable form controls
│       └── layout.php         # Shared page shell and navigation
├── modules/
│   ├── auth/                  # Login view, login/logout actions
│   ├── dashboard/             # Inventory statistics and alerts
│   ├── inventory/             # Item view and CRUD actions
│   ├── categories/            # Category view and CRUD actions
│   ├── borrowing/             # History view, borrow and return commands
│   ├── suppliers/             # Supplier view and CRUD actions
│   ├── reports/               # Report view and CSV export
│   ├── profile/               # Profile view and update command
│   ├── logs/                  # Activity log view
│   └── users/                 # Account view and creation command
├── assets/style.css           # Shared responsive styling
├── database/
│   ├── schema.sql             # Tables, foreign keys, InnoDB definitions
│   └── create.php             # CLI-only database creation
└── docs/STRUCTURE.md           # This guide
```

## Request flow

1. `index.php` loads `app/bootstrap.php` to initialize sessions and shared functions.
2. POST requests pass through CSRF verification in `app/actions.php`.
3. The dispatcher invokes an explicitly mapped feature action. Management actions require an administrator or custodian; account creation additionally requires an administrator.
4. Borrowing, returns, and profile updates own their transaction boundaries. The dispatcher wraps management actions and their activity logs in a shared transaction.
5. GET requests pass page access checks, then the shared layout loads the feature's `view.php`. CSV requests use `modules/reports/export.php` before HTML output.

## Where to make changes

- Change a feature's forms, tables, and read queries in its `view.php`.
- Change feature writes in its `actions.php` (or `borrow.php` / `return.php`).
- Keep multi-step writes and their audit entries inside their existing transaction boundaries.
- Register new commands explicitly in `app/actions.php`; never construct a handler path directly from request input.
- Change common navigation and page layout in `app/views/layout.php`.
- Change database credentials in `config.php`.

The `app/` and `database/` directories retain their Apache access restrictions. Refactoring the code does not modify existing database records.

## Regression check

Run `C:\xampp\php\php.exe tests\modules.php` from the project directory. It exercises real inventory, borrowing, return, and profile handlers against a separate randomly named test database, then drops that test database. The configured database account needs permission to create and drop databases for this check. Existing SchoolTrak data is untouched.

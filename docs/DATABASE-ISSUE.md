# XAMPP shutdown diagnosis

The local MySQL error log at `C:\xampp\mysql\data\mysql_error.log` records an InnoDB fatal error at 13:16 on October 29, 2026:

```
Unable to read page [page id: space=2, page number=0] into the buffer pool after 100.
mysqld got exception 0x80000003
```

Earlier entries also report InnoDB page log sequence numbers ahead of the system log sequence number. These are database storage errors. A successful PHP connection does not mean the storage engine is healthy; a later query can encounter an unreadable page and the database server can abort.

The same fatal page-read error repeated at 13:27:33. A read-only Windows service check confirmed that `MySQL80` owns port 3306, while XAMPP's configured port is 3307. Do not stop or remove `MySQL80` as part of a SchoolTrak code change; other applications may use it.

The XAMPP panel screenshot also reports port 3306 already in use. The actual XAMPP `my.ini`, phpMyAdmin configuration, and SchoolTrak configuration currently use port 3307. The panel warning alone does not explain the InnoDB fatal error. Do not switch SchoolTrak to port 3306 without identifying the other database server.

## Application changes

- `install.php` performs no database work on GET/refresh.
- Setup no longer scans `information_schema.tables`.
- Setup only initializes missing schema on an explicit valid POST, refuses to create an administrator if an account exists, and uses a named lock to serialize setup requests.
- A successful setup redirects with HTTP 303 so refresh does not resubmit the installation.
- The administrator session is assigned only after the transaction commits.
- Connections have a five-second connection timeout, no persistent connections, and no automatic retry after a server disconnect.
- Database errors distinguish authentication, missing database, unavailable port, and server disconnect.
- `index.php` no longer eagerly opens a database connection. Anonymous login and protected-page redirects work without MySQL. Authenticated page query failures produce a complete HTTP 503 response with an option to clear the stale session without querying the database.

These changes do not repair corrupted InnoDB storage. Submitting setup or visiting database-backed pages may still encounter the server fault until MariaDB is recovered.

## Remaining server work

Back up the complete XAMPP MySQL data directory and configuration before attempting recovery. Identify and preserve all databases, including databases belonging to other projects. Use MariaDB's documented recovery/export/restore procedure or a database administrator. Recovery mode is for extracting data; it does not repair corruption and should not be used as the application's normal operating mode.

Official reference: https://mariadb.com/docs/server/server-usage/storage-engines/innodb/innodb-troubleshooting/innodb-recovery-modes

No database files, redo logs, system tables, or existing project records were deleted or replaced by this fix. No recovery mode or XAMPP service configuration was changed.

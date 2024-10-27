[< Back to summary](../README.md)

# ⏫ Migration

Sharp included a simple database migration management system.

## MigrationManager

The management of your migration is made through the `MigrationManager` components and your terminal

### Create a migration

You can create a migration file with a simple commmand

```bash
php do migration-create <migration-name>
```

This command will create a SQL file in `<YourApp>/Migrations` that you can edit

Note: Every created migration file contains a timestamp in its filename, it is used to represent the timeline of your application migrations

### List migrations

This command

```bash
php do migration-list
```

... will display a list of all the migrations in your
application and highlight the migrations that are currently active on your database

### Launch migrations

Note: please make a backup of your current database with tools such as `mysqldump` before launching migrations

To apply missing migrations to your database, launch this command

```bash
php do migration-launch
```

Note : When applying migrations, Sharp will wrap your SQL script in a SQL transaction. In case of error, your
database will rollback to its initial state

[< Back to summary](../README.md)
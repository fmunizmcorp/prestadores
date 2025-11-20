# Database Export Instructions - v1.0.0

## Export Database Schema

```bash
# Export schema only (no data)
mysqldump -u u673902663_clinfec -p \
  --no-data \
  --routines \
  --triggers \
  u673902663_clinfec > schema_v1.0.0.sql

# Export full database (schema + data)
mysqldump -u u673902663_clinfec -p \
  --routines \
  --triggers \
  --single-transaction \
  u673902663_clinfec > full_backup_v1.0.0.sql

# Export compressed
mysqldump -u u673902663_clinfec -p \
  --routines \
  --triggers \
  --single-transaction \
  u673902663_clinfec | gzip > full_backup_v1.0.0.sql.gz
```

## Restore Database

```bash
# Create database
mysql -u root -p
CREATE DATABASE u673902663_clinfec CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;

# Restore from SQL
mysql -u u673902663_clinfec -p u673902663_clinfec < full_backup_v1.0.0.sql

# Restore from compressed
gunzip < full_backup_v1.0.0.sql.gz | mysql -u u673902663_clinfec -p u673902663_clinfec
```

## Database Credentials (v1.0.0)

```
Host: localhost
Database: u673902663_clinfec
User: u673902663_clinfec
Password: [see .env or config/Database.php]
Charset: utf8mb4
Collation: utf8mb4_unicode_ci
```

## Tables (v1.0.0)

- usuarios
- prestadores
- projetos
- atividades
- notas_fiscais
- [... see migrations in database/migrations/]
```

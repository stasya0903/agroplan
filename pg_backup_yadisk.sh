#!/bin/bash

# Variables
DB_NAME="agroplan"
DB_USER="myuser"
BACKUP_DIR="$HOME/Yandex.Disk/db_backups"
FILE_NAME="${DB_NAME}_$(date +%F).dump"

# Make sure the backup directory exists
mkdir -p "$BACKUP_DIR"

# Create backup (uses .pgpass for passwordless authentication)
pg_dump -U "$DB_USER" -h localhost -d "$DB_NAME" -F c -f "$BACKUP_DIR/$FILE_NAME"

# Keep only 5 most recent backups
cd "$BACKUP_DIR"
ls -1t ${DB_NAME}_*.dump | tail -n +6 | xargs -r rm --

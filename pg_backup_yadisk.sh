#!/bin/bash

# Variables
DB_NAME="agroplan"
DB_USER="myuser"
TMP_DIR="/tmp/db_backups"
RCLONE_REMOTE="yandex"
RCLONE_PATH="yandex:/db_backups"
FILE_NAME="${DB_NAME}_$(date +%F).dump"
LOCAL_BACKUP="$TMP_DIR/$FILE_NAME"

# Ensure temp directory exists
mkdir -p "$TMP_DIR"

# Create the backup file
pg_dump -U "$DB_USER" -F c "$DB_NAME" > "$LOCAL_BACKUP"

# Upload to Yandex Disk
rclone copy "$LOCAL_BACKUP" "$RCLONE_PATH"

# Remove the local temp file
rm -f "$LOCAL_BACKUP"

# Keep only the 5 most recent backups on Yandex Disk
rclone ls "$RCLONE_PATH" | grep "${DB_NAME}_" | sort -r | sed -n '6,$p' | awk '{print $2}' | while read old_file; do
    rclone delete "$RCLONE_PATH/$old_file"
done

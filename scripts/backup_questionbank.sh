#!/bin/bash

# Question Bank Migration Backup Script
# Created: September 29, 2025
# Purpose: Backup all critical exam/question data before migration

BACKUP_DIR="storage/backups/questionbank_migration_$(date +%Y%m%d_%H%M%S)"
mkdir -p $BACKUP_DIR

echo "🔄 Starting Question Bank Migration Backup..."
echo "📁 Backup Directory: $BACKUP_DIR"

# Function to backup table data
backup_table() {
    local table=$1
    echo "📋 Backing up table: $table"
    php artisan db:table --table=$table > "$BACKUP_DIR/${table}_backup.sql" 2>/dev/null || {
        echo "⚠️  Using mysqldump fallback for $table"
        mysqldump --single-transaction --routines --triggers $(php artisan tinker --execute="echo config('database.connections.mysql.database')") $table > "$BACKUP_DIR/${table}_backup.sql"
    }
}

# Backup critical tables
echo "🗃️  Backing up critical tables..."
backup_table "questions"
backup_table "options"
backup_table "exams"
backup_table "exam_sessions"
backup_table "responses"
backup_table "subjects"
backup_table "users"
backup_table "students"

# Create table count summary
echo "📊 Creating backup summary..."
cat > "$BACKUP_DIR/backup_summary.txt" << EOF
Question Bank Migration Backup Summary
=====================================
Backup Date: $(date)
Backup Directory: $BACKUP_DIR

Table Record Counts:
EOF

# Add record counts to summary
for table in questions options exams exam_sessions responses subjects users students; do
    count=$(mysql -e "SELECT COUNT(*) FROM $table;" $(php artisan tinker --execute="echo config('database.connections.mysql.database')") 2>/dev/null | tail -1)
    echo "  $table: $count records" >> "$BACKUP_DIR/backup_summary.txt"
done

# Create restoration script
cat > "$BACKUP_DIR/restore.sh" << 'EOF'
#!/bin/bash
echo "🔄 Restoring Question Bank Data..."
echo "⚠️  WARNING: This will overwrite current data!"
read -p "Are you sure you want to continue? (yes/no): " confirm

if [ "$confirm" = "yes" ]; then
    for sql_file in *.sql; do
        if [ -f "$sql_file" ]; then
            echo "📋 Restoring: $sql_file"
            mysql $(php artisan tinker --execute="echo config('database.connections.mysql.database')") < "$sql_file"
        fi
    done
    echo "✅ Restoration completed!"
else
    echo "❌ Restoration cancelled"
fi
EOF

chmod +x "$BACKUP_DIR/restore.sh"

echo "✅ Backup completed successfully!"
echo "📁 Backup location: $BACKUP_DIR"
echo "🔧 To restore: cd $BACKUP_DIR && ./restore.sh"
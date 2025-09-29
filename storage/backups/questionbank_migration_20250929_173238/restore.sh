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

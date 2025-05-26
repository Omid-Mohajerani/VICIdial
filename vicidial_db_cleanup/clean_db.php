<?php
// =====================
// CONFIGURATION SECTION
// =====================

$DB_HOST = 'localhost';
$DB_USER = 'dbadmin';
$DB_PASS = 'V3ry$trongP@ssw0rd!';
$DB_NAME = 'asterisk';
$MONTHS_TO_KEEP = 12;

// =====================
// INITIALIZATION
// =====================

$mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($mysqli->connect_error) {
    die("❌ DB Connection failed: " . $mysqli->connect_error);
}

echo "✅ Connected to database: $DB_NAME\n";

// Unix timestamp for 6 months ago
$cutoffEpoch = strtotime("-$MONTHS_TO_KEEP months");
$cutoffDate = date('Y-m-d H:i:s', $cutoffEpoch);

echo "📅 Deleting records older than: $cutoffDate\n";

// =====================
// CLEANUP RULES
// =====================

$cleanupRules = [
    'recording_log' => "start_epoch < $cutoffEpoch",
    'vicidial_agent_log' => "event_time < '$cutoffDate'",
    'user_call_log' => "call_date < '$cutoffDate'",
    'vicidial_dial_log' => "call_date < '$cutoffDate'",
    'vicidial_peer_event_log' => "event_date < '$cutoffDate'",
    'vicidial_user_log' => "event_date < '$cutoffDate'",
    'vicidial_sessions_recent_archive' => "call_date < '$cutoffDate'",
    'vicidial_carrier_log' => "call_date < '$cutoffDate'",
    'vicidial_agent_visibility_log' => "db_time < '$cutoffDate'"
];

// =====================
// CLEANUP PROCESS
// =====================

foreach ($cleanupRules as $table => $whereClause) {
    echo "\n➡️ Cleaning `$table` where $whereClause ... ";

    $countQuery = "SELECT COUNT(*) AS count FROM $table WHERE $whereClause";
    $countResult = $mysqli->query($countQuery);
    $count = $countResult ? $countResult->fetch_assoc()['count'] : 'unknown';

    if ($count > 0) {
        echo "\n   🔍 Found $count old records. Deleting...\n";
        $deleteQuery = "DELETE FROM $table WHERE $whereClause";
        if ($mysqli->query($deleteQuery)) {
            echo "   ✅ Deleted $count rows from `$table`\n";
        } else {
            echo "   ❌ Error deleting from `$table`: " . $mysqli->error . "\n";
        }
    } else {
        echo "✅ No old records to delete.\n";
    }
}

// =====================
// OPTIMIZE ALL TABLES
// =====================

echo "\n🧹 Optimizing all tables in `$DB_NAME`...\n";

$tablesResult = $mysqli->query("SHOW TABLES");
if ($tablesResult) {
    while ($row = $tablesResult->fetch_array()) {
        $tableName = $row[0];
        echo "   🔧 Optimizing `$tableName`... ";
        if ($mysqli->query("OPTIMIZE TABLE `$tableName`")) {
            echo "✅ Done\n";
        } else {
            echo "❌ Failed: " . $mysqli->error . "\n";
        }
    }
} else {
    echo "❌ Failed to list tables: " . $mysqli->error . "\n";
}

$mysqli->close();
echo "\n🏁 Cleanup and optimization completed.\n";
?>


<?php
function getEnumValues($conn, $table, $column) {
    $res = $conn->query("SHOW COLUMNS FROM `$table` LIKE '$column'");
    $row = $res->fetch_assoc();
    preg_match("/^enum\((.*)\)$/", $row['Type'], $matches);
    return array_map(fn($v) => trim($v, "'"), explode(",", $matches[1]));
}

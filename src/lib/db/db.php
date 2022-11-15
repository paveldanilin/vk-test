<?php
namespace vk\lib\db;

use vk\lib\Context;

function db_get_conn(string $pool, Context $context): \mysqli
{
    $host = $context->resolveHost($pool);
    $config = $context->getArray('config')[$pool];

    $db = new \mysqli($host,
        $config['user'] ?? 'guest',
        $config['pass'] ?? 'guest',
        $config['dbname'] ?? 'app',
            "3306");
    if ($db->connect_errno) {
        throw new \RuntimeException('Could not connect to DB: ' . $db->connect_error);
    }
    return $db;
}


/*
function db_insert(string $resource, Context $context, string $table, array $columns): void
{
    $conn = db_get_conn($resource, $context);

    $column_names = \array_keys($columns);
    $column_names_str = \implode(',', $column_names);

    $column_values = [];
    foreach ($column_names as $column_name) {
        $value = $columns[$column_name];
        if (\is_string($value)) {
            $value = \mysqli_real_escape_string($conn, $value);
        }
        $column_values[] = $value;
    }
    $column_values_str = \implode(',', $column_values);

    $sql = "INSERT INTO user_posts ({$column_names_str}) VALUES ({$column_values_str})";

    error_log('[SQL] ' . $resource . ' => ' . $sql);

    $conn->query($sql);
}
*/

function db_select(string $resource, Context $context, string $from, array $parameters = [], array $columns = [], array $order_by = [], int $limit = 0): array
{
    $conn = db_get_conn($resource, $context);

    $selector = '*';
    if (!empty($columns)) {
        $cols = [];
        foreach ($columns as $k => $v) {
            if (\is_string($k)) {
                $cols[] = "`$k` AS $v";
            } else {
                $cols[] = "`$v`";
            }
        }
        $selector = \implode(',', $cols);
    }

    $select = "SELECT $selector FROM `$from`";

    if (!empty($parameters)) {
        $select .= " WHERE 1=1";
        foreach ($parameters as $name => $value) {
            if (\is_array($value)) {
                if (\count($value) === 1) {
                    $select .= " AND $name " . $value[0];
                    //                          IS NULL
                } elseif (\count($value) === 2) {
                    $select .= " AND $name " . $value[0] . $value[1];
                    //                         operator    value
                } elseif (\count($value) === 3) {
                    $select .= ' ' . $value[0] . ' ' . $value[1] . ' ' . $value[2];
                    //               AND/OR             operator         value
                }
            } else {
                if (\is_string($value)) {
                    $value = mysqli_real_escape_string($conn, $value);
                }
                $select .= " AND $name = $value";
            }
        }
    }

    if (!empty($order_by)) {
        $ords = [];
        foreach ($order_by as $order_k => $order_v) {
            if (\is_string($order_k)) {
                $ords[] = "$order_k $order_v";
            } else {
                $ords[] = $order_v;
            }
        }
        $select .= " ORDER BY " . \implode(',', $ords);
    }

    if ($limit > 0) {
        $select .= " LIMIT $limit";
    }

    \error_log('[SQL] ' . $resource . ' => `' . $select . '`');

    $ret = $conn->query($select);
    if (false === $ret) {
        $conn->close();
        throw new \RuntimeException($conn->error . '[sql=' . $select . ']');
    }

    if ($ret->num_rows === 0) {
        $conn->close();
        return [];
    }

    $rows = [];
    while ($row = $ret->fetch_assoc()) {
        $rows[] = $row;
    }

    $conn->close();

    return $rows;
}

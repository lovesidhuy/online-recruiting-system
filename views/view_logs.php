<?php
$decisionLog = file_exists(__DIR__ . '/../logs/ai_decision_log.txt')
    ? file_get_contents(__DIR__ . '/../logs/ai_decision_log.txt')
    : 'No decision logs found.';

$reasonLog = file_exists(__DIR__ . '/../logs/ai_reason_log.txt')
    ? file_get_contents(__DIR__ . '/../logs/ai_reason_log.txt')
    : 'No reason logs found.';
?>

<!DOCTYPE html>
<html>
<head>
    <title>AI Screening Logs</title>
    <style>
        body {
            font-family: monospace;
            background: #f4f4f4;
            padding: 20px;
        }
        h2 {
            background: #333;
            color: white;
            padding: 10px;
        }
        pre {
            background: #fff;
            border: 1px solid #ccc;
            padding: 15px;
            overflow-x: auto;
            white-space: pre-wrap;
        }
        a {
            display: inline-block;
            margin-bottom: 10px;
            text-decoration: none;
            background: #444;
            color: #fff;
            padding: 6px 12px;
            border-radius: 4px;
        }
    </style>
</head>
<body>

<a href="../dashboards/hr_dashboard.php">← Back to Dashboard</a>

<h2>AI Decision Log (PHP)</h2>
<pre><?= htmlspecialchars($decisionLog) ?></pre>

<h2>AI Reason Log (Node.js)</h2>
<pre><?= htmlspecialchars($reasonLog) ?></pre>

</body>
</html>

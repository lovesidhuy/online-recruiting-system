<?php
require_once __DIR__ . '/../config/bootstrap.php'; 

function sendJobNotificationToApplicants($job) {
    global $DB;

    $category_id   = $job['category_id'];
    $job_title     = $job['job_title'];
    $posting_date  = $job['posting_date'];

    $query = "
        SELECT a.first_name, a.email
        FROM applicant_preferred_job_categories p
        JOIN applicant a ON a.applicant_id = p.applicant_id
        WHERE p.category_id = ?
    ";
    
    $stmt = $DB->prepare($query);
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $notificationCount = 0;
    while ($row = $result->fetch_assoc()) {
        $to   = $row['email'];
        $name = $row['first_name'];

        // For testing purposes, we'll log notifications instead of sending emails
        // In production, you would integrate with an email service
        $logMessage = "[" . date('Y-m-d H:i:s') . "] Job notification sent to {$name} ({$to}) for job: {$job_title}\n";
        file_put_contents(__DIR__ . '/../logs/notification_log.txt', $logMessage, FILE_APPEND | LOCK_EX);
        
        $notificationCount++;
    }

    $stmt->close();
    
    // Log summary
    if ($notificationCount > 0) {
        $summaryMessage = "[" . date('Y-m-d H:i:s') . "] Total notifications sent for job '{$job_title}': {$notificationCount}\n";
        file_put_contents(__DIR__ . '/../logs/notification_log.txt', $summaryMessage, FILE_APPEND | LOCK_EX);
    }
    
    return $notificationCount;
}

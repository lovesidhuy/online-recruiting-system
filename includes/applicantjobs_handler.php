<?php
require_once __DIR__ . '/../includes/applicantjobs_helper.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['apply'], $_POST['job_id'])) {
        $job_id = (int)$_POST['job_id'];
         // Fetch existing applications for this applicant
        $existingApps = $appObj->getApplicationsWithJobTitles($applicant_id)->fetch_all(MYSQLI_ASSOC);
        $alreadyApplied = array_filter($existingApps, fn($a) => (int)$a['job_id'] === $job_id);

        if ($alreadyApplied) {
            $message = "You have already applied for this job.";
        } else {
            if ($USE_AI) {
                // Build resume/job data and send to AI screening server
                $resumeData = buildApplicationJson($DB, $applicant_id, $job_id);
                $response = @file_get_contents(
                    "http://localhost:4000/screen",
                    false,
                    stream_context_create([
                        'http' => [
                            'method' => 'POST',
                            'header' => "Content-Type: application/json",
                            'content' => json_encode($resumeData)
                        ]
                    ])
                );
                // Parse AI response

                if ($response === false) {
                    $ai_bool = false;
                    $ai_decision = 'Fail';
                } else {
                    $aiResult    = json_decode($response, true);
                    $decision    = $aiResult['decision'] ?? 'false';
                    $ai_bool     = ($decision === 'true');
                    $ai_decision = $ai_bool ? 'Pass' : 'Fail';
                }
            } else {
                // Random pass/fail if AI is disabled

                $ai_bool = (bool)random_int(0, 1);
                $ai_decision = $ai_bool ? 'Pass' : 'Fail';
            }

            $logLine = sprintf(
                "Applicant #%d => Job #%d => AI Decision: %s => %s | MODE: %s\n",
                $applicant_id, $job_id, $ai_decision, date('Y-m-d H:i:s'), $USE_AI ? 'AI' : 'Random'
            );
            file_put_contents(__DIR__ . '/../logs/ai_decision_log.txt', $logLine, FILE_APPEND);

            $appObj->addApplication($applicant_id, $job_id, date('Y-m-d'), $ai_bool);
            $application_id = $DB->insert_id;
            $screenObj->addScreeningResult($application_id, $ai_bool ? 'True' : 'False');

            // Log email notifications instead of sending actual emails
            $jobInfo   = $jobObj->getJobById($job_id)->fetch_assoc();
            $job_title = $jobInfo['job_title'] ?? 'Unknown Job';
            $to        = $_SESSION['user']['email'];
            $name      = $_SESSION['user']['name'] ?? 'Applicant';

            // Log applicant notification
            $emailLog = sprintf(
                "[%s] EMAIL TO APPLICANT: %s (%s) - Application received for '%s' - AI Screening: %s\n",
                date('Y-m-d H:i:s'), $name, $to, $job_title, $ai_decision
            );
            file_put_contents(__DIR__ . '/../logs/notification_log.txt', $emailLog, FILE_APPEND);

            // Log HR notification if passed
            if ($ai_bool) {
                $hrEmailLog = sprintf(
                    "[%s] EMAIL TO HR: Qualified candidate %s (%s) for job '%s'\n",
                    date('Y-m-d H:i:s'), $name, $to, $job_title
                );
                file_put_contents(__DIR__ . '/../logs/notification_log.txt', $hrEmailLog, FILE_APPEND);
            }

            $message = "You applied to '$job_title'. Application status: $ai_decision";
        }
    }

    if (isset($_POST['withdraw'], $_POST['application_id'])) {
        $appObj->deleteApplication($_POST['application_id']);
        $message = "Application withdrawn.";
    }

    if (isset($_POST['toggle_preferred'], $_POST['category_id'])) {
        $cat_id = (int)$_POST['category_id'];
        $preferred = $prefObj->getPreferredJobsByApplicant($applicant_id)->fetch_all(MYSQLI_ASSOC);
        $exists = array_filter($preferred, fn($p) => (int)$p['category_id'] === $cat_id);

        if ($exists) {
            $prefObj->deletePreferredJob($applicant_id, $cat_id);
            $message = "Removed from preferred categories.";
        } else {
            $prefObj->addPreferredJob($applicant_id, $cat_id);
            $message = "Added to preferred categories.";
        }
    }

    if (isset($_POST['view_job'], $_POST['job_id'])) {
        $_SESSION['selected_job'] = $_POST['job_id'];
    }

    $_SESSION['message'] = $message;
    header("Location: " . $_SERVER['REQUEST_URI']);
    exit();
}

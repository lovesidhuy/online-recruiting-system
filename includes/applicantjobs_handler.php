<?php
require_once __DIR__ . '/../includes/applicantjobs_helper.php';

// Enable AI mode
$USE_AI = true;

// Helper function to call Node.js AI screening API
function callAiScreening($resumeData) {
    $ch = curl_init("http://localhost:4000/screen");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($resumeData));

    $result = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        error_log("AI SCREENING ERROR: $error");
        return ['decision' => 'false', 'reason' => 'AI Error'];
    }

    return json_decode($result, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    if (isset($_POST['apply'], $_POST['job_id'])) {
        $job_id = (int)$_POST['job_id'];
        $existingApps = $appObj->getApplicationsWithJobTitles($applicant_id)->fetch_all(MYSQLI_ASSOC);
        $alreadyApplied = array_filter($existingApps, fn($a) => (int)$a['job_id'] === $job_id);

        if ($alreadyApplied) {
            $message = "You have already applied for this job.";
        } else {
            if ($USE_AI) {
                error_log("✅ Entered AI screening");

                // Step 1: Build resume data
                $resumeData = buildApplicationJson($DB, $applicant_id, $job_id);
                error_log("Resume Data: " . json_encode($resumeData));

                // Step 2: Call AI API
                $aiResult = callAiScreening($resumeData);
                error_log("AI Result: " . json_encode($aiResult));

                // Step 3: Extract decision and reason
                $decision = strtolower(trim($aiResult['decision'] ?? 'false'));
                $reason = $aiResult['reason'] ?? 'No reason provided';
                $ai_bool = ($decision === 'true');
                $ai_decision = $ai_bool ? 'Pass' : 'Fail';
                error_log("🧠 Final Decision: $ai_decision | Reason: $reason");

                // Step 4: Log to ai_decision_log.txt
                $logLine = sprintf(
                    "Applicant #%d => Job #%d => AI Decision: %s => %s | Reason: %s | MODE: AI\n",
                    $applicant_id, $job_id, $ai_decision, date('Y-m-d H:i:s'), $reason
                );
                $logFile = __DIR__ . '/../logs/ai_decision_log.txt';
                file_put_contents($logFile, $logLine, FILE_APPEND);
                error_log("✅ Written to ai_decision_log.txt");
            } else {
                $ai_bool = (bool)random_int(0, 1);
                $ai_decision = $ai_bool ? 'Pass' : 'Fail';
                $reason = 'Screened using random fallback.';

                // Log fallback decision
                $logLine = sprintf(
                    "Applicant #%d => Job #%d => AI Decision: %s => %s | Reason: %s | MODE: Random\n",
                    $applicant_id, $job_id, $ai_decision, date('Y-m-d H:i:s'), $reason
                );
                file_put_contents(__DIR__ . '/../logs/ai_decision_log.txt', $logLine, FILE_APPEND);
            }

            // Save application + result
            $appObj->addApplication($applicant_id, $job_id, date('Y-m-d'), $ai_bool);
            $application_id = $DB->insert_id;
            $screenObj->addScreeningResult($application_id, $ai_bool ? 'True' : 'False');

            // Get job info for email
            $jobInfo = $jobObj->getJobById($job_id)->fetch_assoc();
            $job_title = $jobInfo['job_title'] ?? 'Unknown Job';
            $to = $_SESSION['user']['email'];
            $name = $_SESSION['user']['name'] ?? 'Applicant';

            // Email functionality has been removed.

            $message = "You applied to '$job_title'.";
        }
    }

    // Other actions
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

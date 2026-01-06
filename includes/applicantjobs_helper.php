<?php
function buildApplicationJson($DB, $applicant_id, $job_id) {
    $applicantModel  = new Applicant($DB);
    $educationModel  = new Education($DB);
    $experienceModel = new WorkExperience($DB);
    $skillModel      = new ApplicantSkill($DB);
    $jobModel        = new Job($DB);

    $info   = $applicantModel->getApplicantById($applicant_id)->fetch_assoc();
    $edu    = $educationModel->getEducationByApplicant($applicant_id)->fetch_all(MYSQLI_ASSOC);
    $exp    = $experienceModel->getExperienceByApplicant($applicant_id)->fetch_all(MYSQLI_ASSOC);
    $skills = $skillModel->getSkillsByApplicant($applicant_id)->fetch_all(MYSQLI_ASSOC);
    $job    = $jobModel->getJobById($job_id)->fetch_assoc();

    return [
        'applicant' => [
            'id'         => $info['applicant_id'],
            'name'       => $info['first_name'] . ' ' . $info['last_name'],
            'email'      => $info['email'],
            'phone'      => $info['phone_number'],
            'dob'        => $info['dob'],
            'education'  => $edu,
            'experience' => $exp,
            'skills'     => array_column($skills, 'skill_name')
        ],
        'job' => [
            'id'                  => $job['job_id'],
            'title'               => $job['job_title'],
            'company'             => $job['company_name'],
            'category_id'         => $job['category_id'],
            'description'         => $job['job_description'],
            'required_education'  => $job['required_education'],
            'required_experience' => $job['required_experience']
        ],
        'applied_at' => date('Y-m-d H:i:s')
    ];
}

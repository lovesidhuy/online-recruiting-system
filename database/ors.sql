DROP DATABASE IF EXISTS ors;
CREATE DATABASE IF NOT EXISTS ors;
USE ors;

-- 1) Administrative Tables
CREATE TABLE admin (
    admin_id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone_number VARCHAR(25),
    password VARCHAR(255) NOT NULL DEFAULT 'Admin@123',
    profile_image VARCHAR(255) NOT NULL DEFAULT 'b.com'
);

CREATE TABLE hr (
    hr_id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone_number VARCHAR(25),
    password VARCHAR(255) NOT NULL DEFAULT 'HR@123',
    profile_image VARCHAR(255) NOT NULL DEFAULT 'c.com',
    FOREIGN KEY (admin_id) REFERENCES admin(admin_id) ON DELETE CASCADE
);

-- 2) Applicant Tables
CREATE TABLE applicant (
    applicant_id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone_number VARCHAR(25),
    password VARCHAR(255) NOT NULL DEFAULT 'Applicant@123',
    profile_image VARCHAR(255) NOT NULL DEFAULT 'd.com',
    dob DATE
);

CREATE TABLE education (
    education_id INT AUTO_INCREMENT PRIMARY KEY,
    applicant_id INT NOT NULL,
    degree VARCHAR(100) NOT NULL,
    institution VARCHAR(100) NOT NULL,
    start_year INT NOT NULL,
    end_year INT,
    FOREIGN KEY (applicant_id) REFERENCES applicant(applicant_id) ON DELETE CASCADE
);

CREATE TABLE work_experience (
    work_id INT AUTO_INCREMENT PRIMARY KEY,
    applicant_id INT NOT NULL,
    company_name VARCHAR(100) NOT NULL,
    job_title VARCHAR(100) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE,
    description TEXT,
    FOREIGN KEY (applicant_id) REFERENCES applicant(applicant_id) ON DELETE CASCADE
);

CREATE TABLE skill (
    skill_id INT AUTO_INCREMENT PRIMARY KEY,
    skill_name VARCHAR(100) UNIQUE NOT NULL
);

CREATE TABLE applicant_skill (
    applicant_id INT NOT NULL,
    skill_id INT NOT NULL,
    PRIMARY KEY (applicant_id, skill_id),
    FOREIGN KEY (applicant_id) REFERENCES applicant(applicant_id) ON DELETE CASCADE,
    FOREIGN KEY (skill_id) REFERENCES skill(skill_id) ON DELETE CASCADE
);

-- 3) Company and Categories
CREATE TABLE company (
    company_id INT AUTO_INCREMENT PRIMARY KEY,
    company_name VARCHAR(100) NOT NULL,
    contact_email VARCHAR(100),
    phone_number VARCHAR(25)
);

CREATE TABLE job_category (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) UNIQUE NOT NULL
);

-- Job Table
CREATE TABLE job (
    job_id INT AUTO_INCREMENT PRIMARY KEY,
    job_title VARCHAR(100) NOT NULL,
    hr_id INT NOT NULL,
    company_id INT,
    job_description TEXT,
    location VARCHAR(100),
    salary_range VARCHAR(50),
    category_id INT,
    position_type ENUM('Part Time','Full Time','Contract') NOT NULL DEFAULT 'Full Time',
    min_salary DECIMAL(12,2),
    max_salary DECIMAL(12,2),
    required_education VARCHAR(255),
    required_experience VARCHAR(255),
    posting_date DATE,
    close_date DATE,
    FOREIGN KEY (hr_id) REFERENCES hr(hr_id) ON DELETE CASCADE,
    FOREIGN KEY (company_id) REFERENCES company(company_id) ON DELETE SET NULL,
    FOREIGN KEY (category_id) REFERENCES job_category(category_id) ON DELETE SET NULL
);

-- Applications & AI Screening
CREATE TABLE applications (
    application_id INT AUTO_INCREMENT PRIMARY KEY,
    applicant_id INT NOT NULL,
    job_id INT NOT NULL,
    applied_date DATE,
    application_status BOOLEAN NOT NULL DEFAULT FALSE,
    FOREIGN KEY (applicant_id) REFERENCES applicant(applicant_id) ON DELETE CASCADE,
    FOREIGN KEY (job_id) REFERENCES job(job_id) ON DELETE CASCADE
);

CREATE TABLE ai_screening_results (
    screening_id INT AUTO_INCREMENT PRIMARY KEY,
    application_id INT NOT NULL,
    ai_decision ENUM('True', 'False') NOT NULL,
    FOREIGN KEY (application_id) REFERENCES applications(application_id) ON DELETE CASCADE
);

-- Preferred Job Categories
CREATE TABLE applicant_preferred_job_categories (
    applicant_id INT NOT NULL,
    category_id INT NOT NULL,
    PRIMARY KEY (applicant_id, category_id),
    FOREIGN KEY (applicant_id) REFERENCES applicant(applicant_id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES job_category(category_id) ON DELETE CASCADE
);

-- SAMPLE DATA INSERTS

-- Admin
INSERT INTO admin (admin_id, first_name, last_name, email, phone_number)
VALUES 
(1, 'Test', 'Admin1', 'admin1@test.com', '+1 (000) 000-1000'),
(2, 'Test', 'Admin2', 'admin2@test.com', '+1 (000) 000-2000');

-- HR
INSERT INTO hr (hr_id, admin_id, first_name, last_name, email, phone_number)
VALUES
(3, 1, 'Test', 'HR1', 'hr1@test.com', '+1 (000) 000-3000'),
(4, 2, 'Test', 'HR2', 'hr2@test.com', '+1 (000) 000-4000'),
(5, 1, 'Test', 'HR3', 'hr3@test.com', '+1 (000) 000-5000');

-- Applicants
INSERT INTO applicant (applicant_id, first_name, last_name, email, phone_number, dob)
VALUES
(10, 'James', 'Wilson', 'james.wilson@gmail.com', '+1 (416) 555-0101', '1988-06-15'),
(11, 'Sarah', 'Chen', 'sarah.chen@yahoo.com', '+1 (604) 555-0202', '1992-03-22'),
(12, 'Mohammed', 'Ali', 'mohammed.ali@ymail.com', '+1 (514) 555-0303', '1990-11-30'),
(13, 'Emily', 'Tremblay', 'emily.tremblay@xmail.com', '+1 (403) 555-0404', '1985-09-18'),
(14, 'David', 'Singh', 'david.singh@zmail.com', '+1 (613) 555-0505', '1991-07-25'),
(15, 'Rita', 'Patel', 'rita.patel@tmail.com', '+1 (289) 555-0606', '1989-12-10'),
(16, 'Carlos', 'Garcia', 'carlos.garcia@kmail.org', '+1 (305) 555-0707', '1990-01-01'),
(17, 'Anna', 'Brown', 'anna.brown@rnail.net', '+1 (917) 555-0808', '1993-02-12'),
(18, 'Wei', 'Zhang', 'wei.zhang@wrel.com', '+1 (647) 555-0909', '1987-10-30'),
(19, 'Maria', 'Lopez', 'maria.lopez@srel.com', '+1 (415) 555-1010', '1994-05-05');

-- Job Categories
INSERT INTO job_category (category_id, category_name)
VALUES
(1, 'Software Development'),
(2, 'Data Science & Analytics'),
(3, 'Finance & Accounting'),
(4, 'Healthcare'),
(5, 'Sales & Marketing'),
(6, 'Human Resources'),
(7, 'Engineering'),
(8, 'Customer Service'),
(9, 'Project Management'),
(10, 'Operations'),
(11, 'Legal'),
(12, 'Research & Development');

-- Skills
INSERT INTO skill (skill_name)
VALUES
('Java'),
('Python'),
('SQL'),
('Machine Learning'),
('Data Analysis'),
('Project Management'),
('Leadership'),
('Teamwork'),
('Problem Solving'),
('Communication'),
('Sales'),
('Customer Service'),
('Finance'),
('Healthcare Management'),
('Mechanical Design');

-- Companies
INSERT INTO company (company_name, contact_email, phone_number)
VALUES
('Tech Solutions Inc', 'contact@techsolutions.com', '+1 (416) 555-1234'),
('Data Analytics Co', 'info@dataanalytics.com', '+1 (604) 555-5678'),
('Creative Minds Agency', 'careers@creativeminds.com', '+1 (905) 555-8765'),
('Green Energy Corp', 'hr@greenenergy.com', '+1 (613) 555-4321'),
('Engineering Solutions', 'contact@engsolutions.com', '+1 (514) 555-3456'),
('Blue Finance Group', 'jobs@bluefinance.com', '+1 (212) 555-9090');

-- Existing Jobs
INSERT INTO job 
(
  job_title, hr_id, company_id, job_description, location, salary_range, 
  category_id, position_type, min_salary, max_salary, required_education, 
  required_experience, posting_date
)
VALUES
('Front-End Developer', 3, 1,
 'Develop and maintain front-end web applications.', 
 'Toronto, ON', '60,000 - 80,000', 1, 
 'Full Time', 60000.00, 80000.00,
 'BSc in Computer Science or related field', '1+ years front-end dev', 
 '2025-02-25'),

('Data Analyst', 4, 2,
 'Analyze datasets to provide actionable insights.', 
 'Vancouver, BC', '55,000 - 75,000', 2, 
 'Full Time', 55000.00, 75000.00,
 'BSc in Data Science or related field', 'Experience with SQL & Python', 
 '2025-02-25'),

('Junior Accountant', 5, 6,
 'Manage ledger entries and financial statements.', 
 'Montreal, QC', '45,000 - 60,000', 3,
 'Full Time', 45000.00, 60000.00,
 'Bachelor in Accounting', 'Internship or 1-year experience', 
 '2025-02-25'),

('Healthcare Coordinator', 3, 4,
 'Coordinate patient care and manage health records.', 
 'Ottawa, ON', '50,000 - 70,000', 4, 
 'Full Time', 50000.00, 70000.00,
 'BSc in Healthcare Administration', '1+ year clinical setting', 
 '2025-02-25'),

('Digital Marketing Specialist', 4, 3,
 'Execute and optimize online marketing campaigns.', 
 'Remote', '50,000 - 80,000', 5, 
 'Full Time', 50000.00, 80000.00,
 'Bachelor in Marketing or related field', 'SEO & SEM experience', 
 '2025-02-25'),

('HR Generalist', 5, 1,
 'Handle recruitment, onboarding, and employee relations.', 
 'Calgary, AB', '48,000 - 65,000', 6,
 'Full Time', 48000.00, 65000.00,
 'Bachelor in HR or related field', '2+ years HR experience', 
 '2025-02-25'),

('Mechanical Engineer', 3, 5,
 'Design and develop mechanical systems and components.', 
 'Edmonton, AB', '60,000 - 90,000', 7, 
 'Full Time', 60000.00, 90000.00,
 'BSc in Mechanical Engineering', 'CAD & prototyping experience', 
 '2025-02-25'),

('Customer Service Representative', 4, 3,
 'Assist customers with inquiries and troubleshoot issues.', 
 'Victoria, BC', '35,000 - 45,000', 8, 
 'Full Time', 35000.00, 45000.00,
 'High School Diploma or equivalent', 'Strong communication skills', 
 '2025-02-25'),

('Project Coordinator', 5, 2,
 'Coordinate tasks, deadlines, and resources for projects.', 
 'Mississauga, ON', '50,000 - 70,000', 9, 
 'Full Time', 50000.00, 70000.00,
 'Bachelor in Business or related field', 'Project Management skills', 
 '2025-02-25'),

('Operations Specialist', 3, 6,
 'Optimize operational processes and logistics.', 
 'Halifax, NS', '55,000 - 75,000', 10, 
 'Full Time', 55000.00, 75000.00,
 'Bachelor in Business or Operations', 'Process improvement experience', 
 '2025-02-25'),

('Legal Assistant', 4, 3,
 'Assist attorneys with drafting and research.', 
 'Quebec City, QC', '40,000 - 55,000', 11, 
 'Full Time', 40000.00, 55000.00,
 'Legal Studies or Paralegal Certification', 'Strong organizational skills', 
 '2025-02-25'),

('R&D Scientist', 5, 2,
 'Conduct experiments and develop new products or processes.', 
 'Waterloo, ON', '65,000 - 95,000', 12, 
 'Full Time', 65000.00, 95000.00,
 'MSc in relevant science field', '2+ years lab research', 
 '2025-02-25');

INSERT INTO company (company_id, company_name, contact_email, phone_number)
VALUES
(7, 'J&J Law Firm', 'J&J.Law@gmail.com', '+1 (212) 888-9999');

INSERT INTO job (
    job_title, hr_id, company_id, job_description, location, salary_range, 
    category_id, position_type, min_salary, max_salary, required_education, 
    required_experience, posting_date, close_date
)
VALUES
(
  'Energy Specialist', 
  3, 
  4,
  'Reporting to the Manager, Energy and Sustainability, the Energy Specialist identifies and enhances energy efficiency opportunities at Green Energy Corp. The position provides technical and reporting support for energy retrofit projects, new construction projects and in-house sustainability programs. Employee is expected to collect utility data for energy use, performance tracking and greenhouse gas emission reporting. The position develops energy efficiency and sustainability design standards, and operator engagement programs.', 
  'Delta', 
  '77,000 - 90,000', 
  12, 
  'Full Time', 
  77000.00, 
  90000.00, 
  'Masters in Clean Energy', 
  'At least 5 years of work experience in the energy or sustainability field.', 
  '2025-03-02', 
  '2025-04-02'
);

INSERT INTO job (
    job_title, hr_id, company_id, job_description, location, salary_range, 
    category_id, position_type, min_salary, max_salary, required_education, 
    required_experience, posting_date, close_date
)
VALUES
(
  'Private Music Instructor', 
  4, 
  3,
  'As a private music instructor in the Music Department, you will instruct students in the Music Diploma, Music Technology Diploma, and Foundation for Music Therapy Studies programs. The successful candidate will instruct students, facilitate learning, evaluate student progress in cohort programs and model musical excellence. As part of an integrated instructional team, the successful candidate will plan, organize, and manage learning activities and maintain quality learner-centred instruction.', 
  'West Vancouver', 
  '15,000 - 25,000', 
  8, 
  'Part Time', 
  15000.00, 
  25000.00, 
  'Bachelor of Education', 
  '5+ years of practice with brass instruments and 2+ years of experience teaching.', 
  '2025-03-07', 
  '2025-05-07'
);

INSERT INTO job (
    job_title, hr_id, company_id, job_description, location, salary_range, 
    category_id, position_type, min_salary, max_salary, required_education, 
    required_experience, posting_date, close_date
)
VALUES
(
  'HR Manager', 
  5, 
  7, 
  'In this position you will be an operational and strategic manager to the J&J Law Firm. You will oversee a team of 300 employees and will leverage strong human resource management, problem solving and communication skills to advise and guide leaders and staff on a broad set of human resources matters. As a trusted HR partner, this position facilitates implementation and execution of HR strategy, programs and plans aligned to deliver the business strategy.', 
  'Kelowna', 
  '80,000 - 100,000', 
  6, 
  'Full Time', 
  80000.00, 
  100000.00, 
  'Masters degree in Human Resource Management or Business Administration with a focus on HR', 
  '3-5 years of HR management experience in the field', 
  '2025-02-09', 
  '2025-02-28'
);

-- Education
INSERT INTO education (applicant_id, degree, institution, start_year, end_year)
VALUES
(10, 'BSc Computer Science', 'University of Toronto', 2006, 2010),
(10, 'MSc Computer Science', 'University of Toronto', 2010, 2012),
(11, 'BSc Statistics', 'University of British Columbia', 2010, 2014),
(12, 'Bachelor of Business Administration', 'McGill University', 2008, 2012),
(13, 'Bachelor of Nursing', 'University of Alberta', 2003, 2007),
(14, 'Bachelor of Engineering', 'Carleton University', 2007, 2011),
(15, 'Bachelor of Commerce', 'Ryerson University', 2008, 2012),
(16, 'Bachelor of Mechanical Engineering', 'MIT', 2009, 2013),
(17, 'BA Communications', 'York University', 2011, 2015),
(18, 'BSc Data Science', 'University of Waterloo', 2007, 2011),
(19, 'BA Marketing', 'UCLA', 2010, 2014);

-- Work Experience
INSERT INTO work_experience (applicant_id, company_name, job_title, start_date, end_date, description)
VALUES
(10, 'Tech Solutions Inc', 'Software Developer', '2013-06-01', '2018-05-30', 'Front-end development with React'),
(10, 'Startup Co', 'Full Stack Developer', '2018-06-01', NULL, 'Developed REST APIs and microservices'),
(11, 'Data Analytics Co', 'Junior Data Analyst', '2015-08-01', '2018-07-30', 'Assisted in data cleansing and reporting'),
(11, 'Insight Corp', 'Data Analyst', '2018-08-01', NULL, 'Managed dashboards and statistical analyses'),
(12, 'Retail Bank', 'Financial Analyst', '2012-09-01', NULL, 'Budget forecasts, P&L analysis'),
(13, 'Community Hospital', 'Registered Nurse', '2007-07-01', '2012-12-31', 'General ward nurse'),
(14, 'Engineering Solutions', 'Junior Engineer', '2012-01-01', '2016-03-01', 'CAD design and testing'),
(15, 'Retail Chain', 'Sales Associate', '2012-06-01', '2015-06-01', 'Managed POS and customer relations'),
(16, 'Automotive Corp', 'Mechanical Intern', '2013-05-01', '2014-08-01', 'Supported design team with testing'),
(17, 'Media Company', 'Communications Intern', '2015-01-01', '2016-06-01', 'Assisted with press releases'),
(18, 'Data Startup', 'Data Analyst', '2012-02-01', '2016-12-31', 'Analyzed user metrics'),
(19, 'Ad Agency', 'Marketing Coordinator', '2014-01-01', '2018-07-01', 'Assisted in digital marketing campaigns');

-- Applicant Skills
INSERT INTO applicant_skill (applicant_id, skill_id)
VALUES
(10, 1),
(10, 2),
(10, 3),
(11, 2),
(11, 4),
(11, 5),
(12, 13),
(13, 14),
(14, 15),
(15, 10),
(16, 7),
(16, 8),
(17, 10),
(18, 2),
(18, 3),
(18, 5),
(19, 11);

-- Applications
INSERT INTO applications (applicant_id, job_id, applied_date, application_status)
VALUES
(10, 1, '2025-03-01', TRUE),
(11, 2, '2025-03-02', TRUE),
(12, 3, '2025-03-02', FALSE),
(13, 4, '2025-03-03', TRUE),
(14, 7, '2025-03-03', TRUE),
(15, 5, '2025-03-04', FALSE);

-- AI Screening Results
INSERT INTO ai_screening_results (application_id, ai_decision)
VALUES
(1, 'True'),
(2, 'True'),
(3, 'False'),
(4, 'True'),
(5, 'True'),
(6, 'False');

-- Applicant Preferred Categories
INSERT INTO applicant_preferred_job_categories (applicant_id, category_id)
VALUES
(10, 1),
(11, 2),
(12, 3),
(13, 4),
(14, 7);

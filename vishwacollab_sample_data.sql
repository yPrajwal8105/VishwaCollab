-- VishwaCollab Sample Data SQL File
-- Import this file into phpMyAdmin to add sample data

-- Create database if it doesn't exist
CREATE DATABASE IF NOT EXISTS vishwacollab;
USE vishwacollab;

-- Create tables
CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    course VARCHAR(100),
    skills TEXT,
    cgpa DECIMAL(3,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS companies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    industry VARCHAR(100),
    location VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS jobs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    requirements TEXT,
    location VARCHAR(100),
    salary VARCHAR(50),
    status ENUM('active', 'closed') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    job_id INT NOT NULL,
    status ENUM('pending', 'accepted', 'rejected', 'interview') DEFAULT 'pending',
    application_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert sample students data
INSERT INTO students (user_id, name, email, course, skills, cgpa) VALUES 
(1, 'Rahul Sharma', 'rahul@example.com', 'Computer Science', 'Java, Python, Web Development, Machine Learning', 9.2),
(2, 'Priya Patel', 'priya@example.com', 'Information Technology', 'Data Science, Python, SQL, Statistics', 9.5),
(3, 'Amit Kumar', 'amit@example.com', 'Computer Engineering', 'Android Development, Java, Kotlin, UI/UX', 8.9),
(4, 'Sneha Singh', 'sneha@example.com', 'Software Engineering', 'React, Node.js, JavaScript, MongoDB', 9.1),
(5, 'Vikram Reddy', 'vikram@example.com', 'Computer Science', 'C++, System Programming, Algorithms', 8.7),
(6, 'Anjali Gupta', 'anjali@example.com', 'Information Technology', 'Cloud Computing, AWS, Docker, Kubernetes', 9.3),
(7, 'Rajesh Verma', 'rajesh@example.com', 'Computer Science', 'Full Stack Development, PHP, Laravel, MySQL', 8.8),
(8, 'Kavya Nair', 'kavya@example.com', 'Software Engineering', 'Angular, TypeScript, REST APIs, Git', 9.0),
(9, 'Suresh Kumar', 'suresh@example.com', 'Computer Engineering', 'DevOps, Jenkins, Linux, Shell Scripting', 8.6),
(10, 'Meera Joshi', 'meera@example.com', 'Information Technology', 'Data Analytics, R, Tableau, Power BI', 9.4);

-- Insert sample companies data
INSERT INTO companies (user_id, name, email, industry, location) VALUES 
(1, 'TechCorp Solutions', 'hr@techcorp.com', 'Information Technology', 'Bangalore'),
(2, 'DataViz Analytics', 'careers@dataviz.com', 'Data Analytics', 'Hyderabad'),
(3, 'CloudTech Systems', 'jobs@cloudtech.com', 'Cloud Computing', 'Mumbai'),
(4, 'WebCraft Studios', 'contact@webcraft.com', 'Web Development', 'Pune'),
(5, 'AI Innovations', 'info@aiinnovations.com', 'Artificial Intelligence', 'Delhi'),
(6, 'MobileFirst Apps', 'careers@mobilefirst.com', 'Mobile Development', 'Chennai'),
(7, 'CyberSec Pro', 'jobs@cybersec.com', 'Cybersecurity', 'Gurgaon'),
(8, 'FinTech Solutions', 'hr@fintech.com', 'Financial Technology', 'Bangalore'),
(9, 'GameDev Studios', 'contact@gamedev.com', 'Game Development', 'Mumbai'),
(10, 'EduTech Platform', 'careers@edutech.com', 'Educational Technology', 'Hyderabad');

-- Insert sample jobs data
INSERT INTO jobs (company_id, title, description, requirements, location, salary, status) VALUES 
(1, 'Senior Software Developer', 'Develop and maintain web applications using modern technologies', 'Java, Spring Boot, React, MySQL, 3+ years experience', 'Bangalore', '8-12 LPA', 'active'),
(2, 'Data Scientist', 'Build machine learning models and analyze large datasets', 'Python, Machine Learning, SQL, Statistics, 2+ years experience', 'Hyderabad', '6-10 LPA', 'active'),
(3, 'Cloud Engineer', 'Design and implement cloud infrastructure solutions', 'AWS, Docker, Kubernetes, Linux, 2+ years experience', 'Mumbai', '7-11 LPA', 'active'),
(4, 'Frontend Developer', 'Create responsive web applications with modern frameworks', 'React, JavaScript, HTML, CSS, 1+ years experience', 'Pune', '4-7 LPA', 'active'),
(5, 'AI Research Engineer', 'Develop AI solutions and machine learning algorithms', 'Python, TensorFlow, PyTorch, Research experience', 'Delhi', '10-15 LPA', 'active'),
(6, 'Mobile App Developer', 'Build cross-platform mobile applications', 'React Native, Flutter, JavaScript, 2+ years experience', 'Chennai', '5-9 LPA', 'active'),
(7, 'Cybersecurity Analyst', 'Protect systems and networks from cyber threats', 'Security tools, Network security, Ethical hacking', 'Gurgaon', '6-10 LPA', 'active'),
(8, 'Full Stack Developer', 'End-to-end web application development', 'Node.js, React, MongoDB, JavaScript, 2+ years experience', 'Bangalore', '5-8 LPA', 'active'),
(9, 'Game Developer', 'Create engaging mobile and web games', 'Unity, C#, Game Design, 1+ years experience', 'Mumbai', '4-7 LPA', 'active'),
(10, 'DevOps Engineer', 'Automate deployment and infrastructure management', 'Jenkins, Docker, AWS, Linux, 2+ years experience', 'Hyderabad', '6-10 LPA', 'active'),
(1, 'Junior Software Developer', 'Entry-level position for fresh graduates', 'Java, Python, Basic programming knowledge', 'Bangalore', '3-5 LPA', 'active'),
(2, 'Data Analyst Intern', 'Analyze data and create visualizations', 'Python, SQL, Excel, Fresh graduates welcome', 'Hyderabad', '2-4 LPA', 'active'),
(3, 'Cloud Support Engineer', 'Provide technical support for cloud services', 'AWS basics, Linux, Customer support', 'Mumbai', '3-6 LPA', 'active'),
(4, 'UI/UX Designer', 'Design user interfaces and user experiences', 'Figma, Adobe XD, Design principles', 'Pune', '4-7 LPA', 'active'),
(5, 'Machine Learning Intern', 'Work on ML projects and research', 'Python, Machine Learning basics, Fresh graduates', 'Delhi', '2-4 LPA', 'active');

-- Insert sample applications data
INSERT INTO applications (student_id, job_id, status, application_date) VALUES 
(1, 1, 'pending', '2024-01-15 10:30:00'),
(2, 2, 'interview', '2024-01-14 14:20:00'),
(3, 6, 'accepted', '2024-01-13 09:15:00'),
(4, 4, 'pending', '2024-01-16 11:45:00'),
(5, 5, 'interview', '2024-01-12 16:30:00'),
(6, 3, 'pending', '2024-01-17 08:20:00'),
(7, 8, 'accepted', '2024-01-11 13:10:00'),
(8, 4, 'pending', '2024-01-18 15:25:00'),
(9, 7, 'interview', '2024-01-10 12:40:00'),
(10, 2, 'pending', '2024-01-19 09:30:00'),
(1, 11, 'pending', '2024-01-20 10:15:00'),
(2, 12, 'interview', '2024-01-19 14:50:00'),
(3, 13, 'pending', '2024-01-21 11:20:00'),
(4, 14, 'accepted', '2024-01-18 16:15:00'),
(5, 15, 'pending', '2024-01-22 08:45:00');

-- Show summary of inserted data
SELECT 'Students' as table_name, COUNT(*) as count FROM students
UNION ALL
SELECT 'Companies' as table_name, COUNT(*) as count FROM companies
UNION ALL
SELECT 'Jobs' as table_name, COUNT(*) as count FROM jobs
UNION ALL
SELECT 'Applications' as table_name, COUNT(*) as count FROM applications;

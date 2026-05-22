# VishwaCollab - Credentials & Access Guide

## 🚀 Quick Access Guide

### Base URL
```
http://localhost/Vishwacollab/
```

---

## 📋 Default Login Credentials

### **STUDENT Account**
```
Email: student@example.com
Password: student123
Role: student
```

**Access Features:**
- ✅ AI Resume Builder
- ✅ Chat with Seniors
- ✅ Job Recommendations (Enhanced)
- ✅ Mock Interview Practice
- ✅ Job Search
- ✅ Applications Management
- ✅ Interview Scheduling

---

### **TPO (Training & Placement Officer) Account**
```
Email: tpo@example.com
Password: tpo123
Role: tpo
```

**Access Features:**
- ✅ Student Management
- ✅ Company Management
- ✅ Job Postings Management
- ✅ Placements Tracking
- ✅ Reports & Analytics
- ✅ Live Market Jobs (Adzuna)

---

### **COMPANY Account**
```
Email: company@example.com
Password: company123
Role: company
```

**Access Features:**
- ✅ Post Jobs
- ✅ View Applications
- ✅ Manage Candidates
- ✅ Schedule Interviews
- ✅ Company Profile Management

---

## 🎯 Where to Find All Features

### **STUDENT Dashboard** (`student-dashboard.php`)

#### Main Navigation Menu:
1. **Dashboard** - Overview of applications, interviews, and stats
2. **My Profile** - Update personal information and skills
3. **Upload Resume** - Upload resume files
4. **AI Resume Builder** ⭐ NEW - `student-resume-ai.php`
   - Generate resume content based on job role
   - AI-powered professional summaries
   - Skills and achievements generation
5. **Resume Builder** - Traditional resume upload
6. **ATS Score** - Check resume ATS compatibility
7. **Job Recommendations** ⭐ ENHANCED - `student-job-recommendations.php`
   - AI-powered job matching
   - Skills-based recommendations
   - Match scores and explanations
8. **Job Search** ⭐ REDESIGNED - `student-jobs.php`
   - Modern search interface
   - Live external jobs from Adzuna
   - Enhanced job cards
9. **Applications** - Track job applications
10. **Interviews** - View scheduled interviews
11. **Mock Interview** ⭐ NEW - `student-mock-interview.php`
    - Practice technical interviews
    - Practice behavioral interviews
    - Get AI feedback on answers
    - Track performance
12. **Chat with Seniors** ⭐ NEW - `student-chat.php`
    - Connect with alumni
    - Get career guidance
    - Real-time messaging
13. **Take Quiz** - Skill assessment quizzes
14. **Leaderboard** - See rankings
15. **Seniors Placements** - View alumni placements
16. **Settings** - Account settings

---

### **TPO Dashboard** (`tpo-dashboard.php`)

#### Main Navigation Menu:
1. **Dashboard** - Overview and statistics
2. **Students** - Manage student profiles
3. **Companies** - Manage company accounts
4. **Jobs** ⭐ REDESIGNED - `tpo-jobs.php`
   - View live market jobs from Adzuna
   - Analyze market demand
   - Search by role and location
5. **Placements** - Track student placements
6. **Reports** - Generate reports
7. **Settings** - TPO settings

---

### **COMPANY Dashboard** (`company-dashboard.php`)

#### Main Navigation Menu:
1. **Dashboard** - Company overview
2. **Post Job** - Create new job postings
3. **Jobs** - Manage posted jobs
4. **Applications** - View job applications
5. **Candidates** - Browse candidate profiles
6. **Interviews** - Schedule and manage interviews
7. **Profile** - Company profile management
8. **Settings** - Company settings

---

## 🆕 New Features Quick Access

### 1. **AI Resume Builder**
- **URL:** `http://localhost/Vishwacollab/student-resume-ai.php`
- **Access:** Student Dashboard → AI Resume Builder
- **Features:**
  - Enter job role (e.g., "Software Developer")
  - Generate professional summary
  - Get relevant skills suggestions
  - Download as PDF

### 2. **Chat with Seniors**
- **URL:** `http://localhost/Vishwacollab/student-chat.php`
- **Access:** Student Dashboard → Chat with Seniors
- **Features:**
  - Browse available seniors/alumni
  - Start conversations
  - Real-time messaging
  - View conversation history

### 3. **Enhanced Job Recommendations**
- **URL:** `http://localhost/Vishwacollab/student-job-recommendations.php`
- **Access:** Student Dashboard → Job Recommendations
- **Features:**
  - AI-powered matching
  - Match scores (0-100%)
  - Match explanations
  - Skills highlighting

### 4. **Mock Interview Practice**
- **URL:** `http://localhost/Vishwacollab/student-mock-interview.php`
- **Access:** Student Dashboard → Mock Interview
- **Features:**
  - Choose interview type (Technical/Behavioral/Mixed)
  - Practice questions
  - Submit answers
  - Get AI feedback
  - Track performance

---

## 🔧 Setup Instructions

### 1. Database Setup (Required for Chat & Mock Interview)

Run these scripts in your browser or via command line:

```
http://localhost/Vishwacollab/create_chat_tables.php
http://localhost/Vishwacollab/create_mock_interview_tables.php
```

Or via command line:
```bash
cd C:\xampp\htdocs\Vishwacollab
php create_chat_tables.php
php create_mock_interview_tables.php
```

### 2. Create Test Accounts (If Default Accounts Don't Exist)

#### Create Student Account:
1. Go to: `http://localhost/Vishwacollab/signup.php`
2. Select Role: **Student**
3. Fill in details and register

#### Create TPO Account:
1. Go to: `http://localhost/Vishwacollab/create_tpo_account.php` (if exists)
2. Or use SQL:
```sql
INSERT INTO users (name, email, password_hash, role) 
VALUES ('TPO User', 'tpo@example.com', '$2y$10$...', 'tpo');
```

#### Create Company Account:
1. Go to: `http://localhost/Vishwacollab/signup.php`
2. Select Role: **Company**
3. Fill in company details

### 3. Add Seniors for Chat System

To enable seniors/alumni for chat, run this SQL:

```sql
-- First, ensure you have alumni users
INSERT INTO alumni_students (user_id, current_company, current_position, expertise_areas, is_available_for_chat)
VALUES 
(1, 'Google', 'Software Engineer', 'Web Development, AI/ML', 1),
(2, 'Microsoft', 'Data Scientist', 'Data Analysis, Machine Learning', 1),
(3, 'Amazon', 'Cloud Architect', 'AWS, Cloud Computing', 1);
```

Replace `user_id` with actual user IDs from your `users` table.

---

## 📍 Direct URLs to All Features

### Student Features:
- Dashboard: `http://localhost/Vishwacollab/student-dashboard.php`
- AI Resume Builder: `http://localhost/Vishwacollab/student-resume-ai.php`
- Chat with Seniors: `http://localhost/Vishwacollab/student-chat.php`
- Job Recommendations: `http://localhost/Vishwacollab/student-job-recommendations.php`
- Mock Interview: `http://localhost/Vishwacollab/student-mock-interview.php`
- Job Search: `http://localhost/Vishwacollab/student-jobs.php`
- Applications: `http://localhost/Vishwacollab/student-applications.php`
- Interviews: `http://localhost/Vishwacollab/student-interviews.php`
- Profile: `http://localhost/Vishwacollab/profile.php`

### TPO Features:
- Dashboard: `http://localhost/Vishwacollab/tpo-dashboard.php`
- Jobs (Market Analysis): `http://localhost/Vishwacollab/tpo-jobs.php`
- Students: `http://localhost/Vishwacollab/tpo-students.php`
- Companies: `http://localhost/Vishwacollab/tpo-companies.php`
- Placements: `http://localhost/Vishwacollab/tpo-placements.php`

### Company Features:
- Dashboard: `http://localhost/Vishwacollab/company-dashboard.php`
- Post Job: `http://localhost/Vishwacollab/company-post-job.php`
- Applications: `http://localhost/Vishwacollab/company-applications.php`
- Candidates: `http://localhost/Vishwacollab/company-candidates.php`

---

## 🔐 Creating New Accounts

### Via Signup Page:
1. Go to: `http://localhost/Vishwacollab/signup.php`
2. Select your role (Student/Company)
3. Fill in required information
4. Submit

### Via SQL (For Testing):
```sql
-- Student
INSERT INTO users (name, email, password_hash, role) 
VALUES ('Test Student', 'test@student.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student');

-- Company
INSERT INTO users (name, email, password_hash, role) 
VALUES ('Test Company', 'test@company.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'company');

-- TPO
INSERT INTO users (name, email, password_hash, role) 
VALUES ('Test TPO', 'test@tpo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'tpo');
```

**Default Password Hash:** `password123` (use `password_hash('password123', PASSWORD_DEFAULT)` in PHP)

---

## ⚙️ Configuration

### OpenAI API (Optional - for Enhanced AI Features)
Edit `config.php`:
```php
define('OPENAI_API_KEY', 'your-openai-api-key-here');
```

### Adzuna API (Already Configured)
Located in: `external_jobs_adzuna.php`
- App ID: `eb2290f8`
- App Key: `5cb739f3d1dbc32151095cbcf040dc25`

---

## 🐛 Troubleshooting

### Features Not Showing?
1. **Check Database Tables:** Run setup scripts
2. **Check Navigation:** Ensure menu links are updated
3. **Check Permissions:** Verify user role matches
4. **Clear Cache:** Hard refresh browser (Ctrl+F5)

### Can't Login?
1. Check if user exists in `users` table
2. Verify password hash matches
3. Check session configuration

### Chat Not Working?
1. Run `create_chat_tables.php`
2. Add seniors to `alumni_students` table
3. Set `is_available_for_chat = 1`

### Mock Interview Not Working?
1. Run `create_mock_interview_tables.php`
2. Check browser console for errors
3. Verify JavaScript is enabled

---

## 📞 Support

For issues:
1. Check PHP error logs: `C:\xampp\php\logs\php_error_log`
2. Check database connection in `db_connect.php`
3. Verify all files are uploaded correctly

---

**Last Updated:** <?php echo date('Y-m-d H:i:s'); ?>


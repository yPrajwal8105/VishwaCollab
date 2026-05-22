-- Company + TPO upgrade migration (safe additive changes)
-- Apply with a MySQL client after taking a backup.

SET NAMES utf8mb4;

-- Track status changes and important user actions for auditability.
CREATE TABLE IF NOT EXISTS activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    role ENUM('student','company','tpo') NOT NULL,
    action VARCHAR(120) NOT NULL,
    entity_type VARCHAR(80),
    entity_id INT,
    details TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_role_created (user_id, role, created_at),
    INDEX idx_entity (entity_type, entity_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Notes on applicant profiles from company recruiters.
CREATE TABLE IF NOT EXISTS application_notes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    application_id INT NOT NULL,
    company_user_id INT NOT NULL,
    note TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_application_created (application_id, created_at),
    INDEX idx_company_created (company_user_id, created_at),
    FOREIGN KEY (application_id) REFERENCES applications(id) ON DELETE CASCADE,
    FOREIGN KEY (company_user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Company interview scheduling workflow.
CREATE TABLE IF NOT EXISTS company_interviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    application_id INT NOT NULL UNIQUE,
    company_user_id INT NOT NULL,
    interview_at DATETIME NOT NULL,
    mode ENUM('online','onsite','phone') DEFAULT 'online',
    meeting_link VARCHAR(255) NULL,
    notes TEXT NULL,
    result_status ENUM('scheduled','completed','cancelled') DEFAULT 'scheduled',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_company_interview_at (company_user_id, interview_at),
    FOREIGN KEY (application_id) REFERENCES applications(id) ON DELETE CASCADE,
    FOREIGN KEY (company_user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Company notification center data.
CREATE TABLE IF NOT EXISTS company_notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_user_id INT NOT NULL,
    title VARCHAR(180) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('info','success','warning','error') DEFAULT 'info',
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_company_unread (company_user_id, is_read, created_at),
    FOREIGN KEY (company_user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Query performance for common company/tpo dashboards.
ALTER TABLE applications
    ADD INDEX idx_application_date (application_date),
    ADD INDEX idx_status_date (status, application_date);

ALTER TABLE jobs
    ADD INDEX idx_company_status_posted (company_id, status, posted_at);

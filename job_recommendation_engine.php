<?php
// Enhanced Job Recommendation Engine
// Combines local jobs with external Adzuna jobs and uses AI for better matching

require_once 'external_jobs_adzuna.php';

function calculateJobMatchScore($job, $studentSkills, $studentCourse = '', $studentExperience = '') {
    $score = 0;
    $matchedSkills = [];
    
    // Extract job skills from description/title
    $jobText = strtolower($job['title'] . ' ' . ($job['description'] ?? '') . ' ' . ($job['required_skills'] ?? ''));
    $studentSkillArray = array_map('strtolower', array_map('trim', explode(',', $studentSkills)));
    
    // Skill matching (60% weight)
    foreach ($studentSkillArray as $skill) {
        if (stripos($jobText, $skill) !== false) {
            $matchedSkills[] = $skill;
            $score += 10;
        }
    }
    
    // Course/Education matching (20% weight)
    if (!empty($studentCourse) && stripos($jobText, $studentCourse) !== false) {
        $score += 20;
    }
    
    // Experience level matching (20% weight)
    $experienceKeywords = ['intern', 'trainee', 'junior', 'fresher', 'entry level'];
    $hasExperienceMatch = false;
    foreach ($experienceKeywords as $keyword) {
        if (stripos($jobText, $keyword) !== false) {
            $hasExperienceMatch = true;
            break;
        }
    }
    if ($hasExperienceMatch || empty($studentExperience)) {
        $score += 20;
    }
    
    // Cap score at 100
    $score = min(100, $score);
    
    return [
        'match_score' => $score,
        'matched_skills' => $matchedSkills,
        'reason' => generateMatchReason($score, count($matchedSkills), $job['title'])
    ];
}

function generateMatchReason($score, $matchedSkillsCount, $jobTitle) {
    if ($score >= 80) {
        return "Excellent match! Your skills align perfectly with this {$jobTitle} position.";
    } elseif ($score >= 60) {
        return "Good match! You have {$matchedSkillsCount} relevant skills for this role.";
    } elseif ($score >= 40) {
        return "Moderate match. Consider developing additional skills for this role.";
    } else {
        return "Basic match. This role may require additional training or experience.";
    }
}

function getRecommendedJobs($user_id, $limit = 20) {
    global $conn;
    $recommendedJobs = [];
    
    // Get student profile
    $student = null;
    if (isDatabaseAvailable()) {
        $stmt = $conn->prepare("SELECT name, course, skills, experience, education FROM students WHERE user_id = ? LIMIT 1");
        if ($stmt) {
            $stmt->bind_param('i', $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $student = $result->fetch_assoc();
            $stmt->close();
        }
    }
    
    if (!$student) {
        return [];
    }
    
    $studentSkills = $student['skills'] ?? '';
    $studentCourse = $student['course'] ?? '';
    $studentExperience = $student['experience'] ?? '';
    
    // Get local jobs
    if (isDatabaseAvailable() && $conn) {
        $sql = "SELECT j.*, c.name AS company_name
                FROM jobs j
                LEFT JOIN companies c ON c.id = j.company_id
                WHERE j.status = 'active' OR j.status IS NULL
                ORDER BY j.posted_at DESC
                LIMIT 50";
        if ($result = $conn->query($sql)) {
            while ($job = $result->fetch_assoc()) {
                $matchData = calculateJobMatchScore($job, $studentSkills, $studentCourse, $studentExperience);
                $job['match_score'] = $matchData['match_score'];
                $job['matched_skills'] = $matchData['matched_skills'];
                $job['match_reason'] = $matchData['reason'];
                $job['source'] = 'local';
                $recommendedJobs[] = $job;
            }
            $result->free();
        }
    }
    
    // Get external jobs from Adzuna
    $externalJobs = fetch_adzuna_jobs($studentSkills, 'India', 30);
    foreach ($externalJobs as $job) {
        $matchData = calculateJobMatchScore($job, $studentSkills, $studentCourse, $studentExperience);
        $job['match_score'] = $matchData['match_score'];
        $job['matched_skills'] = $matchData['matched_skills'];
        $job['match_reason'] = $matchData['reason'];
        $job['source'] = 'external';
        $job['company_name'] = $job['company'] ?? '';
        $recommendedJobs[] = $job;
    }
    
    // Sort by match score
    usort($recommendedJobs, function ($a, $b) {
        return $b['match_score'] <=> $a['match_score'];
    });
    
    // Return top matches
    return array_slice($recommendedJobs, 0, $limit);
}
?>


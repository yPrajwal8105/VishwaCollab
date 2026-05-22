<?php
// AI Resume Builder Helper
// Uses OpenAI API to generate resume content based on job role

function generateResumeContent($jobRole, $userSkills, $userExperience, $userEducation) {
    // If OpenAI API key is not configured, return template-based content
    if (empty(OPENAI_API_KEY)) {
        return generateTemplateResume($jobRole, $userSkills, $userExperience, $userEducation);
    }
    
    $prompt = "Create a professional resume summary and key skills section for a {$jobRole} position. 
    
User Information:
- Skills: {$userSkills}
- Experience: {$userExperience}
- Education: {$userEducation}

Generate:
1. A compelling professional summary (2-3 sentences)
2. Key skills relevant to {$jobRole} (5-8 skills)
3. Professional achievements/experience highlights (3-4 bullet points)

Format the response as JSON with keys: summary, skills, achievements";

    $ch = curl_init('https://api.openai.com/v1/chat/completions');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . OPENAI_API_KEY
        ],
        CURLOPT_POSTFIELDS => json_encode([
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                ['role' => 'user', 'content' => $prompt]
            ],
            'temperature' => 0.7,
            'max_tokens' => 500
        ]),
        CURLOPT_TIMEOUT => 30
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200 && $response) {
        $data = json_decode($response, true);
        if (isset($data['choices'][0]['message']['content'])) {
            $content = json_decode($data['choices'][0]['message']['content'], true);
            if ($content) {
                return $content;
            }
        }
    }
    
    // Fallback to template if API fails
    return generateTemplateResume($jobRole, $userSkills, $userExperience, $userEducation);
}

function generateTemplateResume($jobRole, $userSkills, $userExperience, $userEducation) {
    $roleTemplates = [
        'Software Developer' => [
            'summary' => 'Motivated software developer with strong problem-solving skills and passion for creating efficient, scalable solutions. Experienced in modern development practices and collaborative team environments.',
            'skills' => ['Programming Languages', 'Software Development', 'Problem Solving', 'Version Control', 'Testing & Debugging'],
            'achievements' => [
                'Developed and maintained multiple software applications',
                'Collaborated with cross-functional teams to deliver projects',
                'Implemented best practices for code quality and documentation'
            ]
        ],
        'Data Analyst' => [
            'summary' => 'Analytical data professional skilled in extracting insights from complex datasets. Proficient in statistical analysis and data visualization to drive informed business decisions.',
            'skills' => ['Data Analysis', 'Statistical Methods', 'Data Visualization', 'SQL', 'Excel'],
            'achievements' => [
                'Analyzed large datasets to identify key trends and patterns',
                'Created comprehensive reports and visualizations',
                'Supported data-driven decision making processes'
            ]
        ],
        'Web Developer' => [
            'summary' => 'Creative web developer specializing in building responsive, user-friendly websites. Experienced in front-end and back-end technologies with focus on performance and user experience.',
            'skills' => ['HTML/CSS', 'JavaScript', 'Responsive Design', 'Web Frameworks', 'API Integration'],
            'achievements' => [
                'Built responsive websites with modern design principles',
                'Optimized web performance and loading times',
                'Integrated third-party APIs and services'
            ]
        ]
    ];
    
    // Find matching template or use default
    $template = null;
    foreach ($roleTemplates as $key => $value) {
        if (stripos($jobRole, $key) !== false) {
            $template = $value;
            break;
        }
    }
    
    if (!$template) {
        $template = [
            'summary' => "Dedicated professional seeking {$jobRole} position. Strong background in relevant skills and committed to continuous learning and professional growth.",
            'skills' => array_slice(explode(',', $userSkills), 0, 5),
            'achievements' => [
                'Demonstrated strong work ethic and commitment to excellence',
                'Successfully completed relevant projects and coursework',
                'Maintained high standards of quality and professionalism'
            ]
        ];
    }
    
    // Enhance skills with user's actual skills
    if (!empty($userSkills)) {
        $userSkillArray = array_map('trim', explode(',', $userSkills));
        $template['skills'] = array_unique(array_merge($template['skills'], $userSkillArray));
        $template['skills'] = array_slice($template['skills'], 0, 8);
    }
    
    return $template;
}

function getJobRoleSuggestions($searchTerm = '') {
    $suggestions = [
        'Software Developer', 'Web Developer', 'Mobile App Developer',
        'Data Analyst', 'Data Scientist', 'Business Analyst',
        'UI/UX Designer', 'Graphic Designer', 'Product Manager',
        'Marketing Manager', 'Sales Executive', 'Content Writer',
        'DevOps Engineer', 'Cloud Engineer', 'Cybersecurity Analyst',
        'Project Manager', 'Business Development', 'HR Manager',
        'Financial Analyst', 'Accountant', 'Operations Manager'
    ];
    
    if (empty($searchTerm)) {
        return $suggestions;
    }
    
    $filtered = array_filter($suggestions, function($item) use ($searchTerm) {
        return stripos($item, $searchTerm) !== false;
    });
    
    return array_values($filtered);
}
?>


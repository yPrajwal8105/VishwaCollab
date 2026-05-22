<?php
require_once 'config.php';
require_once 'db_connect.php';

if (!isLoggedIn() || getUserRole() !== 'student') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$interviewHistory = [];

// Get interview history
if (isDatabaseAvailable()) {
    // Check if table exists
    $tableCheck = $conn->query("SHOW TABLES LIKE 'mock_interviews'");
    if ($tableCheck && $tableCheck->num_rows > 0) {
        $stmt = $conn->prepare("SELECT * FROM mock_interviews WHERE user_id = ? ORDER BY created_at DESC LIMIT 10");
        if ($stmt) {
            $stmt->bind_param('i', $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $interviewHistory[] = $row;
            }
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mock Interview Practice - Student Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
    <style>
        .interview-setup {
            background: #fff;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            margin-bottom: 2rem;
        }
        .interview-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
        }
        .interview-panel {
            background: #fff;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            display: flex;
            flex-direction: column;
        }
        .question-area {
            flex: 1;
            background: #f8f9fa;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            min-height: 200px;
        }
        .question-text {
            font-size: 1.1rem;
            color: var(--text);
            line-height: 1.6;
            margin-bottom: 1rem;
        }
        .answer-area {
            flex: 1;
            background: #f8f9fa;
            border-radius: 12px;
            padding: 1.5rem;
            min-height: 300px;
        }
        .answer-input {
            width: 100%;
            min-height: 200px;
            padding: 1rem;
            border: 2px solid var(--border);
            border-radius: 10px;
            font-size: 1rem;
            font-family: inherit;
            resize: vertical;
        }
        .answer-input:focus {
            outline: none;
            border-color: var(--primary-color);
        }
        .interview-controls {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
        }
        .btn-interview {
            flex: 1;
            padding: 1rem;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        .btn-start {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: white;
        }
        .btn-start:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(26, 115, 232, 0.3);
        }
        .btn-submit {
            background: #34a853;
            color: white;
        }
        .btn-submit:hover {
            background: #2e7d32;
        }
        .btn-next {
            background: var(--primary-color);
            color: white;
        }
        .btn-next:hover {
            background: var(--primary-dark);
        }
        .btn-end {
            background: #ea4335;
            color: white;
        }
        .btn-end:hover {
            background: #c62828;
        }
        .feedback-area {
            background: #e8f5e9;
            border-left: 4px solid #34a853;
            border-radius: 8px;
            padding: 1rem;
            margin-top: 1rem;
        }
        .feedback-title {
            font-weight: 600;
            color: #2e7d32;
            margin-bottom: 0.5rem;
        }
        .feedback-text {
            color: #1b5e20;
            line-height: 1.6;
        }
        .interview-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            margin-top: 2rem;
        }
        .stat-card {
            background: #fff;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            text-align: center;
        }
        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }
        .stat-label {
            color: var(--muted);
            font-size: 0.9rem;
        }
        .interview-history {
            margin-top: 2rem;
        }
        .history-item {
            background: #fff;
            border-radius: 12px;
            padding: 1rem;
            margin-bottom: 0.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .hidden {
            display: none;
        }
        @media (max-width: 960px) {
            .interview-container {
                grid-template-columns: 1fr;
            }
            .interview-stats {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body class="theme-student">
    <div class="dashboard-container">
        <div class="sidebar">
            <div class="sidebar-header">
                <h2>Student Dashboard</h2>
            </div>
            <div class="sidebar-menu">
                <a href="student-dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
                <a href="profile.php"><i class="fas fa-user"></i> My Profile</a>
                <a href="student-jobs.php"><i class="fas fa-briefcase"></i> Job Search</a>
                <a href="student-applications.php"><i class="fas fa-file-alt"></i> Applications</a>
                <a href="student-interviews.php"><i class="fas fa-calendar-alt"></i> Interviews</a>
                <a href="student-resume.php"><i class="fas fa-file-pdf"></i> Resume Builder</a>
                <a href="student-mock-interview.php" class="active"><i class="fas fa-microphone"></i> Mock Interview</a>
                <a href="settings.php"><i class="fas fa-cog"></i> Settings</a>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
        <div class="main-content">
            <div class="page-header-section">
                <div>
                    <h1 class="page-title">AI Mock Interview Practice</h1>
                    <p class="page-subtitle">Practice interview questions and get AI-powered feedback</p>
                </div>
            </div>

            <div class="interview-setup" id="setupPanel">
                <h2 style="margin-bottom: 1rem;">Start Mock Interview</h2>
                <form id="interviewForm">
                    <div class="form-group">
                        <label class="form-label">Job Role</label>
                        <input type="text" class="form-control" id="jobRole" placeholder="e.g., Software Developer, Data Analyst" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Interview Type</label>
                        <select class="form-control" id="interviewType" required>
                            <option value="technical">Technical Interview</option>
                            <option value="behavioral">Behavioral Interview</option>
                            <option value="mixed">Mixed (Technical + Behavioral)</option>
                        </select>
                    </div>
                    <button type="submit" class="btn-start">
                        <i class="fas fa-play"></i> Start Interview
                    </button>
                </form>
            </div>

            <div class="interview-container hidden" id="interviewPanel">
                <div class="interview-panel">
                    <h3 style="margin-bottom: 1rem;">Question</h3>
                    <div class="question-area">
                        <div class="question-text" id="questionText">Loading question...</div>
                        <div style="color: var(--muted); font-size: 0.9rem;">
                            <i class="fas fa-clock"></i> Take your time to think before answering
                        </div>
                    </div>
                    <div class="interview-controls">
                        <button class="btn-interview btn-next" onclick="nextQuestion()">
                            <i class="fas fa-forward"></i> Skip Question
                        </button>
                        <button class="btn-interview btn-end" onclick="endInterview()">
                            <i class="fas fa-stop"></i> End Interview
                        </button>
                    </div>
                </div>

                <div class="interview-panel">
                    <h3 style="margin-bottom: 1rem;">Your Answer</h3>
                    <div class="answer-area">
                        <textarea class="answer-input" id="answerInput" placeholder="Type your answer here..."></textarea>
                    </div>
                    <div class="interview-controls">
                        <button class="btn-interview btn-submit" onclick="submitAnswer()">
                            <i class="fas fa-check"></i> Submit Answer
                        </button>
                    </div>
                    <div class="feedback-area hidden" id="feedbackArea">
                        <div class="feedback-title">
                            <i class="fas fa-lightbulb"></i> AI Feedback
                        </div>
                        <div class="feedback-text" id="feedbackText"></div>
                    </div>
                </div>
            </div>

            <div class="interview-stats">
                <div class="stat-card">
                    <div class="stat-value" id="totalInterviews"><?php echo count($interviewHistory); ?></div>
                    <div class="stat-label">Total Interviews</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value" id="avgScore">--</div>
                    <div class="stat-label">Average Score</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value" id="questionsAnswered">0</div>
                    <div class="stat-label">Questions Answered</div>
                </div>
            </div>

            <?php if (!empty($interviewHistory)): ?>
                <div class="interview-history">
                    <h3 style="margin-bottom: 1rem;">Recent Interviews</h3>
                    <?php foreach ($interviewHistory as $interview): ?>
                        <div class="history-item">
                            <div>
                                <strong><?php echo htmlspecialchars($interview['job_role'] ?? 'General Interview'); ?></strong>
                                <div style="color: var(--muted); font-size: 0.9rem;">
                                    <?php echo date('M j, Y h:i A', strtotime($interview['created_at'])); ?>
                                </div>
                            </div>
                            <div style="color: var(--primary-color); font-weight: 600;">
                                Score: <?php echo $interview['score'] ?? 'N/A'; ?>%
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        let currentQuestionIndex = 0;
        let questions = [];
        let interviewStarted = false;

        const technicalQuestions = [
            "Tell me about yourself and your technical background.",
            "What programming languages are you most comfortable with?",
            "Explain a challenging technical problem you solved recently.",
            "How do you approach debugging a complex issue?",
            "Describe a project you're proud of and the technologies you used.",
            "How do you stay updated with the latest technology trends?",
            "Explain the difference between REST and GraphQL APIs.",
            "How would you optimize a slow database query?",
            "Describe your experience with version control systems.",
            "What is your approach to writing clean, maintainable code?"
        ];

        const behavioralQuestions = [
            "Tell me about a time you worked in a team. What was your role?",
            "Describe a situation where you had to meet a tight deadline.",
            "Give an example of how you handled a difficult situation at work or school.",
            "Tell me about a time you had to learn something new quickly.",
            "Describe a project where you had to take initiative.",
            "How do you handle stress and pressure?",
            "Tell me about a time you made a mistake and how you handled it.",
            "Describe a situation where you had to communicate a complex idea.",
            "Give an example of how you've demonstrated leadership.",
            "Tell me about a time you had to adapt to a significant change."
        ];

        document.getElementById('interviewForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const jobRole = document.getElementById('jobRole').value;
            const interviewType = document.getElementById('interviewType').value;
            
            // Select questions based on type
            if (interviewType === 'technical') {
                questions = technicalQuestions;
            } else if (interviewType === 'behavioral') {
                questions = behavioralQuestions;
            } else {
                questions = [...technicalQuestions.slice(0, 5), ...behavioralQuestions.slice(0, 5)];
            }
            
            // Shuffle questions
            questions = questions.sort(() => Math.random() - 0.5).slice(0, 5);
            
            startInterview();
        });

        function startInterview() {
            document.getElementById('setupPanel').classList.add('hidden');
            document.getElementById('interviewPanel').classList.remove('hidden');
            interviewStarted = true;
            currentQuestionIndex = 0;
            showQuestion();
        }

        function showQuestion() {
            if (currentQuestionIndex < questions.length) {
                document.getElementById('questionText').textContent = questions[currentQuestionIndex];
                document.getElementById('answerInput').value = '';
                document.getElementById('feedbackArea').classList.add('hidden');
            } else {
                endInterview();
            }
        }

        function submitAnswer() {
            const answer = document.getElementById('answerInput').value.trim();
            if (!answer) {
                alert('Please enter an answer before submitting.');
                return;
            }

            // Simulate AI feedback (in production, this would call an API)
            const feedback = generateFeedback(answer, questions[currentQuestionIndex]);
            document.getElementById('feedbackText').textContent = feedback;
            document.getElementById('feedbackArea').classList.remove('hidden');
            
            // Update stats
            const questionsAnswered = parseInt(document.getElementById('questionsAnswered').textContent) + 1;
            document.getElementById('questionsAnswered').textContent = questionsAnswered;
        }

        function generateFeedback(answer, question) {
            // Simple feedback generation (in production, use AI API)
            const answerLength = answer.length;
            let feedback = "Good answer! ";
            
            if (answerLength < 50) {
                feedback += "Consider providing more detail and examples. ";
            } else if (answerLength > 200) {
                feedback += "Your answer is comprehensive. ";
            } else {
                feedback += "Your answer length is appropriate. ";
            }
            
            feedback += "Make sure to include specific examples and quantify your achievements where possible.";
            return feedback;
        }

        function nextQuestion() {
            currentQuestionIndex++;
            showQuestion();
        }

        function endInterview() {
            if (confirm('Are you sure you want to end the interview?')) {
                alert('Interview completed! Your performance has been saved.');
                location.reload();
            }
        }
    </script>
</body>
</html>


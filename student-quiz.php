<?php
require_once 'config.php';
require_once 'db_connect.php';

if (!isLoggedIn() || getUserRole() !== 'student') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$resume_id = isset($_GET['resume_id']) ? (int) $_GET['resume_id'] : 0;
$error = '';
$message = '';
$resume = null;
$skills = [];
$questions = $_SESSION['quiz_questions'] ?? [];
$results = null;

function ensureConnQuiz()
{
    global $conn;
    if ($conn instanceof mysqli && @$conn->ping()) {
        return $conn;
    }

    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        throw new Exception('Database connection failed: ' . $conn->connect_error);
    }
    $conn->set_charset('utf8');
    return $conn;
}

try {
    ensureConnQuiz();
} catch (Exception $dbEx) {
    $error = $dbEx->getMessage();
}

function getLatestResumeIdQuiz($userId)
{
    global $conn;
    $orderField = 'created_at';
    $columnCheck = $conn->query("SHOW COLUMNS FROM resumes LIKE 'uploaded_at'");
    if ($columnCheck && $columnCheck->num_rows > 0) {
        $orderField = 'uploaded_at';
    }
    $columnCheck && $columnCheck->free();

    $stmt = $conn->prepare("SELECT id FROM resumes WHERE user_id = ? ORDER BY {$orderField} DESC LIMIT 1");
    if (!$stmt) {
        return 0;
    }
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $id = ($result && $result->num_rows > 0) ? (int) $result->fetch_assoc()['id'] : 0;
    $stmt->close();
    return $id;
}

if (!$resume_id && !$error) {
    $resume_id = getLatestResumeIdQuiz($user_id);
    if (!$resume_id) {
        $error = 'Please upload a resume before starting the quiz.';
    }
}

if (!$error) {
    $stmt = $conn->prepare("
        SELECT r.file_name, pr.skills AS parsed_skills
        FROM resumes r
        LEFT JOIN parsed_resume_data pr ON pr.resume_id = r.id
        WHERE r.id = ? AND r.user_id = ?
        LIMIT 1
    ");
    if ($stmt) {
        $stmt->bind_param('ii', $resume_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $result->num_rows > 0) {
            $resume = $result->fetch_assoc();
            $skills = json_decode($resume['parsed_skills'] ?? '[]', true) ?: [];
        } else {
            $error = 'Resume not found.';
        }
        $stmt->close();
    } else {
        $error = 'Failed to fetch resume.';
    }
}

$questionBank = [
    'JavaScript' => [
        [
            'question' => 'Which of the following is NOT a primitive type in JavaScript?',
            'options' => ['Undefined', 'Number', 'Boolean', 'Float'],
            'answer' => 3,
            'explanation' => 'JavaScript does not have a separate float type; Number covers both integers and floats.'
        ],
        [
            'question' => 'What does `Array.prototype.map()` return?',
            'options' => ['The modified original array', 'A new array', 'The length of mapped array', 'A boolean'],
            'answer' => 1,
            'explanation' => 'map() returns a new array with the transformed values.'
        ]
    ],
    'Python' => [
        [
            'question' => 'What is the output of `len(set([1,1,2,3]))`?',
            'options' => ['4', '3', '2', 'Error'],
            'answer' => 1,
            'explanation' => 'set removes duplicates so {1,2,3} has length 3.'
        ],
        [
            'question' => 'Which keyword is used to create a generator in Python?',
            'options' => ['yield', 'return', 'async', 'gen'],
            'answer' => 0,
            'explanation' => 'Generators use the yield keyword.'
        ]
    ],
    'React' => [
        [
            'question' => 'What hook is used to manage component state in functional components?',
            'options' => ['useState', 'useEffect', 'useMemo', 'useReducer'],
            'answer' => 0,
            'explanation' => 'useState returns a state variable and setter.'
        ],
        [
            'question' => 'Which prop should be provided when rendering lists in React?',
            'options' => ['id', 'key', 'value', 'index'],
            'answer' => 1,
            'explanation' => 'React uses the key prop to track list items.'
        ]
    ],
    'HTML' => [
        [
            'question' => 'Which HTML5 element is semantic for navigation links?',
            'options' => ['<section>', '<nav>', '<aside>', '<article>'],
            'answer' => 1,
            'explanation' => '<nav> is used for groups of navigation links.'
        ]
    ],
    'CSS' => [
        [
            'question' => 'Which property controls the stacking order of positioned elements?',
            'options' => ['position', 'display', 'z-index', 'order'],
            'answer' => 2,
            'explanation' => 'z-index controls stacking order.'
        ]
    ],
    'SQL' => [
        [
            'question' => 'Which SQL clause filters rows before grouping?',
            'options' => ['WHERE', 'HAVING', 'GROUP BY', 'ORDER BY'],
            'answer' => 0,
            'explanation' => 'WHERE filters rows before GROUP BY.'
        ]
    ],
];

$fallbackQuestions = [
    [
        'question' => 'Which Git command creates a copy of a repository from a remote source?',
        'options' => ['git push', 'git clone', 'git fork', 'git pull'],
        'answer' => 1,
        'explanation' => 'git clone creates a working copy of a repository.'
    ],
    [
        'question' => 'In Agile, what is the typical duration of a sprint?',
        'options' => ['1-4 weeks', '1 day', '6 months', 'Whenever finished'],
        'answer' => 0,
        'explanation' => 'Sprints usually last one to four weeks.'
    ],
    [
        'question' => 'Which HTTP status code indicates a successful request?',
        'options' => ['200', '301', '404', '500'],
        'answer' => 0,
        'explanation' => '200 OK indicates success.'
    ]
];

function generateQuizQuestions(array $skills, array $bank, array $fallback, int $count = 5): array
{
    $selected = [];
    $usedSkills = [];
    foreach ($skills as $skill) {
        if (isset($bank[$skill])) {
            $pool = $bank[$skill];
            shuffle($pool);
            foreach ($pool as $question) {
                $selected[] = array_merge($question, ['skill' => $skill]);
                break;
            }
            $usedSkills[] = $skill;
            if (count($selected) >= $count) {
                break;
            }
        }
    }

    if (count($selected) < $count) {
        shuffle($fallback);
        foreach ($fallback as $question) {
            $selected[] = array_merge($question, ['skill' => 'General']);
            if (count($selected) >= $count) {
                break;
            }
        }
    }

    return array_slice($selected, 0, $count);
}

function tableExists($conn, $table)
{
    if (!$conn) {
        return false;
    }
    $tableEsc = $conn->real_escape_string($table);
    $result = $conn->query("SHOW TABLES LIKE '{$tableEsc}'");
    $exists = $result && $result->num_rows > 0;
    if ($result) {
        $result->free();
    }
    return $exists;
}

if (!$error && isset($_POST['start_quiz'])) {
    if (!$skills) {
        $error = 'No skills were extracted from your resume. Please upload a richer resume.';
    } else {
        $questions = generateQuizQuestions($skills, $questionBank, $fallbackQuestions);
        $_SESSION['quiz_questions'] = $questions;
        $message = 'Quiz started! Answer the questions below.';
    }
}

if (!$error && isset($_POST['submit_quiz']) && $questions) {
    $answers = $_POST['answers'] ?? [];
    $correct = 0;
    $feedback = [];

    foreach ($questions as $index => $question) {
        $userAnswer = isset($answers[$index]) ? (int) $answers[$index] : -1;
        $isCorrect = $userAnswer === (int) $question['answer'];
        if ($isCorrect) {
            $correct++;
        }
        $feedback[] = [
            'question' => $question['question'],
            'skill' => $question['skill'],
            'is_correct' => $isCorrect,
            'correct_option' => $question['options'][$question['answer']],
            'selected_option' => $userAnswer >= 0 ? ($question['options'][$userAnswer] ?? 'N/A') : 'Not answered',
            'explanation' => $question['explanation'],
        ];
    }

    $scorePercent = round(($correct / count($questions)) * 100);
    $results = [
        'correct' => $correct,
        'total' => count($questions),
        'scorePercent' => $scorePercent,
        'feedback' => $feedback,
    ];

    // Attempt to store results if table exists
    if (tableExists($conn, 'quiz_results')) {
        $stmt = $conn->prepare("
            INSERT INTO quiz_results (user_id, role, score, total_questions, correct_answers, time_taken, answers)
            VALUES (?, 'student', ?, ?, ?, 0, ?)
        ");
        if ($stmt) {
            $answersJson = json_encode($feedback);
            $scoreDecimal = (float) $scorePercent;
            $totalQ = (int) $results['total'];
            $correctAns = (int) $results['correct'];
            $stmt->bind_param('idiis', $user_id, $scoreDecimal, $totalQ, $correctAns, $answersJson);
            $stmt->execute();
            $stmt->close();
        }
    }

    unset($_SESSION['quiz_questions']);
    $questions = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Skill Quiz - VishwaCollab</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f7fa; margin: 0; padding: 20px; }
        .container { max-width: 900px; margin: 0 auto; background: #fff; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.08); }
        .message { padding: 15px; border-radius: 6px; margin-bottom: 20px; }
        .error { background: #ffebee; color: #c62828; border-left: 4px solid #c62828; }
        .success { background: #e8f5e9; color: #2e7d32; border-left: 4px solid #2e7d32; }
        .btn { background: #1a73e8; color: #fff; padding: 12px 24px; border: none; border-radius: 6px; cursor: pointer; font-size: 16px; text-decoration: none; display: inline-block; }
        .btn-secondary { background: #666; }
        .question-card { margin-bottom: 20px; padding: 15px; border: 1px solid #e5e7eb; border-radius: 8px; background: #f9fafb; }
        .feedback { margin-top: 20px; padding: 15px; border-radius: 8px; background: #f0fdf4; }
        label.option { display: block; margin: 8px 0; padding: 8px 12px; border-radius: 6px; border: 1px solid #d1d5db; cursor: pointer; }
        label.option input { margin-right: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <div style="display:flex; justify-content:space-between; align-items:center;">
            <div>
                <h1>Skill Quiz</h1>
                <?php if ($resume): ?>
                    <p style="color:#555;">Based on: <strong><?php echo htmlspecialchars($resume['file_name']); ?></strong></p>
                <?php endif; ?>
            </div>
            <a href="student-resume-upload.php" class="btn btn-secondary">⬅ Back</a>
        </div>

        <?php if ($error): ?>
            <div class="message error"><?php echo htmlspecialchars($error); ?></div>
        <?php elseif ($message): ?>
            <div class="message success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <?php if (!$questions && !$results): ?>
            <p>Generate a personalized quiz based on your extracted skills. You'll get 5 quick questions to test your knowledge.</p>
            <form method="POST">
                <button type="submit" name="start_quiz" class="btn">Start Quiz</button>
            </form>
        <?php endif; ?>

        <?php if ($questions): ?>
            <form method="POST">
                <?php foreach ($questions as $index => $question): ?>
                    <div class="question-card">
                        <p style="margin:0; color:#555;">Skill: <strong><?php echo htmlspecialchars($question['skill']); ?></strong></p>
                        <h3 style="margin:10px 0;"><?php echo ($index + 1) . '. ' . htmlspecialchars($question['question']); ?></h3>
                        <?php foreach ($question['options'] as $optionIndex => $option): ?>
                            <label class="option">
                                <input type="radio" name="answers[<?php echo $index; ?>]" value="<?php echo $optionIndex; ?>">
                                <?php echo htmlspecialchars($option); ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
                <button type="submit" name="submit_quiz" class="btn">Submit Answers</button>
            </form>
        <?php endif; ?>

        <?php if ($results): ?>
            <div class="message success">
                You scored <strong><?php echo $results['scorePercent']; ?>%</strong> (<?php echo $results['correct']; ?> / <?php echo $results['total']; ?> correct)
            </div>
            <?php foreach ($results['feedback'] as $item): ?>
                <div class="feedback" style="background: <?php echo $item['is_correct'] ? '#e8f5e9' : '#fff5f5'; ?>;">
                    <p style="margin:0;color:#555;">Skill: <strong><?php echo htmlspecialchars($item['skill']); ?></strong></p>
                    <p><strong><?php echo htmlspecialchars($item['question']); ?></strong></p>
                    <p>Your answer: <?php echo htmlspecialchars($item['selected_option']); ?></p>
                    <p>Correct answer: <?php echo htmlspecialchars($item['correct_option']); ?></p>
                    <p style="color:#555;"><?php echo htmlspecialchars($item['explanation']); ?></p>
                </div>
            <?php endforeach; ?>
            <div style="margin-top:20px;">
                <a href="?resume_id=<?php echo $resume_id; ?>" class="btn">Retake Quiz</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>


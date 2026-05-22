<?php
require_once 'config.php';
require_once 'db_connect.php';

// Set content type to JSON
header('Content-Type: application/json');

// Initialize response
$response = [
    'success' => false,
    'total_results' => 0,
    'jobs' => [],
    'companies' => [],
    'students' => [],
    'error' => null
];

// Check if this is a search request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'search') {
    $searchTerm = trim($_POST['search_term']);
    
    if (empty($searchTerm)) {
        $response['error'] = 'Search term is required';
        echo json_encode($response);
        exit();
    }
    
    // Check if database is available
    if (!isDatabaseAvailable()) {
        $response['error'] = 'Database connection failed';
        echo json_encode($response);
        exit();
    }
    
    try {
        // Search jobs
        $jobsQuery = "SELECT j.*, COALESCE(c.name, 'Unknown Company') as company_name 
                     FROM jobs j 
                     LEFT JOIN companies c ON j.company_id = c.id 
                     WHERE j.title LIKE ? OR j.description LIKE ? OR j.requirements LIKE ? OR COALESCE(c.name, '') LIKE ?
                     ORDER BY j.created_at DESC 
                     LIMIT 10";
        $searchPattern = "%{$searchTerm}%";
        $stmt = $conn->prepare($jobsQuery);
        $stmt->bind_param("ssss", $searchPattern, $searchPattern, $searchPattern, $searchPattern);
        $stmt->execute();
        $jobsResult = $stmt->get_result();
        
        while ($job = $jobsResult->fetch_assoc()) {
            $response['jobs'][] = $job;
        }
        
        // Search companies
        $companiesQuery = "SELECT * FROM companies 
                          WHERE name LIKE ? OR industry LIKE ? OR location LIKE ?
                          ORDER BY created_at DESC 
                          LIMIT 10";
        $stmt = $conn->prepare($companiesQuery);
        $stmt->bind_param("sss", $searchPattern, $searchPattern, $searchPattern);
        $stmt->execute();
        $companiesResult = $stmt->get_result();
        
        while ($company = $companiesResult->fetch_assoc()) {
            $response['companies'][] = $company;
        }
        
        // Search students
        $studentsQuery = "SELECT * FROM students 
                         WHERE name LIKE ? OR course LIKE ? OR skills LIKE ?
                         ORDER BY created_at DESC 
                         LIMIT 10";
        $stmt = $conn->prepare($studentsQuery);
        $stmt->bind_param("sss", $searchPattern, $searchPattern, $searchPattern);
        $stmt->execute();
        $studentsResult = $stmt->get_result();
        
        while ($student = $studentsResult->fetch_assoc()) {
            $response['students'][] = $student;
        }
        
        // Calculate total results
        $response['total_results'] = count($response['jobs']) + count($response['companies']) + count($response['students']);
        $response['success'] = true;
        
    } catch (Exception $e) {
        $response['error'] = 'Search failed: ' . $e->getMessage();
    }
} else {
    $response['error'] = 'Invalid request';
}

// Return JSON response
echo json_encode($response);
?>

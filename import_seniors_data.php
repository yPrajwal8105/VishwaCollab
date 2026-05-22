<?php
$_SERVER['SERVER_NAME'] = 'localhost';
require_once 'config.php';
require_once 'db_connect.php';

if (!isDatabaseAvailable()) {
    die("Database connection failed.\n");
}

$create_table = "CREATE TABLE IF NOT EXISTS alumni_placements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_name VARCHAR(100) NOT NULL,
    student_email VARCHAR(100),
    course VARCHAR(100),
    batch_year YEAR,
    company_name VARCHAR(200) NOT NULL,
    job_title VARCHAR(200) NOT NULL,
    salary VARCHAR(50),
    location VARCHAR(100),
    placement_date DATE,
    cgpa DECIMAL(3,2),
    skills TEXT,
    additional_info TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";
$conn->query($create_table);

// Clear old data
$conn->query("TRUNCATE TABLE alumni_placements");

$file = 'seniors_data.tsv';
if (!file_exists($file)) {
    die("File not found: $file\n");
}

$handle = fopen($file, 'r');
if ($handle !== false) {
    // Skip header
    $header = fgetcsv($handle, 0, "\t");

    $stmt = $conn->prepare("INSERT INTO alumni_placements 
        (student_name, student_email, course, batch_year, company_name, job_title, salary, location, placement_date, cgpa, skills, additional_info) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $count = 0;
    while (($data = fgetcsv($handle, 0, "\t")) !== false) {
        $nonEmpty = array_filter($data, function ($v) {
            return trim((string)$v) !== '';
        });
        if (count($nonEmpty) === 0) continue;

        // TSV Columns:
        // 0: Sr.No, 1: PRN, 2: Student_Name, 3: Division, 4: Roll No., 5: WhatsApp, 6: Email, 
        // 7: Company Name, 8: Company Mentor, 9: Mentor Email, 10: Type of Internship, 11: Nat/Int, 
        // 12: Source, 13: Period, 14: Start Date, 15: End Date, 16: Duration, 17: Stipend, 18: Offer, 19: Guide, 20: Column1

        $student_name = trim($data[2] ?? '');
        $student_email = trim($data[6] ?? '');
        $course = trim($data[3] ?? '');
        $company_name = trim($data[7] ?? '');
        $job_title = trim($data[10] ?? 'Industry Internship'); // Type of Internship
        if ($job_title === '') $job_title = 'Industry Internship';

        $batch_year = null;
        if ($student_email !== '' && preg_match('/(\d{2})@/', $student_email, $matches)) {
            $year_suffix = (int)$matches[1];
            if ($year_suffix >= 0 && $year_suffix <= 99) {
                $batch_year = 2000 + $year_suffix;
                // Add 4 to the joining year for standard B.Tech batch year
                if (strpos(strtolower($course), 'btech') !== false || strpos(strtolower($course), 'ty') !== false) {
                    $batch_year += 4;
                }
            }
        }
        
        $salary = trim($data[17] ?? '');
        $location = '';
        $placement_date = null;
        if (!empty($data[14])) {
            $timestamp = strtotime(trim($data[14]));
            if ($timestamp !== false) {
                $placement_date = date('Y-m-d', $timestamp);
            }
        }

        $cgpa = null;
        $skills = '';
        $additional_info = trim($data[11] ?? ''); // Nat/Int Internship

        if ($student_name !== '' && $company_name !== '') {
            $stmt->bind_param("sssssssssdss",
                $student_name, $student_email, $course, $batch_year, 
                $company_name, $job_title, $salary, $location, 
                $placement_date, $cgpa, $skills, $additional_info);
            
            if ($stmt->execute()) {
                $count++;
            } else {
                echo "Failed to insert $student_name: " . $stmt->error . "\n";
            }
        }
    }
    fclose($handle);
    echo "Successfully imported $count placement records.\n";
} else {
    echo "Could not open $file\n";
}
?>

<?php
/**
 * Resume Parser for PDF and DOCX files
 * Requires: composer require smalot/pdfparser (for PDF)
 *           composer require phpword/phpword (for DOCX)
 */

class ResumeParser {
    
    /**
     * Parse resume file and extract information
     */
    public static function parseResume($filePath, $fileType) {
        $rawText = '';
        
        try {
            if ($fileType === 'application/pdf' || pathinfo($filePath, PATHINFO_EXTENSION) === 'pdf') {
                $rawText = self::parsePDF($filePath);
            } elseif ($fileType === 'application/vnd.openxmlformats-officedocument.wordprocessingml.document' || 
                      pathinfo($filePath, PATHINFO_EXTENSION) === 'docx') {
                $rawText = self::parseDOCX($filePath);
            } else {
                throw new Exception('Unsupported file type');
            }
            
            return self::extractData($rawText);
        } catch (Exception $e) {
            error_log("Resume parsing error: " . $e->getMessage());
            throw new Exception('Failed to parse resume: ' . $e->getMessage());
        }
    }
    
    /**
     * Parse PDF file
     */
    private static function parsePDF($filePath) {
        // Try to load composer autoloader if available
        $composerAutoload = __DIR__ . '/../vendor/autoload.php';
        if (file_exists($composerAutoload)) {
            require_once $composerAutoload;
        }
        
        // Method 1: Use smalot/pdfparser library (best option)
        if (class_exists('Smalot\PdfParser\Parser')) {
            try {
                $parser = new \Smalot\PdfParser\Parser();
                $pdf = $parser->parseFile($filePath);
                $text = $pdf->getText();
                if (!empty(trim($text))) {
                    return $text;
                }
            } catch (Exception $e) {
                error_log("PDF Parser error: " . $e->getMessage());
            }
        }
        
        // Method 2: Try pdftotext command (if available on system)
        if (function_exists('shell_exec') && !ini_get('safe_mode')) {
            // Try Windows path first (common XAMPP location)
            $pdftotextPaths = [
                'pdftotext',
                'C:\\xampp\\poppler\\bin\\pdftotext.exe',
                'C:\\Program Files\\poppler\\bin\\pdftotext.exe',
            ];
            
            foreach ($pdftotextPaths as $pdftotext) {
                $command = escapeshellarg($pdftotext) . " " . escapeshellarg($filePath) . " -";
                $text = @shell_exec($command);
                if ($text && trim($text) !== '') {
                    return $text;
                }
            }
        }
        
        // Method 3: Basic PDF text extraction (very limited, extracts readable text only)
        $text = self::extractTextFromPDFBasic($filePath);
        if (!empty(trim($text))) {
            return $text;
        }
        
        // Last resort: provide helpful error message
        $errorMsg = 'PDF parsing not available. ';
        $errorMsg .= 'Please install Composer and run: composer install ';
        $errorMsg .= 'OR convert your PDF to DOCX format and upload that instead.';
        throw new Exception($errorMsg);
    }
    
    /**
     * Basic PDF text extraction (very limited - only works with text-based PDFs)
     * This is a fallback method that tries to extract readable text from PDF
     */
    private static function extractTextFromPDFBasic($filePath) {
        if (!file_exists($filePath) || !is_readable($filePath)) {
            return '';
        }
        
        $content = @file_get_contents($filePath);
        if ($content === false) {
            return '';
        }
        
        // Try to extract text from PDF stream objects
        // This is a very basic method and may not work for all PDFs
        $text = '';
        
        // Look for text objects in PDF (between BT and ET markers)
        if (preg_match_all('/BT\s+(.*?)\s+ET/s', $content, $matches)) {
            foreach ($matches[1] as $match) {
                // Extract text between parentheses (common PDF text encoding)
                if (preg_match_all('/\((.*?)\)/', $match, $textMatches)) {
                    foreach ($textMatches[1] as $textMatch) {
                        $text .= $textMatch . ' ';
                    }
                }
                // Extract text between brackets (another PDF text encoding)
                if (preg_match_all('/\[(.*?)\]/', $match, $textMatches)) {
                    foreach ($textMatches[1] as $textMatch) {
                        $text .= $textMatch . ' ';
                    }
                }
            }
        }
        
        // Also try to extract readable ASCII text directly
        if (empty($text)) {
            // Remove binary data and keep only printable ASCII
            $text = preg_replace('/[^\x20-\x7E\n\r\t]/', '', $content);
            // Remove PDF structure keywords but keep text
            $text = preg_replace('/\b(stream|endstream|obj|endobj|xref|trailer|startxref|PDF|xref|trailer)\b/i', '', $text);
            // Clean up excessive whitespace
            $text = preg_replace('/\s+/', ' ', $text);
        }
        
        return trim($text);
    }
    
    /**
     * Parse DOCX file
     */
    private static function parseDOCX($filePath) {
        // Try to load composer autoloader if available
        $composerAutoload = __DIR__ . '/../vendor/autoload.php';
        if (file_exists($composerAutoload)) {
            require_once $composerAutoload;
        }
        
        // Simple DOCX text extraction
        // For better results, install: composer require phpoffice/phpword
        if (class_exists('PhpOffice\PhpWord\IOFactory')) {
            try {
                $phpWord = \PhpOffice\PhpWord\IOFactory::load($filePath);
                $text = '';
                foreach ($phpWord->getSections() as $section) {
                    foreach ($section->getElements() as $element) {
                        if (method_exists($element, 'getText')) {
                            $text .= $element->getText() . "\n";
                        }
                    }
                }
                if (!empty(trim($text))) {
                    return $text;
                }
            } catch (Exception $e) {
                error_log("PHPWord error: " . $e->getMessage());
            }
        }
        
        // Fallback: extract from XML (basic method)
        if (class_exists('ZipArchive')) {
            $zip = new ZipArchive();
            if ($zip->open($filePath) === TRUE) {
                $text = $zip->getFromName('word/document.xml');
                $zip->close();
                if ($text) {
                    // Remove XML tags and decode entities
                    $text = strip_tags($text);
                    $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
                    // Clean up extra whitespace
                    $text = preg_replace('/\s+/', ' ', $text);
                    if (!empty(trim($text))) {
                        return $text;
                    }
                }
            }
        }
        
        throw new Exception('DOCX parsing not available. Please install phpword library using: composer require phpoffice/phpword');
    }
    
    /**
     * Extract structured data from raw text
     */
    private static function extractData($text) {
        $data = [
            'raw_text' => $text,
            'work_experience' => self::extractWorkExperience($text),
            'skills' => self::extractSkills($text),
            'education' => self::extractEducation($text),
            'projects' => self::extractProjects($text)
        ];
        
        return $data;
    }
    
    /**
     * Extract skills from resume text
     */
    private static function extractSkills($text) {
        $skills = [];
        $skillKeywords = [
            'JavaScript', 'TypeScript', 'Python', 'Java', 'C++', 'C#', 'Go', 'Rust',
            'React', 'Vue', 'Angular', 'Node.js', 'Express', 'Next.js', 'Django', 'Flask',
            'MongoDB', 'PostgreSQL', 'MySQL', 'Redis', 'AWS', 'Docker', 'Kubernetes',
            'Git', 'CI/CD', 'Machine Learning', 'Data Science', 'TensorFlow', 'PyTorch',
            'HTML', 'CSS', 'SASS', 'TailwindCSS', 'Bootstrap', 'GraphQL', 'REST API',
            'Agile', 'Scrum', 'DevOps', 'Linux', 'System Design', 'Microservices'
        ];
        
        $lowerText = strtolower($text);
        foreach ($skillKeywords as $skill) {
            if (stripos($text, $skill) !== false) {
                $skills[] = $skill;
            }
        }
        
        // Also look for skills section
        if (preg_match('/(?:skills?|technical skills?|technologies?)[:]\s*([^\n]+(?:\n[^\n]+)*?)(?=\n\n|\n[A-Z]|$)/i', $text, $matches)) {
            $skillsText = $matches[1];
            $skillsList = preg_split('/[,;|•\-\n]/', $skillsText);
            foreach ($skillsList as $skill) {
                $skill = trim($skill);
                if (!empty($skill) && strlen($skill) > 2) {
                    $skills[] = $skill;
                }
            }
        }
        
        return array_unique($skills);
    }
    
    /**
     * Extract work experience
     */
    private static function extractWorkExperience($text) {
        $experience = [];
        
        if (preg_match('/(?:experience|work experience|employment|professional experience)[:]\s*(.*?)(?=\n\n(?:education|projects|skills)|$)/is', $text, $matches)) {
            $expText = $matches[1];
            $entries = preg_split('/\n(?=[A-Z])/', $expText);
            
            foreach ($entries as $entry) {
                $lines = array_filter(array_map('trim', explode("\n", $entry)));
                if (count($lines) >= 2) {
                    $firstLine = $lines[0];
                    if (preg_match('/^(.+?)(?:\s+at\s+|\s+@\s+|\s+-\s+)(.+)$/i', $firstLine, $posMatch)) {
                        $experience[] = [
                            'position' => trim($posMatch[1]),
                            'company' => trim($posMatch[2]),
                            'duration' => $lines[1] ?? '',
                            'description' => implode(' ', array_slice($lines, 2))
                        ];
                    }
                }
            }
        }
        
        return $experience;
    }
    
    /**
     * Extract education
     */
    private static function extractEducation($text) {
        $education = [];
        
        if (preg_match('/(?:education|academic|qualifications?)[:]\s*(.*?)(?=\n\n(?:experience|projects|skills)|$)/is', $text, $matches)) {
            $eduText = $matches[1];
            $entries = preg_split('/\n(?=[A-Z])/', $eduText);
            
            foreach ($entries as $entry) {
                $lines = array_filter(array_map('trim', explode("\n", $entry)));
                if (count($lines) >= 1) {
                    $firstLine = $lines[0];
                    if (preg_match('/(.+?)(?:\s+from\s+|\s+@\s+|\s+-\s+)(.+)$/i', $firstLine, $degMatch)) {
                        $education[] = [
                            'degree' => trim($degMatch[1]),
                            'institution' => trim($degMatch[2]),
                            'field' => $lines[1] ?? '',
                            'year' => $lines[2] ?? ''
                        ];
                    }
                }
            }
        }
        
        return $education;
    }
    
    /**
     * Extract projects
     */
    private static function extractProjects($text) {
        $projects = [];
        
        if (preg_match('/(?:projects?|personal projects?)[:]\s*(.*?)(?=\n\n(?:experience|education|skills)|$)/is', $text, $matches)) {
            $projText = $matches[1];
            $entries = preg_split('/\n(?=[A-Z])/', $projText);
            
            foreach ($entries as $entry) {
                $lines = array_filter(array_map('trim', explode("\n", $entry)));
                if (count($lines) >= 1) {
                    $projects[] = [
                        'name' => $lines[0],
                        'description' => implode(' ', array_slice($lines, 1)),
                        'technologies' => self::extractSkills($entry)
                    ];
                }
            }
        }
        
        return $projects;
    }
}
?>




<?php
require_once 'database.php';

// First, clear existing data
try {
    $pdo->exec("DELETE FROM rvu_codes");
    echo "Cleared existing data.\n";
} catch (PDOException $e) {
    die("Error clearing table: " . $e->getMessage());
}

// Prepare the insert statement
$stmt = $pdo->prepare("INSERT INTO rvu_codes (code, description, work_rvu, facility_pe_rvu, non_facility_pe_rvu, mp_rvu) 
                      VALUES (:code, :description, :work_rvu, :facility_pe_rvu, :non_facility_pe_rvu, :mp_rvu)");

// Function to clean and truncate text fields
function cleanField($text, $maxLength = null) {
    // Remove multiple spaces and trim
    $text = preg_replace('/\s+/', ' ', trim($text));
    if ($maxLength) {
        $text = substr($text, 0, $maxLength);
    }
    return $text;
}

// Extract text from PDF using pdftotext with layout preservation
$pdf_file = 'effective_ june_30_2020_cpt_hcpcs_ada_and_owcp_codes_with_rvu_and_conversion_ factors_ 2.pdf';
$txt_file = 'extracted_codes.txt';

// Extract text from PDF with layout preservation and show the command being executed
$cmd = "pdftotext -layout -nopgbrk '$pdf_file' '$txt_file' 2>&1";
echo "Executing: $cmd\n";
$output = [];
exec($cmd, $output, $return_var);

if ($return_var !== 0) {
    echo "Command output:\n" . implode("\n", $output) . "\n";
    die("Failed to extract text from PDF. Error code: $return_var");
}

if (!file_exists($txt_file)) {
    die("Failed to extract text from PDF - output file not created");
}

// Read the extracted text file
$content = file_get_contents($txt_file);
if ($content === false) {
    die("Failed to read extracted text file");
}

echo "File contents loaded. Size: " . strlen($content) . " bytes\n";

// Split into lines and normalize line endings
$lines = explode("\n", str_replace("\r", "", $content));
echo "Number of lines: " . count($lines) . "\n\n";

// Process lines to handle wrapped content
$processedLines = [];
$currentLine = '';

foreach ($lines as $line) {
    $line = rtrim($line);
    
    // If line starts with a code pattern (5 digits), it's a new entry
    if (preg_match('/^\s*\d{5}\s+/', $line)) {
        if (!empty($currentLine)) {
            $processedLines[] = $currentLine;
        }
        $currentLine = $line;
    } else {
        // Append non-empty lines to current line
        if (!empty(trim($line))) {
            $currentLine .= ' ' . trim($line);
        }
    }
}
// Add the last line
if (!empty($currentLine)) {
    $processedLines[] = $currentLine;
}

echo "Processed " . count($processedLines) . " entries\n\n";

// Debug: Show first few processed lines
echo "First 5 processed lines:\n";
for ($i = 0; $i < 5 && $i < count($processedLines); $i++) {
    echo "Entry $i: [{$processedLines[$i]}]\n";
}
echo "\n";

$headerFound = false;
$count = 0;
$zero_rvu_count = 0;
$non_zero_rvu_count = 0;
$total_work_rvu = 0;
$max_work_rvu = 0;
$max_work_rvu_code = '';

foreach ($processedLines as $line) {
    // Skip empty lines
    if (empty(trim($line))) continue;
    
    // Look for the header line
    if (!$headerFound) {
        if (strpos($line, 'CPT') !== false && strpos($line, 'SHORT DESCRIPTION') !== false) {
            $headerFound = true;
            continue;
        }
        continue;
    }
    
    // Skip header continuation lines
    if (strpos($line, 'END DATE') !== false) continue;
    
    // Try to match the full line pattern
    if (preg_match('/^\s*(\d{5})\s+([A-Z])\s+(\d+\.\d+|\d+)\s+(\d+\.\d+|\d+)\s+(\d+\.\d+|\d+)\s+(\d+\.\d+|\d+)\s+[A-Z]+\s+(\d+\.\d+|\d+)\s+(.+)$/', $line, $matches)) {
        try {
            $work_rvu = floatval(trim($matches[3]));
            
            // Update statistics
            if ($work_rvu == 0) {
                $zero_rvu_count++;
            } else {
                $non_zero_rvu_count++;
                $total_work_rvu += $work_rvu;
                if ($work_rvu > $max_work_rvu) {
                    $max_work_rvu = $work_rvu;
                    $max_work_rvu_code = $matches[1];
                }
            }
            
            $data = [
                'code' => $matches[1],
                'description' => cleanField($matches[8], 255),
                'work_rvu' => $work_rvu,
                'facility_pe_rvu' => floatval(trim($matches[5])),
                'non_facility_pe_rvu' => floatval(trim($matches[4])),
                'mp_rvu' => floatval(trim($matches[6]))
            ];
            
            if ($count < 5) {
                echo "Parsed data: " . json_encode($data) . "\n";
            }
            
            $stmt->execute($data);
            $count++;
            
            if ($count % 100 === 0) {
                echo "Imported {$count} codes...\n";
            }
        } catch (PDOException $e) {
            echo "Error importing code {$matches[1]}: " . $e->getMessage() . "\n";
        }
    }
}

// Clean up
// unlink($txt_file);

echo "\nImport completed successfully!\n";
echo "Total codes imported: {$count}\n";
echo "Codes with zero work RVU: {$zero_rvu_count}\n";
echo "Codes with non-zero work RVU: {$non_zero_rvu_count}\n";
echo "Average work RVU (excluding zeros): " . ($non_zero_rvu_count > 0 ? number_format($total_work_rvu / $non_zero_rvu_count, 2) : 0) . "\n";
echo "Maximum work RVU: {$max_work_rvu} (Code: {$max_work_rvu_code})\n\n";

// Print sample entries
echo "\nSample entries from database:\n";
$stmt = $pdo->query("SELECT * FROM rvu_codes LIMIT 5");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    print_r($row);
} 
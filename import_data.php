<?php
ini_set('memory_limit', '1024M');  // Increase memory limit to 1GB
gc_enable();  // Enable garbage collection
require 'vendor/autoload.php';
require_once 'database.php';

use Smalot\PdfParser\Parser;

// First, let's clear existing data
try {
    $pdo->exec("TRUNCATE TABLE rvu_codes");
    echo "Cleared existing data.\n";
} catch (Exception $e) {
    die("Error clearing table: " . $e->getMessage());
}

// Function to clean and validate a line of data
function parseLine($line) {
    // Remove multiple spaces and trim
    $line = preg_replace('/\s+/', ' ', trim($line));
    
    // Different patterns to match various formats in the PDF
    $patterns = [
        // Pattern for standard format: code description work_rvu facility_pe non_facility_pe mp_rvu
        '/^(\d{5})\s+(.+?)\s+([\d.]+)\s+([\d.]+)\s+([\d.]+)\s+([\d.]+)(?:\s|$)/',
        // Pattern for codes with different format (adjust based on actual PDF format)
        '/^(\d{5})\s+(.+?)\s+([\d.NA]+)\s+([\d.NA]+)\s+([\d.NA]+)\s+([\d.NA]+)(?:\s|$)/'
    ];
    
    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $line, $matches)) {
            // Convert NA to null
            for ($i = 3; $i <= 6; $i++) {
                if (isset($matches[$i])) {
                    $matches[$i] = ($matches[$i] === 'NA') ? null : $matches[$i];
                }
            }
            
            return [
                'code' => $matches[1],
                'description' => $matches[2],
                'work_rvu' => $matches[3] ?? null,
                'facility_pe_rvu' => $matches[4] ?? null,
                'non_facility_pe_rvu' => $matches[5] ?? null,
                'mp_rvu' => $matches[6] ?? null
            ];
        }
    }
    return null;
}

try {
    // Open the file in binary mode
    $file = fopen('effective_ june_30_2020_cpt_hcpcs_ada_and_owcp_codes_with_rvu_and_conversion_ factors_ 2.pdf', 'rb');
    if (!$file) {
        die("Could not open PDF file");
    }

    // Read file in chunks
    $chunk = '';
    $buffer_size = 8192; // 8KB chunks
    $line_buffer = '';
    $count = 0;
    $stmt = $pdo->prepare("INSERT INTO rvu_codes (code, description, work_rvu, facility_pe_rvu, non_facility_pe_rvu, mp_rvu) 
                          VALUES (:code, :description, :work_rvu, :facility_pe_rvu, :non_facility_pe_rvu, :mp_rvu)");

    while (!feof($file)) {
        $chunk = fread($file, $buffer_size);
        $line_buffer .= $chunk;
        
        // Process complete lines
        while (($pos = strpos($line_buffer, "\n")) !== false) {
            $line = substr($line_buffer, 0, $pos);
            $line_buffer = substr($line_buffer, $pos + 1);
            
            $data = parseLine($line);
            if ($data) {
                try {
                    $stmt->execute($data);
                    $count++;
                    if ($count % 100 === 0) {
                        echo "Imported {$count} codes...\n";
                        gc_collect_cycles();
                    }
                } catch (PDOException $e) {
                    echo "Error importing code {$data['code']}: " . $e->getMessage() . "\n";
                }
            }
        }
    }
    
    fclose($file);
    echo "Import completed successfully! Total codes imported: {$count}\n";
    
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
} 
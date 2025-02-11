<?php
require_once 'database.php';

try {
    // Fetch all records from the database
    $stmt = $pdo->query("SELECT code, description, work_rvu, facility_pe_rvu, non_facility_pe_rvu, mp_rvu FROM rvu_codes");
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Convert to JSON
    $json = json_encode($data, JSON_PRETTY_PRINT);
    
    // Write to file
    file_put_contents('data/codes.json', $json);
    
    echo "Successfully exported " . count($data) . " records to data/codes.json\n";
} catch (Exception $e) {
    die("Error exporting data: " . $e->getMessage());
} 
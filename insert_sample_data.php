<?php
require_once 'database.php';

// Sample RVU codes data
$sample_data = [
    [
        'code' => '99214',
        'description' => 'Office or other outpatient visit for the evaluation and management of an established patient',
        'work_rvu' => 1.50,
        'facility_pe_rvu' => 0.98,
        'non_facility_pe_rvu' => 1.45,
        'mp_rvu' => 0.10
    ],
    [
        'code' => '99213',
        'description' => 'Office or other outpatient visit for the evaluation and management of an established patient - low complexity',
        'work_rvu' => 0.97,
        'facility_pe_rvu' => 0.77,
        'non_facility_pe_rvu' => 1.18,
        'mp_rvu' => 0.07
    ],
    [
        'code' => '99215',
        'description' => 'Office or other outpatient visit for the evaluation and management of an established patient - high complexity',
        'work_rvu' => 2.11,
        'facility_pe_rvu' => 1.37,
        'non_facility_pe_rvu' => 1.85,
        'mp_rvu' => 0.15
    ]
];

try {
    // Prepare the insert statement
    $stmt = $pdo->prepare("INSERT INTO rvu_codes (code, description, work_rvu, facility_pe_rvu, non_facility_pe_rvu, mp_rvu) 
                          VALUES (:code, :description, :work_rvu, :facility_pe_rvu, :non_facility_pe_rvu, :mp_rvu)");
    
    foreach ($sample_data as $data) {
        $stmt->execute($data);
        echo "Inserted code: {$data['code']}\n";
    }
    
    echo "Sample data inserted successfully!\n";
    
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
} 
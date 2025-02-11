<?php
$host = 'localhost';
$dbname = 'rvu_lookup';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

function searchCodes($search) {
    global $pdo;
    
    // Clean and prepare search terms
    $search = trim($search);
    
    // If it looks like a code (alphanumeric, 5-7 chars), prioritize exact matches
    if (preg_match('/^[A-Z0-9]{5,7}$/i', $search)) {
        $stmt = $pdo->prepare("SELECT * FROM rvu_codes 
                              WHERE code LIKE :exact_code 
                              LIMIT 100");
        $stmt->execute(['exact_code' => $search]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (!empty($results)) {
            return $results;
        }
    }
    
    // Split search terms for better description matching
    $terms = explode(' ', $search);
    $sql = "SELECT *, 
            (CASE 
                WHEN code LIKE :search THEN 100
                WHEN description LIKE CONCAT(:search, '%') THEN 90
                WHEN description LIKE CONCAT('% ', :search, ' %') THEN 80
                WHEN description LIKE CONCAT('%', :search, '%') THEN 70
                ELSE 60
            END) as relevance
            FROM rvu_codes 
            WHERE ";
    
    // Build conditions for each term
    $conditions = [];
    $params = ['search' => $search]; // Add the :search parameter
    foreach ($terms as $i => $term) {
        $key = "term$i";
        $conditions[] = "description LIKE :$key";
        $params[$key] = "%$term%";
    }
    
    $sql .= "(" . implode(" AND ", $conditions) . ")";
    $sql .= " ORDER BY relevance DESC, work_rvu DESC LIMIT 100";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
} 
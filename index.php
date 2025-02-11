<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medical Procedure Code Lookup</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            padding: 20px;
            background-color: #f8f9fa;
        }
        .search-container {
            max-width: 1000px;
            margin: 30px auto;
            padding: 25px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        .results-table {
            margin-top: 25px;
        }
        .search-tips {
            font-size: 0.9em;
            color: #6c757d;
            margin-top: 10px;
        }
        .highlight {
            background-color: #fff3cd;
            padding: 2px;
            border-radius: 3px;
        }
        .total-results {
            margin-bottom: 15px;
            color: #6c757d;
        }
        .table th {
            background-color: #f8f9fa;
        }
        .description-cell {
            max-width: 300px;
        }
        .code-cell {
            font-family: monospace;
            font-weight: bold;
        }
        .rvu-cell {
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="search-container">
            <h1 class="text-center mb-4">Medical Procedure Code Lookup</h1>
            <form method="GET" action="" class="mb-4">
                <div class="input-group">
                    <input type="text" name="search" class="form-control form-control-lg" 
                           placeholder="Search by procedure description or CPT/HCPCS code" 
                           value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                    <button type="submit" class="btn btn-primary">Search</button>
                </div>
                <div class="search-tips">
                    <strong>Search Tips:</strong>
                    <ul>
                        <li>Enter a procedure description (e.g., "office visit established patient")</li>
                        <li>Or enter a specific CPT/HCPCS code (e.g., "99214")</li>
                        <li>Use multiple keywords to narrow results</li>
                    </ul>
                </div>
            </form>

            <?php
            if (isset($_GET['search']) && !empty($_GET['search'])) {
                require_once 'database.php';
                $search = $_GET['search'];
                $results = searchCodes($search);
                
                if (!empty($results)) {
                    echo '<div class="total-results">Found ' . count($results) . ' results</div>';
                    echo '<div class="table-responsive results-table">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Code</th>
                                        <th>Description</th>
                                        <th class="text-end">Work<br>RVU</th>
                                        <th class="text-end">Facility<br>PE RVU</th>
                                        <th class="text-end">Non-Facility<br>PE RVU</th>
                                        <th class="text-end">MP<br>RVU</th>
                                    </tr>
                                </thead>
                                <tbody>';
                    
                    foreach ($results as $row) {
                        // Highlight search terms in description
                        $description = htmlspecialchars($row['description']);
                        $searchTerms = explode(' ', $search);
                        foreach ($searchTerms as $term) {
                            if (strlen($term) > 2) {
                                $description = preg_replace("/($term)/i", '<span class="highlight">$1</span>', $description);
                            }
                        }
                        
                        echo "<tr>
                                <td class='code-cell'>{$row['code']}</td>
                                <td class='description-cell'>{$description}</td>
                                <td class='rvu-cell'>" . number_format($row['work_rvu'], 2) . "</td>
                                <td class='rvu-cell'>" . number_format($row['facility_pe_rvu'], 2) . "</td>
                                <td class='rvu-cell'>" . number_format($row['non_facility_pe_rvu'], 2) . "</td>
                                <td class='rvu-cell'>" . number_format($row['mp_rvu'], 2) . "</td>
                            </tr>";
                    }
                    
                    echo '</tbody></table></div>';
                } else {
                    echo '<div class="alert alert-info">No procedures found matching your search. Try using different keywords or check the spelling.</div>';
                }
            }
            ?>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 
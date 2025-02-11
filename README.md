# RVU Code Lookup

A simple web application to look up RVU (Relative Value Unit) codes from the CMS database.

## Requirements

- PHP 7.4 or higher
- MySQL/MariaDB
- Composer
- Web server (Apache/Nginx)

## Setup Instructions

1. Clone this repository to your web server directory.

2. Install dependencies:
```bash
composer install
```

3. Configure your database:
   - Open `database.php`
   - Update the database credentials (host, dbname, username, password)

4. Create the database and tables:
   - Import the `setup.sql` file into your MySQL server:
```bash
mysql -u your_username -p < setup.sql
```

5. Import the RVU data:
```bash
php import_data.php
```

6. Configure your web server to point to the directory containing these files.

7. Access the application through your web browser.

## Usage

1. Open the application in your web browser
2. Enter a CPT/HCPCS code or description in the search box
3. Click "Search" to find matching codes
4. Results will display the code, description, and associated RVU values

## Features

- Search by code or description
- Display of Work RVU, Facility PE RVU, Non-Facility PE RVU, and MP RVU
- Modern, responsive interface
- Fast search results 
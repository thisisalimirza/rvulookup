# RVU Code Lookup

A simple web application to look up RVU (Relative Value Unit) codes from the CMS database. This is a static version that can be hosted on GitHub Pages.

## Features

- Search by code or description
- Display of Work RVU, Facility PE RVU, Non-Facility PE RVU, and MP RVU
- Modern, responsive interface
- Fast client-side search
- Highlights matching search terms
- No server required - works entirely in the browser

## Setup Instructions

1. Clone this repository:
```bash
git clone https://github.com/yourusername/rvulookup.git
cd rvulookup
```

2. If you need to update the data:
   - Update your MySQL database with new data
   - Run the export script:
```bash
php export_json.php
```

3. Deploy to GitHub Pages:
   - Push the repository to GitHub
   - Go to repository Settings > Pages
   - Select the main branch as source
   - Your site will be available at https://yourusername.github.io/rvulookup

## Local Development

To run the site locally:

```bash
# Using Python 3
python -m http.server 8000

# Or using PHP
php -S localhost:8000
```

Then open http://localhost:8000 in your browser.

## File Structure

- `index.html` - The main application
- `data/codes.json` - The RVU codes data
- `export_json.php` - Script to export MySQL data to JSON (only needed for updates)

## Usage

1. Open the application in your web browser
2. Enter a CPT/HCPCS code or description in the search box
3. Results will display automatically
4. Click on column headers to sort results 
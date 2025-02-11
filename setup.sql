CREATE DATABASE IF NOT EXISTS rvu_lookup;
USE rvu_lookup;

CREATE TABLE IF NOT EXISTS rvu_codes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(20) NOT NULL,
    description TEXT,
    work_rvu DECIMAL(10,2),
    facility_pe_rvu DECIMAL(10,2),
    non_facility_pe_rvu DECIMAL(10,2),
    mp_rvu DECIMAL(10,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_code ON rvu_codes(code);
CREATE INDEX idx_description ON rvu_codes(description(255)); 
CREATE TABLE IF NOT EXISTS airport_company
(
    airport_code VARCHAR(15)      NOT NULL,
    company_id   TINYINT UNSIGNED NOT NULL,
    PRIMARY KEY (airport_code, company_id),
    FOREIGN KEY (airport_code) REFERENCES airports (code),
    FOREIGN KEY (company_id) REFERENCES companies (id)
)
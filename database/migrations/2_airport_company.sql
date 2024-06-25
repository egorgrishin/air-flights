CREATE TABLE IF NOT EXISTS airport_company
(
    airport_code VARCHAR(15) NOT NULL,
    company_code VARCHAR(63) NOT NULL,
    PRIMARY KEY (airport_code, company_code),
    FOREIGN KEY (airport_code) REFERENCES airports (code) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (company_code) REFERENCES companies (code) ON DELETE CASCADE ON UPDATE CASCADE
);
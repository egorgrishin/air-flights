CREATE TABLE IF NOT EXISTS prices
(
    company_code    VARCHAR(63)     NOT NULL,
    subscription_id BIGINT UNSIGNED NOT NULL,
    price           DECIMAL(10, 2) DEFAULT NULL,

    PRIMARY KEY (company_code, subscription_id),
    FOREIGN KEY (company_code) REFERENCES companies (code) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (subscription_id) REFERENCES subscriptions (id) ON DELETE CASCADE ON UPDATE CASCADE
);
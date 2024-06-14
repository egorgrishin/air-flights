CREATE TABLE IF NOT EXISTS airports
(
    code VARCHAR(15) NOT NULL,
    city_code VARCHAR(15),
    sort TINYINT DEFAULT NULL,
    title VARCHAR(127) NOT NULL,
    PRIMARY KEY (code)
);
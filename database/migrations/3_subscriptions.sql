CREATE TABLE IF NOT EXISTS subscriptions
(
    id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    chat_id    VARCHAR(255)    NOT NULL,
    dep_code   VARCHAR(15)     NOT NULL,
    arr_code   VARCHAR(15)     NOT NULL,
    date       DATE            NOT NULL,
    is_active  TINYINT(1)      NOT NULL DEFAULT 1,
    created_at DATETIME        NOT NULL DEFAULT NOW(),

    PRIMARY KEY (id),
    FOREIGN KEY (dep_code) REFERENCES airports (code),
    FOREIGN KEY (arr_code) REFERENCES airports (code),
    INDEX (chat_id),
    INDEX (is_active),
    INDEX (date)
);
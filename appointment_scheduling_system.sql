CREATE TABLE availability_periods (
    availability_id BIGINT NOT NULL AUTO_INCREMENT,
    provider_id INT NOT NULL,
    availability_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (availability_id),
    CONSTRAINT fk_availability_account
        FOREIGN KEY (provider_id)
        REFERENCES account(AccountID)
        ON DELETE RESTRICT,

    CONSTRAINT chk_availability_time
        CHECK (end_time > start_time),

    INDEX idx_provider_date
        (provider_id, availability_date)
);
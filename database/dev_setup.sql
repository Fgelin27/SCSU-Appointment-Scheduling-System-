CREATE DATABASE IF NOT EXISTS francois_se460_dev;
USE francois_se460_dev;

CREATE TABLE IF NOT EXISTS account (
    AccountID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    Username VARCHAR(255),
    Password VARBINARY(255),
    First_name VARCHAR(255),
    Last_name VARCHAR(255),
    Role VARCHAR(255),
    Available_hours_begin TIME,
    Available_hours_end TIME,
    Available_days_begin DATE,
    Available_days_end DATE
);

CREATE TABLE IF NOT EXISTS availability_periods (
    availability_id INT AUTO_INCREMENT PRIMARY KEY,
    provider_id INT NOT NULL,
    availability_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_availability_provider
        FOREIGN KEY (provider_id) REFERENCES account(AccountID),
    CONSTRAINT chk_availability_times
        CHECK (start_time < end_time),
    INDEX idx_provider_date (provider_id, availability_date)
);

INSERT INTO account (Username, First_name, Last_name, Role)
VALUES ('test_provider', 'Test', 'Provider', 'provider');

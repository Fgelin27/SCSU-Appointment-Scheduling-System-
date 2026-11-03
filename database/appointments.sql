
USE francois_se460_dev;

CREATE TABLE IF NOT EXISTS appointments (
    appointment_id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    provider_id INT NOT NULL,
    availability_id INT NOT NULL,
    appointment_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    status ENUM('pending', 'confirmed', 'cancelled')
        NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_appointment_student
        FOREIGN KEY (student_id)
        REFERENCES account(AccountID),

    CONSTRAINT fk_appointment_provider
        FOREIGN KEY (provider_id)
        REFERENCES account(AccountID),

    CONSTRAINT fk_appointment_availability
        FOREIGN KEY (availability_id)
        REFERENCES availability_periods(availability_id),

    CONSTRAINT chk_appointment_time
        CHECK (start_time < end_time),

    INDEX idx_provider_schedule
        (provider_id, appointment_date, status),

    INDEX idx_student_schedule
        (student_id, appointment_date, status)
);

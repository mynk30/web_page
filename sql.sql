CREATE TABLE `required_documents` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `service_type` VARCHAR(255) NOT NULL,
    `document_type` VARCHAR(255) NOT NULL,
    `document_name` VARCHAR(255) NOT NULL,
    `description` TEXT NULL
);

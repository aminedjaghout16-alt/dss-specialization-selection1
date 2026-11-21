-- Decision Support System: University Specialization Selection
-- Import this file in phpMyAdmin (tab "Import") or run: mysql -u root < database.sql

CREATE DATABASE IF NOT EXISTS dss CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE dss;

DROP TABLE IF EXISTS evaluations;
DROP TABLE IF EXISTS students;
DROP TABLE IF EXISTS specializations;
DROP TABLE IF EXISTS criteria;

CREATE TABLE students (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(100) NULL
);

CREATE TABLE specializations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  description VARCHAR(255) NULL
);

-- type: 'max' = higher is better, 'min' = lower is better
CREATE TABLE criteria (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  type ENUM('max','min') NOT NULL DEFAULT 'max',
  weight DECIMAL(4,2) NOT NULL DEFAULT 0.00
);

-- One score (1..10) per student x specialization x criterion
CREATE TABLE evaluations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  student_id INT NOT NULL,
  specialization_id INT NOT NULL,
  criterion_id INT NOT NULL,
  score TINYINT NOT NULL,
  UNIQUE KEY uniq_eval (student_id, specialization_id, criterion_id),
  CONSTRAINT chk_score CHECK (score BETWEEN 1 AND 10),
  FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
  FOREIGN KEY (specialization_id) REFERENCES specializations(id) ON DELETE CASCADE,
  FOREIGN KEY (criterion_id) REFERENCES criteria(id) ON DELETE CASCADE
);

-- ---------- Sample data ----------
INSERT INTO specializations (id, name, description) VALUES
(1, 'Artificial Intelligence', 'Machine learning, neural networks, intelligent agents'),
(2, 'Cybersecurity', 'Network security, cryptography, ethical hacking'),
(3, 'Data Science', 'Statistics, data analysis, big data'),
(4, 'Information Systems and Software Engineering', 'Software design, databases, project management');

INSERT INTO criteria (id, name, type, weight) VALUES
(1, 'Programming', 'max', 0.20),
(2, 'Mathematics', 'max', 0.15),
(3, 'Alignment with the student''s competencies', 'max', 0.20),
(4, 'Career opportunities', 'max', 0.20),
(5, 'Difficulty', 'min', 0.10),
(6, 'Personal interest', 'max', 0.15);

INSERT INTO students (id, name, email) VALUES
(1, 'Ahmed Benali', 'ahmed@example.com'),
(2, 'Sara Meziane', 'sara@example.com');

-- Student 1 (specialization, criterion, score)
INSERT INTO evaluations (student_id, specialization_id, criterion_id, score) VALUES
(1,1,1,9),(1,1,2,9),(1,1,3,7),(1,1,4,9),(1,1,5,9),(1,1,6,8),
(1,2,1,7),(1,2,2,6),(1,2,3,6),(1,2,4,8),(1,2,5,7),(1,2,6,6),
(1,3,1,7),(1,3,2,9),(1,3,3,7),(1,3,4,9),(1,3,5,7),(1,3,6,7),
(1,4,1,9),(1,4,2,5),(1,4,3,8),(1,4,4,7),(1,4,5,5),(1,4,6,6);

-- Student 2
INSERT INTO evaluations (student_id, specialization_id, criterion_id, score) VALUES
(2,1,1,8),(2,1,2,8),(2,1,3,6),(2,1,4,9),(2,1,5,9),(2,1,6,5),
(2,2,1,7),(2,2,2,6),(2,2,3,8),(2,2,4,8),(2,2,5,7),(2,2,6,9),
(2,3,1,6),(2,3,2,9),(2,3,3,6),(2,3,4,9),(2,3,5,7),(2,3,6,6),
(2,4,1,9),(2,4,2,5),(2,4,3,7),(2,4,4,7),(2,4,5,5),(2,4,6,5);

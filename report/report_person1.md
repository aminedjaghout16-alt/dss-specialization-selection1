# Report - Person 1: Database and CRUD

Name: ____________   Files: database.sql, config.php, students.php, specializations.php, index.php

## 1. Problem description (short)
Explain in 4-5 lines: choosing a university specialization is a multi-criteria decision; the app stores the data and recommends the best option.

## 2. Database design
- ER diagram (draw 4 boxes: students, specializations, criteria, evaluations + arrows)
- Table description (one small table per table: column, type, meaning)
- Keys: primary keys, foreign keys, UNIQUE(student, specialization, criterion), CHECK score 1..10, ON DELETE CASCADE (explain why)

## 3. Connection and security
- config.php: PDO connection to MySQL
- Prepared statements (?) -> protects against SQL injection
- e() = htmlspecialchars -> protects against XSS

## 4. CRUD (students and specializations)
- Create / Read / Update / Delete: how one file handles all four (POST action = save/delete, redirect after POST)
- Screenshot of the Students page and the Specializations page

## 5. Home page (index.php)
- COUNT(*) queries, screenshot

## 6. My improvement (add one small thing, e.g. a search box) 

## 7. Conclusion (3 lines)

## Questions to prepare
- Why ON DELETE CASCADE? What is a foreign key?
- What if two evaluations have the same student+specialization+criterion?
- Why prepared statements?

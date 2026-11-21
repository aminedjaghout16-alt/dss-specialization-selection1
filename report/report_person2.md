# Report - Person 2: Criteria, weights and evaluations

Name: ____________   Files: criteria.php, evaluations.php

## 1. Criteria and weights
Table: criterion | type (max/min) | weight | why this weight
(Programming 0.20, Mathematics 0.15, Alignment 0.20, Career 0.20, Difficulty 0.10 (min), Personal interest 0.15)
- Difference between maximize and minimize criteria; why Difficulty is minimize.
- Why weights must sum to 1 and how the page warns when they don't.

## 2. Criteria page
- Add/edit/delete, validation (weight between 0 and 1), screenshot.

## 3. Evaluation matrix
- Scores 1..10 for each specialization x criterion, per student.
- How the form is built with nested loops: name="score[spec][criterion]".
- INSERT ... ON DUPLICATE KEY UPDATE (insert or update in one query).
- max(1, min(10, value)) forces the 1..10 range.
- Screenshot of the evaluation page.

## 4. My improvement (e.g. a button "fill all with 5")

## 5. Conclusion (3 lines)

## Questions to prepare
- Why must weights sum to 1?
- What does ON DUPLICATE KEY UPDATE do?
- What happens if the user leaves a score empty? (that specialization is not ranked)

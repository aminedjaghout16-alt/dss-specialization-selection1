# Report - Person 3: WSM engine, results and interface

Name: ____________   Files: results.php, header.php, footer.php, style.css

## 1. The Weighted Sum Model
- Stage A: evaluation matrix (scores 1..10)
- Stage B: normalization -> maximize r = x/10 ; minimize r = (11-x)/10 (higher is always better, range 0.1..1)
- Stage C: Score(Pi) = sum over criteria of weight_j * r_ij
- Ranking: sort descending (usort); highest = recommendation; equal scores (4 decimals) = tie warning.

## 2. Algorithm (pseudo-code or flowchart)
for each specialization: for each criterion: normalize, multiply by weight, add -> score
sort by score desc -> assign ranks -> detect ties -> display

## 3. Worked example (use real numbers)
Student Ahmed, AI: 0.20*0.9 + 0.15*0.9 + 0.20*0.7 + 0.20*0.9 + 0.10*0.2 + 0.15*0.8 = 0.775
Do the same for another specialization and compare.

## 4. Results page
- Recommendation box, ranking table, bar chart (CSS width = score %), raw matrix, normalized matrix. Screenshots.
- Edge cases: incomplete data, weights not summing to 1, tie.

## 5. Interface
- header.php / footer.php reused with include; style.css (navbar, cards, tables).

## 6. My improvement (e.g. a print button, or Chart.js chart)

## 7. Conclusion of the whole project (5 lines)

## Questions to prepare
- Why (11-x)/10 and not (10-x)/10?
- What happens in a tie? What if weights don't sum to 1?
- What are the limits of WSM?

# DSS – University Specialization Selection

![PHP](https://img.shields.io/badge/PHP-%E2%89%A57.4-777BB4?logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?logo=mysql&logoColor=white)
![XAMPP](https://img.shields.io/badge/Stack-XAMPP-FB7A24?logo=apache&logoColor=white)
![Method](https://img.shields.io/badge/Method-Weighted%20Sum%20Model-2EA44F)

Web app (**PHP + MySQL**) that helps a student choose the university
specialization that best matches their profile, using the
**Weighted Sum Model (WSM)** — a classical Multi-Criteria Decision Making (MCDM) method.

---

## 📑 Table of contents
1. [How it works (short)](#-how-it-works-short)
2. [Actors](#-actors)
3. [Use cases](#-use-cases)
4. [Use case details](#-use-case-details)
5. [Architecture](#️-architecture)
6. [Database design (ER)](#-database-design-er)
7. [The WSM method](#-the-wsm-method)
8. [Worked example](#-worked-example)
9. [Run it (Windows, XAMPP)](#-run-it-windows-xampp)
10. [Files](#-files)
11. [Project workflow (Git)](#-project-workflow-git)

---

## 🧭 How it works (short)

1. The administrator defines **students**, **specializations** and **criteria**
   (each criterion is *maximized* or *minimized* and has a **weight**).
2. For one student, **evaluations** are entered: a score from **1 to 10**
   for every (specialization, criterion) pair.
3. The app normalizes the scores, applies the weights, sums them per
   specialization (**WSM score**), **ranks** the specializations and
   **recommends** the one with the highest score.
4. Ties (equal scores at 4 decimals) are detected and reported.

```mermaid
flowchart LR
    A[Students] -->|scored 1-10 on| C[Criteria]
    C -->|weights Σ=1| D((WSM))
    B[Specializations] -->|alternatives| D
    D --> E[Ranking + Recommendation]
```

---

## 👥 Actors

| Actor | Role in the system |
|---|---|
| 🧑‍🎓 **Student** | Is described by the evaluations; consults the results and the recommendation. |
| 🧑‍🏫 **Advisor / Admin** | Creates and maintains students, specializations, criteria, weights and evaluations, then runs the calculation. |

---

## 🎯 Use cases

```mermaid
flowchart LR
    A(("🧑‍🏫<br>Advisor / Admin"))
    S(("🧑‍🎓<br>Student"))

    subgraph SYS ["DSS – University Specialization Selection"]
        UC1["UC1<br>Manage students"]
        UC2["UC2<br>Manage specializations"]
        UC3["UC3<br>Manage criteria & weights"]
        UC4["UC4<br>Enter evaluations (scores)"]
        UC5["UC5<br>Compute WSM results"]
        UC6["UC6<br>Consult ranking & recommendation"]
        UC7["UC7<br>Detect ties"]
    end

    A --> UC1
    A --> UC2
    A --> UC3
    A --> UC4
    A --> UC5
    S --> UC6
    S -.->|reads ranking| UC5
    UC5 --> UC6
    UC5 --> UC7
```

| ID | Use case | Actor | Purpose |
|----|----------|-------|---------|
| **UC1** | Manage students | Advisor | Add, edit, delete students (name, email). |
| **UC2** | Manage specializations | Advisor | Add, edit, delete the alternative specializations (name, description). |
| **UC3** | Manage criteria & weights | Advisor | Add, edit, delete criteria; set type (**max/min**) and **weight** (0–1, weights sum to 1). |
| **UC4** | Enter evaluations | Advisor | For a chosen student, fill a **score 1–10** for each (specialization, criterion) pair; saved with upsert, can delete all of a student. |
| **UC5** | Compute WSM results | System | Normalize scores, apply weights, sum per specialization, **rank**. |
| **UC6** | Consult ranking & recommendation | Student / Advisor | See the sorted table, the chart and the **recommended specialization**. |
| **UC7** | Detect ties | System | If the top score is shared (4 decimals), report **all** winners as a tie. |

---

## 📖 Use case details

### UC4 — Enter evaluations (most complex)
| | |
|---|---|
| **Actor** | Advisor |
| **Precondition** | At least one student, one specialization and one criterion exist. |
| **Main flow** | 1. Choose a student → 2. A grid shows every (specialization × criterion) cell → 3. Enter scores 1–10 (empty cells are skipped) → 4. Save. |
| **Postcondition** | `evaluations` rows created/updated (**upsert**). Missing pairs are allowed — they mark the specialization *incomplete*. |
| **Alternate flow** | *Delete all* removes every evaluation of the chosen student. |
| **Validation** | Scores are clamped to the 1–10 range; empty cells are ignored. |

### UC5 — Compute WSM results
| | |
|---|---|
| **Actor** | System (triggered from `results.php`) |
| **Precondition** | Evaluations exist for the selected student. |
| **Main flow** | 1. Load criteria + weights → 2. Load the student's evaluations → 3. Normalize each score → 4. `score(Sᵢ) = Σ (wⱼ × rᵢⱼ)` / Σw → 5. Sort descending → 6. Assign ranks. |
| **Alternate flow** | A specialization with **missing evaluations** is listed as *incomplete* and excluded from the ranking. |
| **Special rule** | Scores rounded to **4 decimals**; equal top scores ⇒ **tie** → several winners are announced. |

---

## 🏗️ Architecture

Classic **LAMP-style 3-tier** architecture:

```mermaid
flowchart TB
    subgraph Browser["🌐 Browser"]
        UI["HTML pages + style.css<br>(tables, forms, chart)"]
    end
    subgraph Server["🐘 PHP (Apache / XAMPP)"]
        P1["index.php<br>dashboard"]
        P2["students.php / specializations.php<br>criteria.php / evaluations.php"]
        P3["results.php<br>WSM engine"]
        CFG["config.php<br>PDO connection"]
    end
    subgraph DB["🗄️ MySQL"]
        D1[("dss database<br>students · specializations<br>criteria · evaluations")]
    end
    UI <-->|HTTP GET / POST| P1 & P2 & P3
    P1 & P2 & P3 -->|PDO prepared statements| CFG --> D1
```

- All SQL uses **prepared statements** (protection against SQL injection).
- All output goes through an escape helper (`e()`) — protection against XSS.
- Forms use **POST-Redirect-GET**, so refreshing a page never re-submits a form.

---

## 🗄️ Database design (ER)

```mermaid
erDiagram
    STUDENTS {
        int id PK
        varchar name "NOT NULL"
        varchar email "NULL"
    }
    SPECIALIZATIONS {
        int id PK
        varchar name "NOT NULL"
        text description
    }
    CRITERIA {
        int id PK
        varchar name "NOT NULL"
        enum type "max | min"
        decimal weight "0..1, Σ = 1"
    }
    EVALUATIONS {
        int student_id FK
        int specialization_id FK
        int criterion_id FK
        int score "1..10"
    }
    STUDENTS ||--o{ EVALUATIONS : "is evaluated by"
    SPECIALIZATIONS ||--o{ EVALUATIONS : "is scored on"
    CRITERIA ||--o{ EVALUATIONS : "weights"
```

`EVALUATIONS` is the **junction table** of the three entities:
one row = *"student S gave specialization P the score x on criterion C"*.
All foreign keys use **ON DELETE CASCADE**.

---

## 📐 The WSM method

**Step 1 — Normalization** (brings all scores to 0…1):

| Criterion type | Formula | Example (x = 7) |
|---|---|---|
| **maximize** (higher is better) | `r = x / 10` | `0.7` |
| **minimize** (lower is better) | `r = (11 − x) / 10` | `0.4` |

**Step 2 — Weighted sum** per specialization *i*:

```
Score(Pi) = Σj ( weight_j × r_ij )      with Σ weights = 1
```

**Step 3 — Ranking**: sort descending; the **highest score wins** and is the
**recommendation**. Equal scores at **4 decimals** ⇒ tie.

```mermaid
flowchart TD
    A["Raw scores (1–10)<br>from evaluations"] --> B{"criterion type?"}
    B -->|max| C["r = x / 10"]
    B -->|min| D["r = (11 − x) / 10"]
    C --> E["Score(Pi) = Σ (w × r)"]
    D --> E
    E --> F["Sort descending → ranks"]
    F --> G{"tie at 4 decimals?"}
    G -->|no| H["✅ Recommend rank 1"]
    G -->|yes| I["🤝 Announce all tied winners"]
```

---

## 🔢 Worked example

Criteria: `Alignment (w=0.5, max)` · `Difficulty (w=0.3, min)` · `Interest (w=0.2, max)`

| Criterion | w | type | CS raw → r | IS raw → r |
|---|---|---|---|---|
| Alignment | 0.5 | max | 8 → **0.8** | 6 → **0.6** |
| Difficulty | 0.3 | min | 6 → **0.5** | 4 → **0.7** |
| Interest | 0.2 | max | 7 → **0.7** | 9 → **0.9** |

| Specialization | Calculation | Score | Rank |
|---|---|---|---|
| **Computer Science** | 0.5×0.8 + 0.3×0.5 + 0.2×0.7 | **0.69** | 🥇 **1 — recommended** |
| **Information Systems** | 0.5×0.6 + 0.3×0.7 + 0.2×0.9 | 0.69 | 🥇 1 — **tie!** |

Here both reach **0.6900** → the app detects the **tie** and announces both.

---

## ▶️ Run it (Windows, XAMPP)

1. Install XAMPP and start **Apache** and **MySQL** in the XAMPP Control Panel.
2. Copy this whole folder to `C:\xampp\htdocs\dss`.
3. Open http://localhost/phpmyadmin → tab **Import** → choose `database.sql` → **Go**.
4. Open http://localhost/dss/

> If your MySQL has a password, edit `config.php`.

---

## 📁 Files

| File | Role |
|---|---|
| `database.sql` | Tables + sample data |
| `config.php` | DB connection (PDO) |
| `students.php` | Student CRUD |
| `specializations.php` | Specialization CRUD |
| `criteria.php` | Criteria CRUD (type max/min + weight 0–1) |
| `evaluations.php` | Enter scores (1–10) per student |
| `results.php` | WSM calculation, ranking, chart, recommendation, tie detection |
| `index.php` | Dashboard: record counts + usage guide |
| `header.php` / `footer.php` / `style.css` | Layout |
| `report/` | Each team member's report |

---

## 🌿 Project workflow (Git)

Each member works on **their own branch**, opens a **Pull Request**, and the
team merges it into `main`.

```mermaid
gitGraph
    commit id: "Initial commit"
    branch person1-database-crud
    commit id: "database.sql"
    commit id: "config.php"
    commit id: "students.php"
    commit id: "specializations.php"
    checkout main
    merge person1-database-crud id: "Merge PR #1"
    branch person2-inputs
    commit id: "criteria.php"
    commit id: "evaluations.php"
    checkout main
    merge person2-inputs id: "Merge PR #2"
    commit id: "results + layout"
```

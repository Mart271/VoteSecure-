<<<<<<< HEAD
# VoteSecure — System Demo Guide

**VoteSecure** is a web-based election management platform designed for schools, student organizations, and institutions. It supports the full election lifecycle — from setup and voter registration to secure online voting, results publication, and AI-powered analytics.

---

## 1. What This System Does

VoteSecure lets administrators run digital elections end-to-end:

| Stage | What happens |
|-------|----------------|
| **Setup** | Create an election, define positions, add candidates |
| **Registration** | Enroll eligible voters and assign unique voter codes |
| **Voting** | Voters cast one ballot per election during the active period |
| **Results** | View live counts, publish official results to voters |
| **Intelligence** | AI tools analyze patterns, detect anomalies, and generate reports |

The platform emphasizes **security**, **transparency**, and **auditability** — every major action is logged.

---

## 2. Technology Stack

| Layer | Technology |
|-------|------------|
| Backend | PHP 8.x (MVC-style architecture) |
| Database | MySQL (`votesecure_db`) |
| Frontend | HTML, CSS, JavaScript |
| Server | Apache (XAMPP) |
| AI Engine | Qwen AI (Alibaba Cloud DashScope API) |
| Security | bcrypt passwords, CSRF tokens, role-based access, audit log |

---

## 3. User Roles

### Administrator (`election_admin` / `system_admin`)
- Creates and manages elections
- Adds positions and candidates
- Registers voters
- Publishes results
- Runs AI analytics and fraud detection
- Reviews the audit log

### Voter (`voter`)
- Views assigned elections
- Casts a ballot (one vote per election)
- Views published results
- Reviews their own vote after casting
- Can self-register via the public registration page

---

## 4. Demo Login Credentials

Open the application at:

```
http://localhost/votesecure/
```

### Administrator accounts

| Username | Password | Role |
|----------|----------|------|
| `admin` | `admin123` | Election Admin |
| `sysadmin` | `admin123` | System Admin |

> Use the **Administrator** tab on the login page.

### Voter accounts (demo)

| Username | Password | Notes |
|----------|----------|-------|
| `voter1` | `voter123` | Demo voter |
| `voter2` … `voter20` | `voter123` | Additional demo voters |

> Use the **Voter** tab on the login page.

### Self-registered voters
Users who sign up via **Create an account** use the password they chose during registration — not `voter123`.

---

## 5. Election Lifecycle

```
  ┌─────────┐     ┌──────────┐     ┌────────┐     ┌───────────┐     ┌───────────┐
  │  DRAFT  │ ──► │  ACTIVE  │ ──► │ CLOSED │ ──► │ PUBLISHED │     │           │
  └─────────┘     └──────────┘     └────────┘     └───────────┘     │  (done)   │
       │                │               │                │            └───────────┘
   Admin sets      Voters can       Voting ends      Results visible
   positions,      cast ballots     automatically    to voters
   candidates,     during window    or manually
   voters
```

| Status | Meaning |
|--------|---------|
| **draft** | Election is being configured; voters cannot vote yet |
| **active** | Voting is open; registered voters can cast ballots |
| **closed** | Voting has ended; results are being finalized |
| **published** | Official results are public; voters can view them |

---

## 6. Admin Portal Walkthrough

After logging in as admin, you land on the **Dashboard**.

### Dashboard
- Overview stats: total elections, active elections, registered voters, average turnout
- Recent elections table
- Recent activity feed (from audit log)

### Elections
- Create a new election (title, description, start/end dates)
- Change election status (draft → active → closed → published)
- Manage positions for each election (President, VP, Secretary, etc.)

### Candidates
- Add candidates per position
- Assign party affiliation (e.g., Tugon Party, Alon Alliance, Independent)
- View vote counts after voting begins

### Voters
- Register voters for a specific election
- System generates a unique **Voter Code** (e.g., `VC-1-2025-001`)
- Track who has voted and who has not

### Results
- Live vote counts per position
- Turnout statistics
- Visual bar charts showing candidate performance

### AI Analytics 🤖
Select an election, then run any of four AI tools:

| Tool | Purpose |
|------|---------|
| **Election Analysis** | Turnout assessment, per-position insights, competitiveness, recommendations |
| **Fraud Detection** | Risk score, anomaly flags, suspicious IP patterns |
| **Turnout Prediction** | Predicted final turnout with confidence level |
| **Results Summary** | Auto-generated official results narrative for publication |

### Audit Log
- Immutable record of system events: logins, ballot submissions, election changes, AI runs
- Supports accountability and post-election review

---

## 7. Voter Portal Walkthrough

After logging in as a voter, you land on **My Elections**.

### My Elections
- Lists all elections the voter is registered for
- Shows voter code, election dates, and status
- **Cast Ballot** button appears when the election is active and the voter has not yet voted

### Casting a Ballot
1. Click **Cast Ballot →** on an active election
2. Select one candidate per position
3. Review choices and submit
4. Receive a confirmation with ballot reference

> Each voter can only vote **once** per election. The system blocks duplicate submissions.

### View My Vote
After voting, voters can review their submitted choices (without changing them).

### Results
When an election is **published** or **closed**, voters can view official results with vote counts and percentages.

### Profile
Voters can update their display name, department, section, avatar, and password.

---

## 8. Security Features (Demo Talking Points)

| Feature | Description |
|---------|-------------|
| **Password hashing** | All passwords stored with bcrypt |
| **Role-based access** | Admin and voter areas are strictly separated |
| **CSRF protection** | Forms include tokens to prevent cross-site attacks |
| **One vote per voter** | Database enforces single ballot per voter per election |
| **IP logging** | Each ballot records the submitter's IP address |
| **Audit trail** | Every login, vote, and admin action is logged with timestamp |
| **Session management** | Secure session handling with ID regeneration on login |
| **AI fraud detection** | Analyzes IP clusters and voting velocity for anomalies |

---

## 9. Suggested Demo Script (10–15 minutes)

Use this flow when presenting to an audience:

### Part A — Admin setup (3 min)
1. Log in as **admin** / **admin123**
2. Show the **Dashboard** — highlight stats and activity feed
3. Open **Elections** — show an existing election or create a new one
4. Briefly show **Candidates** and **Voters** for that election

### Part B — Voter experience (4 min)
1. Log out, then log in as **voter1** / **voter123**
2. Show **My Elections** — point out the voter code and election status
3. If an election is active, **Cast Ballot** — select candidates and submit
4. Show **View My Vote** to confirm the ballot was recorded

### Part C — Results & AI (5 min)
1. Log back in as **admin**
2. Open **Results** — show live vote counts and turnout
3. Go to **AI Analytics** — select the demo election
4. Run **Election Analysis** — show structured insights (turnout, winners, margins)
5. Run **Fraud Detection** — show risk score and anomaly report
6. Run **Results Summary** — show auto-generated narrative

### Part D — Accountability (2 min)
1. Open **Audit Log** — show login events, ballot submissions, and AI runs
2. Emphasize transparency: every action is traceable

---

## 10. Sample Demo Data

The system may include a pre-loaded **Student Council Election** with:

- **5 executive positions** (President, Vice-President, Secretary, Treasurer, Auditor)
- **Multiple parties** (e.g., Tugon Party, Alon Alliance) plus independent candidates
- **~15 registered voters** with ~80% turnout
- Realistic vote splits demonstrating party-line voting patterns

This data is ideal for demonstrating AI analysis features.

---

## 11. Project Structure (Reference)

```
votesecure/
├── index.php              # Login & registration entry point
├── logout.php             # Session logout
├── config/database.php    # DB & app configuration
├── admin/                 # Admin page controllers
├── voter/                 # Voter page controllers
├── api/ai.php             # AI analytics JSON API
├── src/
│   ├── Controllers/       # Business logic (Admin, Voter, Auth, AI)
│   ├── Models/            # Database models (Election, Ballot, User, etc.)
│   └── Services/          # Auth, Session, Qwen AI
├── views/                 # HTML templates (admin, voter, auth)
└── public/                # CSS, JS, uploads
```

---

## 12. Requirements to Run the Demo

1. **XAMPP** with Apache and MySQL running
2. Database **`votesecure_db`** imported and configured in `config/database.php`
3. Application accessible at `http://localhost/votesecure/`
4. **Qwen API key** configured in `config/database.php` for AI features (optional — other features work without it)

---

## 13. Troubleshooting

| Issue | Solution |
|-------|----------|
| Blank page or redirect loop | Clear browser cookies for `localhost`, then visit `/logout.php` |
| Voter login fails | Use **Voter** tab; demo password is `voter123` for voter1–voter20 |
| Admin login fails | Use **Administrator** tab; password is `admin123` |
| AI features not working | Check Qwen API key in `config/database.php` |
| No elections shown for voter | Admin must register the voter for an election first |

---

## 14. Key Value Propositions (Closing Statement)

> **VoteSecure** replaces paper ballots and manual vote counting with a secure, transparent, and auditable digital platform. Administrators get full control over elections with real-time results and AI-powered insights. Voters get a simple, trustworthy experience — one person, one vote, fully traceable. The built-in audit log and fraud detection tools ensure election integrity from start to finish.

---

*VoteSecure v1.0 — AI-Integrated Election System*
=======
# VoteSecure — System Demo Guide

**VoteSecure** is a web-based election management platform designed for schools, student organizations, and institutions. It supports the full election lifecycle — from setup and voter registration to secure online voting, results publication, and AI-powered analytics.

---

## 1. What This System Does

VoteSecure lets administrators run digital elections end-to-end:

| Stage | What happens |
|-------|----------------|
| **Setup** | Create an election, define positions, add candidates |
| **Registration** | Enroll eligible voters and assign unique voter codes |
| **Voting** | Voters cast one ballot per election during the active period |
| **Results** | View live counts, publish official results to voters |
| **Intelligence** | AI tools analyze patterns, detect anomalies, and generate reports |

The platform emphasizes **security**, **transparency**, and **auditability** — every major action is logged.

---

## 2. Technology Stack

| Layer | Technology |
|-------|------------|
| Backend | PHP 8.x (MVC-style architecture) |
| Database | MySQL (`votesecure_db`) |
| Frontend | HTML, CSS, JavaScript |
| Server | Apache (XAMPP) |
| AI Engine | Qwen AI (Alibaba Cloud DashScope API) |
| Security | bcrypt passwords, CSRF tokens, role-based access, audit log |

---

## 3. User Roles

### Administrator (`election_admin` / `system_admin`)
- Creates and manages elections
- Adds positions and candidates
- Registers voters
- Publishes results
- Runs AI analytics and fraud detection
- Reviews the audit log

### Voter (`voter`)
- Views assigned elections
- Casts a ballot (one vote per election)
- Views published results
- Reviews their own vote after casting
- Can self-register via the public registration page

---

## 4. Demo Login Credentials

Open the application at:

```
http://localhost/votesecure/
```

### Administrator accounts

| Username | Password | Role |
|----------|----------|------|
| `admin` | `admin123` | Election Admin |
| `sysadmin` | `admin123` | System Admin |

> Use the **Administrator** tab on the login page.

### Voter accounts (demo)

| Username | Password | Notes |
|----------|----------|-------|
| `voter1` | `voter123` | Demo voter |
| `voter2` … `voter20` | `voter123` | Additional demo voters |

> Use the **Voter** tab on the login page.

### Self-registered voters
Users who sign up via **Create an account** use the password they chose during registration — not `voter123`.

---

## 5. Election Lifecycle

```
  ┌─────────┐     ┌──────────┐     ┌────────┐     ┌───────────┐     ┌───────────┐
  │  DRAFT  │ ──► │  ACTIVE  │ ──► │ CLOSED │ ──► │ PUBLISHED │     │           │
  └─────────┘     └──────────┘     └────────┘     └───────────┘     │  (done)   │
       │                │               │                │            └───────────┘
   Admin sets      Voters can       Voting ends      Results visible
   positions,      cast ballots     automatically    to voters
   candidates,     during window    or manually
   voters
```

| Status | Meaning |
|--------|---------|
| **draft** | Election is being configured; voters cannot vote yet |
| **active** | Voting is open; registered voters can cast ballots |
| **closed** | Voting has ended; results are being finalized |
| **published** | Official results are public; voters can view them |

---

## 6. Admin Portal Walkthrough

After logging in as admin, you land on the **Dashboard**.

### Dashboard
- Overview stats: total elections, active elections, registered voters, average turnout
- Recent elections table
- Recent activity feed (from audit log)

### Elections
- Create a new election (title, description, start/end dates)
- Change election status (draft → active → closed → published)
- Manage positions for each election (President, VP, Secretary, etc.)

### Candidates
- Add candidates per position
- Assign party affiliation (e.g., Tugon Party, Alon Alliance, Independent)
- View vote counts after voting begins

### Voters
- Register voters for a specific election
- System generates a unique **Voter Code** (e.g., `VC-1-2025-001`)
- Track who has voted and who has not

### Results
- Live vote counts per position
- Turnout statistics
- Visual bar charts showing candidate performance

### AI Analytics 🤖
Select an election, then run any of four AI tools:

| Tool | Purpose |
|------|---------|
| **Election Analysis** | Turnout assessment, per-position insights, competitiveness, recommendations |
| **Fraud Detection** | Risk score, anomaly flags, suspicious IP patterns |
| **Turnout Prediction** | Predicted final turnout with confidence level |
| **Results Summary** | Auto-generated official results narrative for publication |

### Audit Log
- Immutable record of system events: logins, ballot submissions, election changes, AI runs
- Supports accountability and post-election review

---

## 7. Voter Portal Walkthrough

After logging in as a voter, you land on **My Elections**.

### My Elections
- Lists all elections the voter is registered for
- Shows voter code, election dates, and status
- **Cast Ballot** button appears when the election is active and the voter has not yet voted

### Casting a Ballot
1. Click **Cast Ballot →** on an active election
2. Select one candidate per position
3. Review choices and submit
4. Receive a confirmation with ballot reference

> Each voter can only vote **once** per election. The system blocks duplicate submissions.

### View My Vote
After voting, voters can review their submitted choices (without changing them).

### Results
When an election is **published** or **closed**, voters can view official results with vote counts and percentages.

### Profile
Voters can update their display name, department, section, avatar, and password.

---

## 8. Security Features (Demo Talking Points)

| Feature | Description |
|---------|-------------|
| **Password hashing** | All passwords stored with bcrypt |
| **Role-based access** | Admin and voter areas are strictly separated |
| **CSRF protection** | Forms include tokens to prevent cross-site attacks |
| **One vote per voter** | Database enforces single ballot per voter per election |
| **IP logging** | Each ballot records the submitter's IP address |
| **Audit trail** | Every login, vote, and admin action is logged with timestamp |
| **Session management** | Secure session handling with ID regeneration on login |
| **AI fraud detection** | Analyzes IP clusters and voting velocity for anomalies |

---

## 9. Suggested Demo Script (10–15 minutes)

Use this flow when presenting to an audience:

### Part A — Admin setup (3 min)
1. Log in as **admin** / **admin123**
2. Show the **Dashboard** — highlight stats and activity feed
3. Open **Elections** — show an existing election or create a new one
4. Briefly show **Candidates** and **Voters** for that election

### Part B — Voter experience (4 min)
1. Log out, then log in as **voter1** / **voter123**
2. Show **My Elections** — point out the voter code and election status
3. If an election is active, **Cast Ballot** — select candidates and submit
4. Show **View My Vote** to confirm the ballot was recorded

### Part C — Results & AI (5 min)
1. Log back in as **admin**
2. Open **Results** — show live vote counts and turnout
3. Go to **AI Analytics** — select the demo election
4. Run **Election Analysis** — show structured insights (turnout, winners, margins)
5. Run **Fraud Detection** — show risk score and anomaly report
6. Run **Results Summary** — show auto-generated narrative

### Part D — Accountability (2 min)
1. Open **Audit Log** — show login events, ballot submissions, and AI runs
2. Emphasize transparency: every action is traceable

---

## 10. Sample Demo Data

The system may include a pre-loaded **Student Council Election** with:

- **5 executive positions** (President, Vice-President, Secretary, Treasurer, Auditor)
- **Multiple parties** (e.g., Tugon Party, Alon Alliance) plus independent candidates
- **~15 registered voters** with ~80% turnout
- Realistic vote splits demonstrating party-line voting patterns

This data is ideal for demonstrating AI analysis features.

---

## 11. Project Structure (Reference)

```
votesecure/
├── index.php              # Login & registration entry point
├── logout.php             # Session logout
├── config/database.php    # DB & app configuration
├── admin/                 # Admin page controllers
├── voter/                 # Voter page controllers
├── api/ai.php             # AI analytics JSON API
├── src/
│   ├── Controllers/       # Business logic (Admin, Voter, Auth, AI)
│   ├── Models/            # Database models (Election, Ballot, User, etc.)
│   └── Services/          # Auth, Session, Qwen AI
├── views/                 # HTML templates (admin, voter, auth)
└── public/                # CSS, JS, uploads
```

---

## 12. Requirements to Run the Demo

1. **XAMPP** with Apache and MySQL running
2. Database **`votesecure_db`** imported and configured in `config/database.php`
3. Application accessible at `http://localhost/votesecure/`
4. **Qwen API key** configured in `config/database.php` for AI features (optional — other features work without it)

---

## 13. Troubleshooting

| Issue | Solution |
|-------|----------|
| Blank page or redirect loop | Clear browser cookies for `localhost`, then visit `/logout.php` |
| Voter login fails | Use **Voter** tab; demo password is `voter123` for voter1–voter20 |
| Admin login fails | Use **Administrator** tab; password is `admin123` |
| AI features not working | Check Qwen API key in `config/database.php` |
| No elections shown for voter | Admin must register the voter for an election first |

---

## 14. Key Value Propositions (Closing Statement)

> **VoteSecure** replaces paper ballots and manual vote counting with a secure, transparent, and auditable digital platform. Administrators get full control over elections with real-time results and AI-powered insights. Voters get a simple, trustworthy experience — one person, one vote, fully traceable. The built-in audit log and fraud detection tools ensure election integrity from start to finish.

---

*VoteSecure v1.0 — AI-Integrated Election System*
>>>>>>> 4b4892c0a36933c726154fb629a76e5be16d9c40

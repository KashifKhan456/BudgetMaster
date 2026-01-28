# BudgetMaster

[![Build Status](https://img.shields.io/badge/build-pending-lightgrey)](#) [![License: MIT](https://img.shields.io/badge/license-MIT-blue)](./LICENSE)

One-line description: BudgetMaster helps individuals and small teams track income, expenses, budgets, and savings goals with simple reports and visualizations.

## Table of Contents
- [Features](#features)
- [Demo / Screenshots](#demo--screenshots)
- [Tech Stack](#tech-stack)
- [Getting Started](#getting-started)
  - [Prerequisites](#prerequisites)
  - [Installation](#installation)
  - [Environment Variables](#environment-variables)
  - [Run Locally](#run-locally)
- [API (example endpoints)](#api-example-endpoints)
- [Data Model (summary)](#data-model-summary)
- [Testing](#testing)
- [Deployment](#deployment)
- [Contributing](#contributing)
- [Roadmap](#roadmap)
- [License](#license)
- [Contact](#contact)
- [Acknowledgements](#acknowledgements)

## Features
- Create and manage multiple budgets
- Log income and expense transactions
- Categorize transactions (e.g., Groceries, Rent, Utilities)
- Recurring transactions and reminders
- Visual reports: charts for spending, income, trends
- Goal tracking and savings planner
- CSV import/export
- User authentication and multi-user support (optional: team/shared budgets)
- Mobile-responsive UI

## Demo / Screenshots
Include screenshots or an animated GIF here:

- Screenshot of Dashboard
- Screenshot of Budget details
- Screenshot of Transaction form

Example:
![dashboard-placeholder](docs/screenshots/dashboard.png)

## Tech Stack
Fill in or update this list to match your implementation:
- Frontend: React / Vue / Angular (replace)
- Backend: Node.js (Express) / Django / Flask / Rails (replace)
- Database: PostgreSQL / MySQL / SQLite / MongoDB (replace)
- Authentication: JWT / OAuth2
- Optional: Docker, GitHub Actions for CI/CD

## Getting Started

### Prerequisites
- Node.js >= 16 (or your project's Node version)
- npm or yarn
- PostgreSQL (or your chosen DB)
- Optional: Docker & Docker Compose

### Installation
Clone the repo:
```bash
git clone https://github.com/<your-org>/BudgetMaster.git
cd BudgetMaster
```

If the repo is split into `frontend/` and `backend/`, run install per folder:

Frontend:
```bash
cd frontend
npm install
# or
yarn
```

Backend:
```bash
cd backend
npm install
# or
yarn
```

### Environment Variables
Create a `.env` in the backend (and frontend if needed). Example `.env.example`:

```
# Backend
PORT=4000
NODE_ENV=development
DATABASE_URL=postgres://user:password@localhost:5432/budgetmaster
JWT_SECRET=your_jwt_secret_here
SMTP_HOST=smtp.example.com
SMTP_PORT=587
SMTP_USER=example
SMTP_PASS=secret

# Frontend (if needed)
REACT_APP_API_URL=http://localhost:4000/api
```

### Run Locally

Backend (example Node/Express):
```bash
cd backend
npm run migrate   # run DB migrations if applicable
npm run seed      # optional: seed sample data
npm start         # or npm run dev
```

Frontend (example React):
```bash
cd frontend
npm start
```

Docker (optional):
```bash
docker-compose up --build
```

## API (example endpoints)
Update these to match your actual API. Provide more details & examples as the API evolves.

- Auth
  - POST /api/auth/register — register new user
  - POST /api/auth/login — login, returns JWT
  - POST /api/auth/refresh — refresh token

- Budgets
  - GET /api/budgets — list budgets (user)
  - POST /api/budgets — create budget
  - GET /api/budgets/:id — budget details
  - PUT /api/budgets/:id — update
  - DELETE /api/budgets/:id — delete

- Transactions
  - GET /api/budgets/:id/transactions
  - POST /api/budgets/:id/transactions
  - PUT /api/transactions/:id
  - DELETE /api/transactions/:id

- Reports
  - GET /api/reports/monthly?year=2025&month=1
  - GET /api/reports/category-summary?from=2025-01-01&to=2025-01-31

Sample request (using curl):
```bash
curl -X POST "http://localhost:4000/api/auth/login" \
  -H "Content-Type: application/json" \
  -d '{"email":"you@example.com","password":"yourpassword"}'
```

## Data Model (summary)
A simple model you can adapt:

- User
  - id, name, email, password_hash, created_at
- Budget
  - id, user_id, name, period (monthly/weekly), limit_amount, notes
- Category
  - id, user_id, name, type (income/expense)
- Transaction
  - id, budget_id, category_id, amount, date, description, recurring_rule_id
- Goal (optional)
  - id, user_id, name, target_amount, current_amount, deadline

## Testing
Unit & integration tests:
```bash
# backend
cd backend
npm test

# frontend
cd frontend
npm test
```

CI:
- Add a GitHub Actions workflow to run tests and linting on push and pull requests.

## Deployment
- Common options: Heroku, Vercel (frontend), Render, DigitalOcean App Platform, AWS (ECS, Lambda), Docker-based self-hosting.
- Make sure production env variables are set and migrations run before starting the app.
- Use a managed DB (RDS, Cloud SQL) or proper backups for PostgreSQL.

## Contributing
1. Fork the repo
2. Create a feature branch: `git checkout -b feat/awesome-feature`
3. Commit your changes: `git commit -m "Add awesome feature"`
4. Push to the branch: `git push origin feat/awesome-feature`
5. Open a pull request and describe your change

Please follow the code style used in the project and add/adjust tests for new behavior.

## Roadmap
- [ ] Recurring transaction engine
- [ ] CSV import/export and bank integrations
- [ ] Multi-currency support
- [ ] Shared/Team budgets & permissions
- [ ] Mobile app (React Native / Flutter)

## License
This project is licensed under the MIT License — see the [LICENSE](./LICENSE) file for details. Change to your preferred license if necessary.

## Contact
Project maintained by: Your Name — you@example.com  
Repo: https://github.com/<your-org>/BudgetMaster

## Acknowledgements
- Icons and UI inspiration
- Open-source libraries used (React, Chart.js, Tailwind, etc.)

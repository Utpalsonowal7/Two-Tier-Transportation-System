
# Project — PostgreSQL Database Setup

This script sets up all required PostgreSQL databases and imports table structures and data from `.sql` files.

---

## 📁 Folder Structure

```
Food-Supply-Project/
├── database/
│   ├── admin.sql
│   ├── comodities.sql
│   ├── mapp.sql
│   ├── retailer.sql
│   ├── wholesaler.sql
│   └── warehouse.sql
├── setup_dbs.sh ✅
└── README.md
```

---

## ⚙️ Requirements

- PostgreSQL installed and added to system PATH (`psql`, `createdb`)
- `.sql` backup files placed in the `database/` folder
- Internet access not required
- Use **Git Bash** on Windows (not CMD or PowerShell)

---

## 🖥️ How to Run the Setup Script

### ✅ Windows (using Git Bash)

1. Download and install **Git for Windows**: [https://git-scm.com/downloads](https://git-scm.com/downloads)
2. Open **Git Bash**
3. Navigate to the project folder:
   ```bash
   cd /c/Users/YourUsername/Desktop/Food-Supply-Project
   ```
4. Run the script:
   ```bash
   bash setup_dbs.sh
   ```

---

### ✅ Windows (using WSL)

1. Open **WSL** terminal (Ubuntu or Debian preferred)
2. Navigate to the project directory (mounted under `/mnt/c`)
   ```bash
   cd /mnt/c/Users/YourUsername/Desktop/Food-Supply-Project
   ```
3. Run the script:
   ```bash
   bash setup_dbs.sh
   ```

---

### ✅ Linux/macOS

1. Open terminal
2. Navigate to the project folder:
   ```bash
   cd ~/Desktop/Food-Supply-Project
   ```
3. Make the script executable (first time only):
   ```bash
   chmod +x setup_dbs.sh
   ```
4. Run the script:
   ```bash
   ./setup_dbs.sh
   ```

---

## 🧪 Script Flow

- Prompts for PostgreSQL username and password
- Creates databases: `admins`, `comodities`, `mapp`, `retailers_db`, `wholesalers_db`, `warehouse_db`
- Imports corresponding `.sql` files from `database/` folder

---

## ❗ Troubleshooting

| Problem                                      | Solution                                      |
|----------------------------------------------|-----------------------------------------------|
| `createdb: command not found`                | Ensure PostgreSQL is added to system `PATH`   |
| `psql: FATAL: password authentication failed`| Enter correct username/password               |
| `Database already exists`                    | Drop manually or modify script to skip        |
| `Permission denied`                          | Run with a PostgreSQL superuser               |

---

## 📩 Need help?

Contact the project developer or refer to PostgreSQL documentation: https://www.postgresql.org/docs/

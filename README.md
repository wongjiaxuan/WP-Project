# Jimat Master

Jimat Master is an online personal finance and budget tracking application designed to simplify managing income, expenses, and savings. It features a user-friendly interface with dashboards, transaction history, and budget planning tools to help users take control of their financial journey.

## 🚀 Features

### User Features
* **User Authentication**: Secure signup and login system with password hashing.
* **Dashboard Overview**: Visual summary of total income, expenses, current savings, and monthly budget progress.
* **Transaction Management**: Add, edit, and delete income and expense transactions.
* **Budget Planner**: Set and manage monthly budget limits for specific categories (Food, Transport, Utilities, Entertainment, Healthcare, Others).
* **Budget Tracking**: Real-time progress bars showing how much of the budget has been spent, along with alerts when nearing limits.
* **Transaction Overview**: Filter transactions by type, category, and date range.

### Admin Features
* **System-Wide Overview**: View total income, expenses, savings, and active budgets across all users in the system.
* **User Monitoring**: See the total number of registered users and system-wide transaction counts.
* **Global Transaction History**: View and filter transactions made by all users.

## 🛠️ Technologies Used

* **Frontend**: HTML, CSS3, JavaScript
* **Backend**: PHP 
* **Database**: MySQL
* **Assets/Icons**: FontAwesome 6

## 📂 Project Structure

```text
├── admin_dashboard.php     # Admin finance statistics view
├── admin_header.php        # Admin navigation/header component
├── admin_home.php          # Admin landing dashboard
├── admin_overview.php      # Admin view of all user transactions
├── check_budget.php        # Logic to check budget limits and trigger alerts
├── dashboard.php           # User personal finance statistics view
├── delete_transaction.php  # Logic for deleting transactions
├── get_categories.php      # API to fetch budget categories
├── get_transactions.php    # API to fetch transaction history
├── home.php                # User landing dashboard
├── index.php               # Login page
├── input.php               # Add new transactions form
├── insert_transaction.php  # Logic to save new transactions
├── logout.php              # Session destruction logic
├── overview.php            # User transaction history and management
├── set_budget.html         # Budget planning interface
├── set_budget.js           # Frontend logic for budget planning
├── set_budget.php          # Budget backend logic
├── signup.php              # User registration page
├── update_transaction.php  # Logic to modify existing transactions
├── includes/
│   └── db.php              # Database connection settings
├── sql/
│   ├── create_tables.sql   # Database schema
│   └── insert_sample.sql   # Sample data insertion
├── style.css               # Global styling
├── script.js               # Global JavaScript functions
└── img/                    # Image assets for categories

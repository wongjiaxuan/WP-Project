# ✅ JimatMaster Project – Setup & Usage Guide

> 🛠️ Web Programming Final Project – Group Collaboration Setup  
> 👨‍💻 Provided by: Member 5 – Database & Backend Infrastructure

---

## 🔹 GENERAL SUMMARY (EVERYONE PLEASE READ)

Hello team 👋 I’ve completed all the core **database setup** for our JimatMaster system. Here's everything you need to know to get started with development:

- ✅ **Database Name**: `jimatmaster`
- ✅ Tables created: `users`, `categories`, `transactions`, `budgets`
- ✅ Sample data inserted (5 users, 5 categories, 5 transactions, 5 budgets)
- ✅ Database connection file (`db.php`) ready for backend usage
- ✅ Login tested and working using securely hashed passwords
- ✅ Admin role included for testing future expansions
- ✅ Sample data is summarized in `inserteddatasample.txt` for reference

---

## 📦 CLONE & SETUP INSTRUCTIONS (IMPORTANT FOR ALL)

To ensure everything works properly:

1. ✅ **Clone the GitHub repository** into your XAMPP `htdocs` folder:
```

C:\xampp\htdocs\JimatMaster\WP-Project\\

```

2. ✅ **Open XAMPP**, start **Apache** and **MySQL**

3. ✅ Go to:
```

[http://localhost/phpmyadmin](http://localhost/phpmyadmin)

```

4. ✅ Create a database called:
```

jimatmaster

````

5. ✅ Import these two files in this order:
- `/sql/create_tables.sql` – creates all necessary tables
- `/sql/insert_sample.sql` – adds sample users, categories, transactions, budgets

6. ✅ You’re now ready to connect your PHP files to the database!

---

## 🔐 SAMPLE USERS FOR TESTING

Stored in: `inserteddatasample.txt`

| Username | Email               | Password     | Role  |
|----------|---------------------|--------------|-------|
| admin    | admin@example.com   | admin1234    | admin |
| sara     | sara@gmail.com      | sara4321     | user  |
| john     | john.doe@gmail.com  | johnpass     | user  |
| fatimah  | fatimah@gmail.com   | fatimah88    | user  |
| azmi     | azmi@hotmail.com    | azmi321      | user  |

Passwords are securely hashed using `password_hash()` in SQL.

---

## 🔸 INSTRUCTIONS FOR EACH MEMBER

### 🧑‍💻 ALL DEVELOPERS (VERY IMPORTANT)
✔️ At the **top of every PHP file** that interacts with the database, you must include:
```php
include 'includes/db.php';
````

Otherwise, your database queries will not work!

---

### 🧑‍💼 Member 1 – User Authentication

* Use the `users` table for login/register
* Use `password_hash()` when inserting new users in `signup.php`
* Use `password_verify()` for login validation in `login.php`
* The system includes one admin account for future role-based access

---

### 💳 Member 2 – Transaction Input & Overview

* Use the `transactions` and `categories` tables
* Transactions are linked via `user_id` and `category_id`
* Sample categories like Food, Transport, Utilities, etc. are already inserted

---

### 💡 Member 3 – Budget & Alert System

* Use the `budgets` table for storing monthly limits
* Each budget entry is tied to `user_id`, `category_id`, and `month`
* You can join with `transactions` to calculate actual spend vs. limit

---

### 📊 Member 4 – Dashboard & Export

* Use `transactions` + `budgets` to generate insights
* You can visualize totals, savings, overspending, etc.
* `export.php` should fetch and export data from real records

---

## 🧩 FILES PROVIDED

| File                     | Description                           |
| ------------------------ | ------------------------------------- |
| `sql/create_tables.sql`  | Creates all database tables           |
| `sql/insert_sample.sql`  | Adds sample users, categories, etc.   |
| `includes/db.php`        | Main database connector file          |
| `inserteddatasample.txt` | Summary of all inserted test data     |
| `test_login.php`         | Used to test login (can delete later) |
| `hash.php`               | Used to generate hashed passwords     |

---

## 📌 FINAL REMINDERS

✔️ Clone the repo into `htdocs`
✔️ Import the SQL files in phpMyAdmin
✔️ Include `db.php` in your PHP backend files
✔️ Start your XAMPP server before testing
✔️ Ask me if anything breaks!

---

🧠 Once everyone’s part is built, I’ll help finalize the ERD and write the system documentation. Let’s keep going strong!

– **Samy**, Member 5 – Database Infrastructure Lead



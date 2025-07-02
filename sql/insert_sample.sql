-- USERS TABLE (5 users with updated strong passwords)
INSERT INTO users (username, email, password, role) VALUES
('admin', 'admin@example.com', '$2y$10$/c0ilPMeYvZtadKtcqgkTOWiwFmOeB9T2Bkz9IEqxebIo8vlfLBhi', 'admin'),  
('sara', 'sara@gmail.com', '$2y$10$88kQWNDdoj8zjH5cIqiykuXCMMUMPpHQWsIb1s8Tg05BcIhEqmq8G', 'user'),       
('john', 'john.doe@gmail.com', '$2y$10$6UZCqIz8V1RgCW.PN6aUguwGGpM43ysIgXziDl7.lNAW7nH2Jsg2y', 'user'),    
('fatimah', 'fatimah@gmail.com', '$2y$10$DYrnlh0Ly1U1ddGZyxVaAOa.uNT5RmtMni99HKCn9RYzDYS6vgBva', 'user'),   
('azmi', 'azmi@hotmail.com', '$2y$10$SEImMWtrvquWWdJ.utLHzelz9PRoY7i2yIZypb4q/vswaNovMeZwa', 'user');    

-- Insert into categories table with updated type values
INSERT INTO categories (name, type) VALUES
('Food', 'expense'),
('Transport', 'expense'),
('Utilities', 'expense'),
('Entertainment', 'expense'),
('Healthcare', 'expense'),
('Others', 'expense'),
('Salary', 'income'),
('Bonus', 'income'),
('Investment', 'income'),
('Others', 'income');

-- TRANSACTIONS TABLE (5 sample entries)
INSERT INTO transactions (user_id, category_id, type, amount, note, date) VALUES
(1, 1, 'expense', 45.00, 'Lunch at cafe', '2025-06-01'),
(2, 2, 'expense', 12.50, 'Grab ride', '2025-06-02'),
(3, 3, 'expense', 120.00, 'Electricity bill', '2025-06-05'),
(4, 4, 'income', 250.00, 'Freelance gig', '2025-06-06'),
(5, 5, 'expense', 65.00, 'Pharmacy visit', '2025-06-07');

-- BUDGETS TABLE (5 sample entries)
INSERT INTO budgets (user_id, category_id, month, amount_limit) VALUES
(1, 1, 'June', 300.00),
(2, 2, 'June', 100.00),
(3, 3, 'June', 150.00),
(4, 4, 'June', 200.00),
(5, 5, 'June', 180.00);

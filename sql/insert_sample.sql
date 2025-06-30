-- USERS TABLE (5 sample users with unique passwords)
INSERT INTO users (username, email, password, role) VALUES
('admin', 'admin@example.com', '$2y$10$GQRoUXz2Q9sXYYf.R5kvq.29d3AWrh1.a9bcyxji7rjqqbXxDcRVK', 'admin'),
('sara', 'sara@gmail.com', '$2y$10$F/XZt43z6oVjtWRF8nGB0ei0bPdo5MuoyvQro3q/50xp6Ijr.Hzkm', 'user'),
('john', 'john.doe@gmail.com', '$2y$10$ikb7XRkTIVkAFxKA7OLsHOqebd7OjONdBXlbP9H92gir9Q8IFmXvO', 'user'),
('fatimah', 'fatimah@gmail.com', '$2y$10$tg1L0W2mlV1sLyAC1Ehgzu8m7QkqHz.xVfe2EhhS8rEyzGQo38OPW', 'user'),
('azmi', 'azmi@hotmail.com', '$2y$10$zUmbBCaUVx65nfEkvueY0uSwQJQfdbobrMaD9k4IwukGQdHSrf3Du', 'user');

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

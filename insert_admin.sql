USE pointage_db;

-- Insert new admin user (password: Admin2024!)
INSERT INTO users (name, email, password, role) VALUES 
('Administrateur', 'admin@fssm.uca.ma', '$2y$12$LQv3c1yqBWVHxq0WYbWXUeYI8eZ8eZ8eZ8eZ8eZ8eZ8eZ8eZ8eZ8eZ', 'admin');

-- Note: Le hash ci-dessus est un exemple. Exécutez generate_hash.php pour obtenir le vrai hash 
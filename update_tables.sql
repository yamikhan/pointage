USE pointage_db;

-- Supprimer l'ancienne table si elle existe
DROP TABLE IF EXISTS attendance;

-- Recréer la table avec la bonne structure
CREATE TABLE attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    date DATE NOT NULL,
    check_in TIME NOT NULL,
    check_out TIME,
    status ENUM('present', 'absent', 'late') DEFAULT 'present',
    remarks TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
); 
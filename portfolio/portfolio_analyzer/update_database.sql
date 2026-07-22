USE portfolio_analyzer;

-- Delete duplicate emails keeping the highest ID to prevent the 1062 error
DELETE t1 FROM users t1 INNER JOIN users t2 WHERE t1.id < t2.id AND t1.email = t2.email;

-- Make email unique in the users table
ALTER TABLE users ADD UNIQUE (email);

-- Create the analysis_history table
CREATE TABLE IF NOT EXISTS analysis_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    score INT NOT NULL,
    suggestions TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

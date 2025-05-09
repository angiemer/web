USE di_internet_technologies_project;

CREATE TABLE test (
    id INT AUTO_INCREMENT PRIMARY KEY,
    value TEXT
);

INSERT INTO test (value) VALUES
('The only way to do great work is to love what you do. - Steve Jobs'),
('In the end, we will remember not the words of our enemies, but the silence of our friends. - Martin Luther King Jr.');

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(40) NOT NULL,
    last_name VARCHAR(40) NOT NULL,
    username VARCHAR(40) NOT NULL UNIQUE,
    password VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE followers ( 
    follower_id INT NOT NULL,  /* follower */
    followed_id INT NOT NULL,  /* following */
    PRIMARY KEY (follower_id, followed_id), 
    FOREIGN KEY (follower_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (followed_id) REFERENCES users(id) ON DELETE CASCADE
);


CREATE TABLE lists (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    is_public BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE songs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    youtube_id VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE list_songs (   /* Αντιστίχοιση των τραγουδιών σε ποια λίστα ανήκουν */
    id INT AUTO_INCREMENT PRIMARY KEY,
    list_id INT NOT NULL,
    song_id INT NOT NULL,
    user_id INT NOT NULL,    
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (list_id) REFERENCES lists(id) ON DELETE CASCADE,  /* FOREIGN KEYS και ON DELETE CASCADE για ακεραιότητα */
    FOREIGN KEY (song_id) REFERENCES songs(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);


CREATE TABLE search_logs (   /* ιστορικό αναζητήσεων */
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    query VARCHAR(255) NOT NULL,
    searched_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);


CREATE TABLE favorites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    video_id VARCHAR(255) NOT NULL,
    title TEXT NOT NULL,
    thumbnail TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);




-- SQLITE Schema for Sharp-PHP Unit Tests

CREATE TABLE test_user (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    login VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(100) NOT NULL,
    salt VARCHAR(100) NOT NULL,
    blocked BOOLEAN DEFAULT FALSE
);

-- User logs are admin, admin

INSERT INTO test_user (login, password, salt)
VALUES ('admin', '$2y$08$pxfA4LlzVyXRPYVZH7czvu.gQQ8BNfzRdhejln2dwB7Bv6QafwAua', 'dummySalt');




CREATE TABLE test_tv_show (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(50) NOT NULL UNIQUE,
    episode_number SMALLINT DEFAULT 1
);


INSERT INTO test_tv_show (name, episode_number)
VALUES
('Malcolm in the middle', 151),
('Breaking Bad', 62),
('Better Call Saul', 63),
('South Park', 328),
('Twin Peaks', 48);

CREATE TABLE test_tv_show_producer (
    tv_show INT NOT NULL REFERENCES test_tv_show(id) ON DELETE CASCADE,
    name VARCHAR(100) NOT NULL
);

INSERT INTO test_tv_show_producer (tv_show, name)
VALUES
(1, 'Linwood Boomer'),
(1, 'Matthew Carlson'),
(2, 'Vince Gilligan'),
(2, 'Mark Johnson'),
(2, 'Michelle MacLaren'),
(3, 'Vince Gilligan'),
(3, 'Peter Gould'),
(3, 'Mark Johnson'),
(3, 'Melissa Bernstein'),
(3, 'Thomas Schnauz'),
(3, 'Gennifer Hutchison'),
(3, 'Diane Mercer'),
(3, 'Alison Tatlock'),
(3, 'Michael Morris'),
(4, 'Trey Parker'),
(4, 'Matt Stone'),
(4, 'Brian Graden'),
(4, 'Deborah Liebling'),
(4, 'Frank C. Agnone II'),
(4, 'Bruce Howell'),
(4, 'Anne Garefino'),
(5, 'Mark Frost'),
(5, 'David Lynch'),
(5, 'Sabrina S. Sutherland ')
;
-- Table for users
CREATE TABLE IF NOT EXISTS users
(
    id         INTEGER PRIMARY KEY AUTOINCREMENT,
    username   TEXT NOT NULL,
    token      TEXT NOT NULL UNIQUE,
    created_at DATETIME DEFAULT (STRFTIME('%Y-%m-%dT%H:%M:%fZ', 'NOW'))
);

-- Table for chatting groups
CREATE TABLE IF NOT EXISTS groups
(
    id         INTEGER PRIMARY KEY AUTOINCREMENT,
    group_name TEXT NOT NULL,
    created_at DATETIME DEFAULT (STRFTIME('%Y-%m-%dT%H:%M:%fZ', 'NOW'))
);

-- Table for messages
CREATE TABLE IF NOT EXISTS messages
(
    id         INTEGER PRIMARY KEY AUTOINCREMENT,
    group_id   INTEGER NOT NULL,
    user_id    INTEGER NOT NULL,
    content    TEXT    NOT NULL,
    created_at DATETIME DEFAULT (STRFTIME('%Y-%m-%dT%H:%M:%fZ', 'NOW')),
    FOREIGN KEY (group_id) REFERENCES groups (id),
    FOREIGN KEY (user_id) REFERENCES users (id)
);

-- 创建 user_groups 表
CREATE TABLE user_groups
(
    id        INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id   INTEGER NOT NULL,
    group_id  INTEGER NOT NULL,
    created_at DATETIME DEFAULT (STRFTIME('%Y-%m-%dT%H:%M:%fZ', 'NOW')),
    FOREIGN KEY (user_id) REFERENCES users (id),
    FOREIGN KEY (group_id) REFERENCES groups (id)
);
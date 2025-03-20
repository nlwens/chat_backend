--Insert Dummy Users
INSERT INTO users (username, token) VALUES ('user1', 'token1');
INSERT INTO users (username, token) VALUES ('user2', 'token2');
INSERT INTO users (username, token) VALUES ('user3', 'token3');

--Insert Dummy Groups
INSERT INTO groups (group_name) VALUES ('Group 1');
INSERT INTO groups (group_name) VALUES ('Group 2');
INSERT INTO groups (group_name) VALUES ('Group 3');

--Insert Dummy Messages
INSERT INTO messages (group_id, user_id, content, created_at)
VALUES (1, 1, 'First dummy Message!', '2023-01-01T00:00:00Z');

INSERT INTO messages (group_id, user_id, content)
VALUES (2, 1, 'Dummy message 2');

INSERT INTO messages (group_id, user_id, content)
VALUES (1, 3, 'Dummy message 3');

--Insert dummy users_groups data
INSERT INTO user_groups (user_id, group_id) VALUES (1, 1);
INSERT INTO user_groups (user_id, group_id) VALUES (1, 2);
INSERT INTO user_groups (user_id, group_id) VALUES (3, 1);
INSERT INTO user_groups (user_id, group_id) VALUES (2, 1);

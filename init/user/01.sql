
CREATE TABLE users (
    id INT NOT NULL PRIMARY KEY,
    username VARCHAR(32) NOT NULL UNIQUE
);


INSERT INTO users (id, username) VALUES (123, 'Pasha');
INSERT INTO users (id, username) VALUES (321, 'Jhon');
INSERT INTO users (id, username) VALUES (5555, 'Andy');
INSERT INTO users (id, username) VALUES (100111001, 'AngryBird');


CREATE TABLE user_subscription (
    user_id INT NOT NULL,
    subs_user_id INT NOT NULL,
    subscribed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP DEFAULT NULL
);

CREATE INDEX usr_sub_idx ON user_posts (user_id, deleted_at);

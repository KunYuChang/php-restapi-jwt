-- TASK 新增欄位並加上索引
ALTER TABLE task
ADD user_id INT NOT NULL,
ADD INDEX (user_id);

-- 查看 TASK
DESCRIBE task;
SELECT * FROM task;

-- 將所有的ROW的新欄位都設定值
UPDATE task SET user_id = 1;

-- 加上外來鍵的設定
ALTER TABLE task
ADD FOREIGN KEY (user_id)
REFERENCES user(id)
ON DELETE CASCADE 
ON UPDATE CASCADE;

-- refresh_token
CREATE TABLE refresh_token (
    token_hash VARCHAR(64) NOT NULL,
    expires_at INT UNSIGNED NOT NULL,
    PRIMARY KEY (token_hash),
    INDEX (expires_at)
);

DESCRIBE refresh_token;
SHOW indexes FROM refresh_token;
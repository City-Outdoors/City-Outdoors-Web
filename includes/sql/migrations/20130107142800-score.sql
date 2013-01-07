ALTER TABLE feature_checkin_question ADD score TEXT;
ALTER TABLE user_account ADD cached_score INT UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE feature_checkin_success ADD score MEDIUMINT UNSIGNED NOT NULL DEFAULT 0;

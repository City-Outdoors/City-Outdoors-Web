ALTER TABLE feature_checkin_question ADD active BOOLEAN NOT NULL DEFAULT '1';
ALTER TABLE feature_checkin_question ADD inactive_reason TEXT NULL;
ALTER TABLE feature_checkin_question ADD deleted BOOLEAN NOT NULL DEFAULT '0';

ALTER TABLE feature_content ADD is_spam_automatic BOOLEAN NOT NULL DEFAULT '0';
ALTER TABLE feature_content ADD is_spam_moderated BOOLEAN NOT NULL DEFAULT '0';

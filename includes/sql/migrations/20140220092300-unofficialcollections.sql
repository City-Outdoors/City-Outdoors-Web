CREATE TABLE organisation (
  id INT UNSIGNED AUTO_INCREMENT NOT NULL,
  title VARCHAR(255) NULL,
  description_text TEXT NULL,
  created_at DATETIME NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;

ALTER TABLE collection ADD organisation_id INT UNSIGNED NULL;
ALTER TABLE collection ADD CONSTRAINT collection_organisation_id  FOREIGN KEY (organisation_id) REFERENCES organisation(id);

ALTER TABLE feature_checkin_question ADD collection_id INT UNSIGNED NULL;
ALTER TABLE feature_checkin_question ADD CONSTRAINT feature_checkin_question_collection_id  FOREIGN KEY (collection_id) REFERENCES collection(id);

CREATE TABLE organisation_has_user (
  organisation_id INT UNSIGNED NOT NULL,
  user_account_id INT UNSIGNED NOT NULL,
  created_at DATETIME NOT NULL,
  PRIMARY KEY (organisation_id,user_account_id)
) ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE organisation_has_user ADD CONSTRAINT organisation_has_user_organisation_id  FOREIGN KEY (organisation_id) REFERENCES organisation(id);
ALTER TABLE organisation_has_user ADD CONSTRAINT organisation_has_user_user_account_id  FOREIGN KEY (user_account_id) REFERENCES user_account(id);




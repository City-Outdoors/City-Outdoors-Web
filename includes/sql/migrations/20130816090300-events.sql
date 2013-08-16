
CREATE TABLE event (
  id INT UNSIGNED AUTO_INCREMENT NOT NULL,
  title VARCHAR(255) NULL,
  description_text TEXT NULL,
  start_at  DATETIME NOT NULL,
  end_at  DATETIME NOT NULL,
  import_source VARCHAR(255) NULL,
  import_id VARCHAR(255) NULL,
  PRIMARY KEY(id)
) ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE TABLE feature_has_event (
  feature_id INT UNSIGNED NOT NULL,
  event_id INT UNSIGNED NOT NULL,
  PRIMARY KEY(feature_id, event_id)
) ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE feature_has_event ADD CONSTRAINT feature_has_event_feature_id  FOREIGN KEY (feature_id) REFERENCES feature(id);
ALTER TABLE feature_has_event ADD CONSTRAINT feature_has_event_event_id  FOREIGN KEY (event_id) REFERENCES event(id);


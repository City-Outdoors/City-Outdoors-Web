CREATE TABLE user_account (
  id INT UNSIGNED AUTO_INCREMENT NOT NULL,
  display_name VARCHAR(255) NOT NULL,
  email VARCHAR(255) NULL,
  password_crypted VARCHAR(255) NULL,
  password_salt VARCHAR(100) NULL,
  profile_url VARCHAR(255) NULL,
  twitter_id BIGINT UNSIGNED NULL,
  twitter_screen_name VARCHAR(50) NULL,
  twitter_token VARCHAR(250) NULL,
  twitter_token_secret VARCHAR(250) NULL,
  enabled TINYINT(1) NOT NULL DEFAULT 1,
  administrator TINYINT(1) NOT NULL DEFAULT 0,
  system_administrator TINYINT(1) NOT NULL DEFAULT 0,
  forgotten_password_code VARCHAR(20) NULL,
  forgotten_password_code_generated_at DATETIME NULL,
  created_at DATETIME NOT NULL,
  PRIMARY KEY(id)  
) ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
CREATE UNIQUE INDEX user_account_email ON user_account (email);
CREATE UNIQUE INDEX user_account_twitter ON user_account (twitter_id);

CREATE TABLE user_session (
  user_account_id INT UNSIGNED NOT NULL,
  id VARCHAR(100) NOT NULL,
  created_at DATETIME NOT NULL,
  last_used_at DATETIME NOT NULL,
  PRIMARY KEY(user_account_id, id)
) ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE user_session ADD CONSTRAINT user_session_user_account_id  FOREIGN KEY (user_account_id) REFERENCES user_account(id);


CREATE TABLE feature (
  id INT UNSIGNED AUTO_INCREMENT NOT NULL,
  title VARCHAR(255) NULL,
  thumbnail_url VARCHAR(255) NULL,
  point_lat REAL NOT NULL,
  point_lng REAL NOT NULL,
  bounds_min_lat REAL NOT NULL,
  bounds_max_lat REAL NOT NULL,
  bounds_min_lng REAL NOT NULL,
  bounds_max_lng REAL NOT NULL,
  created_at DATETIME  NOT NULL,
  PRIMARY KEY(id)  
) ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE TABLE collection (
  id INT UNSIGNED AUTO_INCREMENT  NOT NULL,
  title VARCHAR(255) NOT NULL,
  description TEXT NULL,
  thumbnail_url VARCHAR(255) NULL,
  slug VARCHAR(100) NOT NULL,
  created_at DATETIME NOT NULL,
  created_by INT UNSIGNED NOT NULL,
  icon_width TINYINT NULL,
  icon_height TINYINT NULL,
  icon_offset_x TINYINT NULL,
  icon_offset_y TINYINT NULL,
  icon_url VARCHAR(255) NULL,
  PRIMARY KEY(id)
) ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE UNIQUE INDEX collection_slug ON collection (slug);
ALTER TABLE collection ADD CONSTRAINT collection_created_by  FOREIGN KEY (created_by) REFERENCES user_account(id);

CREATE TABLE collection_has_field (
  id INT UNSIGNED AUTO_INCREMENT NOT NULL,
  collection_id INT UNSIGNED NOT NULL,
  title VARCHAR(100) NOT NULL,
  type VARCHAR(100) NOT NULL,
  is_summary TINYINT(1) NOT NULL DEFAULT 0,
  in_content_areas VARCHAR(250) NULL,
  sort_order SMALLINT,
  PRIMARY KEY(id)
) ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE collection_has_field ADD CONSTRAINT collection_has_field_collection_id  FOREIGN KEY (collection_id) REFERENCES collection(id);

CREATE TABLE item (
  id INT UNSIGNED AUTO_INCREMENT NOT NULL,
  parent_id INT UNSIGNED NULL,
  collection_id INT UNSIGNED NOT NULL,
  feature_id INT UNSIGNED NOT NULL,
  slug VARCHAR(100) NOT NULL,
  free_text_search TEXT NULL,
  created_at DATETIME NOT NULL,
  created_by INT UNSIGNED NULL,
  deleted TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY(id)
) ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
CREATE UNIQUE INDEX item_slug ON item (collection_id, slug);
ALTER TABLE item ADD CONSTRAINT item_collection_id  FOREIGN KEY (collection_id) REFERENCES collection(id);
ALTER TABLE item ADD CONSTRAINT item_feature_id  FOREIGN KEY (feature_id) REFERENCES feature(id);
ALTER TABLE item ADD CONSTRAINT item_created_by  FOREIGN KEY (created_by) REFERENCES user_account(id);
ALTER TABLE item ADD CONSTRAINT item_parent_id  FOREIGN KEY (parent_id) REFERENCES item(id);

CREATE TABLE item_has_string_field (
  id INT UNSIGNED AUTO_INCREMENT NOT NULL,
  item_id INT UNSIGNED  NOT NULL,
  field_id INT UNSIGNED  NOT NULL,
  field_value VARCHAR(250) NOT NULL,
  is_latest BOOLEAN NOT NULL DEFAULT '1',
  created_at DATETIME NOT NULL,
  created_by INT UNSIGNED NOT NULL,
  PRIMARY KEY(id)
) ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE item_has_string_field ADD CONSTRAINT item_has_string_field_item_id  FOREIGN KEY (item_id) REFERENCES item(id);
ALTER TABLE item_has_string_field ADD CONSTRAINT item_has_string_field_field_id  FOREIGN KEY (field_id) REFERENCES collection_has_field(id);
ALTER TABLE item_has_string_field ADD CONSTRAINT item_has_string_field_created_by  FOREIGN KEY (created_by) REFERENCES user_account(id);

CREATE TABLE item_has_text_field (
  id INT UNSIGNED AUTO_INCREMENT NOT NULL,
  item_id INT UNSIGNED  NOT NULL,
  field_id INT UNSIGNED  NOT NULL,
  field_value MEDIUMTEXT NOT NULL,
  is_latest BOOLEAN NOT NULL DEFAULT '1',
  created_at DATETIME NOT NULL,
  created_by INT UNSIGNED NOT NULL,
  PRIMARY KEY(id)
) ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE item_has_text_field ADD CONSTRAINT item_has_text_field_item_id  FOREIGN KEY (item_id) REFERENCES item(id);
ALTER TABLE item_has_text_field ADD CONSTRAINT item_has_text_field_field_id  FOREIGN KEY (field_id) REFERENCES collection_has_field(id);
ALTER TABLE item_has_text_field ADD CONSTRAINT item_has_text_field_created_by  FOREIGN KEY (created_by) REFERENCES user_account(id);

CREATE TABLE item_has_html_field (
  id INT UNSIGNED AUTO_INCREMENT NOT NULL,
  item_id INT UNSIGNED  NOT NULL,
  field_id INT UNSIGNED  NOT NULL,
  field_value MEDIUMTEXT NOT NULL,
  is_latest BOOLEAN NOT NULL DEFAULT '1',
  created_at DATETIME NOT NULL,
  created_by INT UNSIGNED NOT NULL,
  PRIMARY KEY(id)
) ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE item_has_html_field ADD CONSTRAINT item_has_html_field_item_id  FOREIGN KEY (item_id) REFERENCES item(id);
ALTER TABLE item_has_html_field ADD CONSTRAINT item_has_html_field_field_id  FOREIGN KEY (field_id) REFERENCES collection_has_field(id);
ALTER TABLE item_has_html_field ADD CONSTRAINT item_has_html_field_created_by  FOREIGN KEY (created_by) REFERENCES user_account(id);



CREATE TABLE feature_content (
  id INT UNSIGNED AUTO_INCREMENT NOT NULL,
  feature_id INT UNSIGNED NOT NULL,
  comment_body TEXT NOT NULL,
  created_at DATETIME  NOT NULL,
  created_by INT UNSIGNED NULL,
  created_name VARCHAR(255) NULL,
  created_email VARCHAR(255) NULL,
  approved_at DATETIME  NULL,
  approved_by  INT UNSIGNED NULL,
  rejected_at DATETIME  NULL,
  rejected_by  INT UNSIGNED NULL,
  is_report BOOLEAN NOT NULL DEFAULT '0',
  promoted TINYINT(1) NOT NULL DEFAULT 0,
  user_agent VARCHAR(255) NULL,
  ip VARCHAR(50) NULL,
  PRIMARY KEY(id)
) ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE feature_content ADD CONSTRAINT feature_content_feature_id  FOREIGN KEY (feature_id) REFERENCES feature(id);
ALTER TABLE feature_content ADD CONSTRAINT feature_content_created_by  FOREIGN KEY (created_by) REFERENCES user_account(id);
ALTER TABLE feature_content ADD CONSTRAINT feature_content_approved_by  FOREIGN KEY (approved_by) REFERENCES user_account(id);
ALTER TABLE feature_content ADD CONSTRAINT feature_content_rejected_by  FOREIGN KEY (rejected_by) REFERENCES user_account(id);

CREATE TABLE feature_content_image (
  id INT UNSIGNED AUTO_INCREMENT NOT NULL,
  feature_content_id INT UNSIGNED NOT NULL,
  full_filename VARCHAR(255) NULL,
  normal_filename VARCHAR(255) NULL,
  thumb_filename VARCHAR(255) NULL,  
  PRIMARY KEY(id)
) ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE feature_content_image ADD CONSTRAINT feature_content_image_feature_content_id  FOREIGN KEY (feature_content_id) REFERENCES feature_content(id);

CREATE TABLE feature_checkin_question (
  id INT UNSIGNED AUTO_INCREMENT NOT NULL,
  feature_id INT UNSIGNED NOT NULL,
  question TEXT NOT NULL,
  answers TEXT NOT NULL,
  created_at  DATETIME NOT NULL,
  PRIMARY KEY(id)
) ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE TABLE feature_checkin_success (
  id INT UNSIGNED AUTO_INCREMENT NOT NULL,
  user_account_id INT UNSIGNED NOT NULL,
  feature_checkin_question_id INT UNSIGNED NOT NULL,
  answer_given VARCHAR(255) NOT NULL,
  created_at  DATETIME NOT NULL,
  user_agent VARCHAR(255) NULL,
  ip VARCHAR(50) NULL,
  PRIMARY KEY(id)
) ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE feature_checkin_success ADD CONSTRAINT feature_checkin_success_feature_checkin_question_id  FOREIGN KEY (feature_checkin_question_id) REFERENCES feature_checkin_question(id);
ALTER TABLE feature_checkin_success ADD CONSTRAINT feature_checkin_success_user_account_id  FOREIGN KEY (user_account_id) REFERENCES user_account(id);

CREATE TABLE feature_checkin_failure (
  id INT UNSIGNED AUTO_INCREMENT NOT NULL,
  user_account_id INT UNSIGNED NOT NULL,
  feature_checkin_question_id INT UNSIGNED NOT NULL,
  answer_given VARCHAR(255) NOT NULL,
  created_at  DATETIME NOT NULL,
  user_agent VARCHAR(255) NULL,
  ip VARCHAR(50) NULL,
  PRIMARY KEY(id)
) ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE feature_checkin_failure ADD CONSTRAINT feature_checkin_failure_feature_checkin_question_id  FOREIGN KEY (feature_checkin_question_id) REFERENCES feature_checkin_question(id);
ALTER TABLE feature_checkin_failure ADD CONSTRAINT feature_checkin_failure_user_account_id  FOREIGN KEY (user_account_id) REFERENCES user_account(id);

CREATE TABLE feature_favourite (
  user_account_id INT UNSIGNED NOT NULL,
  feature_id INT UNSIGNED NOT NULL,
  favourited_at DATETIME NOT NULL,
  created_at  DATETIME NOT NULL,
  user_agent VARCHAR(255) NULL,
  ip VARCHAR(50) NULL,
  PRIMARY KEY(user_account_id, feature_id)
) ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE feature_favourite ADD CONSTRAINT feature_favourite_feature_id  FOREIGN KEY (feature_id) REFERENCES feature(id);
ALTER TABLE feature_favourite ADD CONSTRAINT feature_favourite_user_account_id  FOREIGN KEY (user_account_id) REFERENCES user_account(id);


CREATE TABLE cms_content (
  id INT UNSIGNED AUTO_INCREMENT NOT NULL,
  page_slug VARCHAR(255) NULL,
  page_title VARCHAR(255) NULL,
  block_slug VARCHAR(255) NULL,
  imported TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY(id)
) ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
CREATE UNIQUE INDEX cms_content_page_slug ON cms_content (page_slug);
CREATE UNIQUE INDEX cms_content_block_slug ON cms_content (block_slug);


CREATE TABLE cms_content_version (
  cms_content_id INT UNSIGNED NOT NULL,
  version SMALLINT UNSIGNED NOT NULL,
  html TEXT NOT NULL,
  created_at DATETIME  NOT NULL,
  created_by INT UNSIGNED NOT NULL, 
  PRIMARY KEY(cms_content_id,version)
) ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE cms_content_version  ADD CONSTRAINT cms_content_version_cms_content_id  FOREIGN KEY (cms_content_id) REFERENCES cms_content(id);
ALTER TABLE cms_content_version  ADD CONSTRAINT cms_content_version_created_by  FOREIGN KEY (created_by) REFERENCES user_account(id);


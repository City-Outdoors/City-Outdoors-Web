
CREATE TABLE feature_checkin_question_possible_answer (
  id INT UNSIGNED AUTO_INCREMENT NOT NULL,
  feature_checkin_question_id INT UNSIGNED NOT NULL,
  answer TEXT NOT NULL,
  score TEXT NOT NULL,
  created_at  DATETIME NOT NULL,
  sort_order SMALLINT NULL DEFAULT 0,
  PRIMARY KEY(id)
) ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE feature_checkin_question_possible_answer ADD CONSTRAINT feature_checkin_question_possible_answer_fcq_id  FOREIGN KEY (feature_checkin_question_id) REFERENCES feature_checkin_question(id);


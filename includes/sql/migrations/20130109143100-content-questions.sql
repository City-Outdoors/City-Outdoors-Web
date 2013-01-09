ALTER TABLE feature_checkin_question MODIFY answers TEXT NULL;
ALTER TABLE feature_checkin_success MODIFY answer_given VARCHAR(255) NULL;
ALTER TABLE feature_checkin_success ADD feature_content_id INT UNSIGNED NULL;
ALTER TABLE feature_checkin_success ADD CONSTRAINT feature_checkin_success_feature_content_id FOREIGN KEY (feature_content_id) REFERENCES feature_content(id);

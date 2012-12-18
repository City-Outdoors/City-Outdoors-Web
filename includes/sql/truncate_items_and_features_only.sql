DELETE FROM feature_checkin_success;
DELETE FROM feature_checkin_question;
DELETE FROM feature_content_image;
DELETE FROM feature_content;
DELETE FROM item_has_string_field;
DELETE FROM item_has_html_field;
DELETE FROM item_has_text_field;
DELETE FROM item WHERE parent_id IS NOT NULL;
DELETE FROM item;
DELETE FROM feature_checkin;
DELETE FROM feature_favourite;
DELETE FROM feature ;



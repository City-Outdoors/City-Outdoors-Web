DELETE FROM cms_content_version;
DELETE FROM cms_content;
DELETE FROM feature_checkin_success;
DELETE FROM feature_checkin_question;
DELETE FROM feature_favourite;
DELETE FROM feature_content_image;
DELETE FROM feature_content;
DELETE FROM item_has_string_field;
DELETE FROM item_has_html_field;
DELETE FROM item_has_text_field;
UPDATE item SET parent_id = null;
DELETE FROM item;
DELETE FROM collection_has_field ;
DELETE FROM collection ;
DELETE FROM feature ;
DELETE FROM user_session ;
DELETE FROM user_account ;


INSERT INTO user_account (id,display_name,email,created_at, password_crypted,password_salt ) VALUES (1,'James','james@jarofgreen.co.uk','2012-01-01 12:00:00','$2mV0NZp92R3g','vdjgHs5PYXw9SHT7cJh3kV');

INSERT INTO collection (id,title,slug,created_at,created_by) VALUES (1,'Playgrounds','playgrounds','2012-01-01 12:00:00',1);

INSERT INTO collection_has_field (id, collection_id,title,type,sort_order) VALUES (1, 1, 'Title', 'STRING', 0);

INSERT INTO feature (point_lat,point_lng,created_at) VALUES (55.9607,-3.1957,'2012-01-01 12:00:00');
INSERT INTO item (id,collection_id,slug,feature_id,created_at) VALUES (1,1,'kingjames',1,'2012-01-01 12:00:00');
INSERT INTO item_has_string_field(item_id,field_id,field_value,created_at,created_by) VALUES (1,1,'King James VI','2012-01-01 12:00:00',1);

INSERT INTO feature (point_lat,point_lng,created_at) VALUES (55.9625746,-3.212924,'2012-01-01 12:00:00');
INSERT INTO item (id,collection_id,slug,feature_id,created_at) VALUES (2,1,'inverleith',2,'2012-01-01 12:00:00');
INSERT INTO item_has_string_field(item_id,field_id,field_value,created_at,created_by) VALUES (2,1,'Inverleith','2012-01-01 12:00:00',1);




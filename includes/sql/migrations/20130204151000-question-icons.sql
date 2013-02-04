ALTER TABLE collection ADD question_icon_width TINYINT NULL;
ALTER TABLE collection ADD question_icon_height TINYINT NULL;
ALTER TABLE collection ADD question_icon_offset_x TINYINT NULL;
ALTER TABLE collection ADD question_icon_offset_y TINYINT NULL;
ALTER TABLE collection ADD question_icon_url VARCHAR(255) NULL;
UPDATE collection SET question_icon_width=icon_width, question_icon_height=icon_height, 
  question_icon_offset_x=icon_offset_x, question_icon_offset_y=icon_offset_y,
  question_icon_url=icon_url;

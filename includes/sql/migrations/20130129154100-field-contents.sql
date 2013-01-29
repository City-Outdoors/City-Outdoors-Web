ALTER TABLE collection_has_field ADD field_contents_slug VARCHAR(255) NULL;
CREATE UNIQUE INDEX collection_has_field_field_contents_slug ON collection_has_field (field_contents_slug);


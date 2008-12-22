CREATE TABLE PREFIX_product_attribute_image (
  id_product_attribute int(10) NOT NULL,
  id_image int(10) NOT NULL,
  PRIMARY KEY(id_product_attribute, id_image)
);

INSERT INTO PREFIX_product_attribute_image (id_image, id_product_attribute) (SELECT id_image, id_product_attribute FROM PREFIX_product_attribute);

ALTER TABLE PREFIX_product_attribute DROP id_image;

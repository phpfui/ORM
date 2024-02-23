-- -----------------------------------------------------
-- Table `customer`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `customer`;
CREATE TABLE IF NOT EXISTS `customer` (
  `customer_id` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
  `company` VARCHAR(50) NULL DEFAULT NULL,
  `last_name` VARCHAR(50) NULL DEFAULT NULL,
  `first_name` VARCHAR(50) NULL DEFAULT NULL,
  `email_address` VARCHAR(50) NULL DEFAULT NULL,
  `job_title` VARCHAR(50) NULL DEFAULT NULL,
  `business_phone` VARCHAR(25) NULL DEFAULT NULL,
  `home_phone` VARCHAR(25) NULL DEFAULT NULL,
  `mobile_phone` VARCHAR(25) NULL DEFAULT NULL,
  `fax_number` VARCHAR(25) NULL DEFAULT NULL,
  `address` LONGTEXT NULL DEFAULT NULL,
  `city` VARCHAR(50) NULL DEFAULT NULL,
  `state_province` VARCHAR(50) NULL DEFAULT NULL,
  `zip_postal_code` VARCHAR(15) NULL DEFAULT NULL,
  `country_region` VARCHAR(50) NULL DEFAULT NULL,
  `web_page` LONGTEXT NULL DEFAULT NULL,
  `notes` LONGTEXT NULL DEFAULT NULL,
  `attachments` LONGBLOB NULL DEFAULT NULL);
CREATE INDEX `customer_city` ON `customer` (`city`);
CREATE INDEX `customer_company` ON `customer` (`company`);
CREATE INDEX `customer_first_name` ON `customer` (`first_name`);
CREATE INDEX `customer_last_name` ON `customer` (`last_name`);
CREATE INDEX `customer_zip_postal_code` ON `customer` (`zip_postal_code`);
CREATE INDEX `customer_state_province` ON `customer` (`state_province`);


-- -----------------------------------------------------
-- Table `employee`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `employee`;
CREATE TABLE IF NOT EXISTS `employee` (
  `employee_id` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
  `company` VARCHAR(50) NULL DEFAULT NULL,
  `last_name` VARCHAR(50) NULL DEFAULT NULL,
  `first_name` VARCHAR(50) NULL DEFAULT NULL,
  `email_address` VARCHAR(50) NULL DEFAULT NULL,
  `job_title` VARCHAR(50) NULL DEFAULT NULL,
  `business_phone` VARCHAR(25) NULL DEFAULT NULL,
  `home_phone` VARCHAR(25) NULL DEFAULT NULL,
  `mobile_phone` VARCHAR(25) NULL DEFAULT NULL,
  `fax_number` VARCHAR(25) NULL DEFAULT NULL,
  `address` LONGTEXT NULL DEFAULT NULL,
  `city` VARCHAR(50) NULL DEFAULT NULL,
  `state_province` VARCHAR(50) NULL DEFAULT NULL,
  `zip_postal_code` VARCHAR(15) NULL DEFAULT NULL,
  `country_region` VARCHAR(50) NULL DEFAULT NULL,
  `web_page` LONGTEXT NULL DEFAULT NULL,
  `notes` LONGTEXT NULL DEFAULT NULL,
  `attachments` LONGBLOB NULL DEFAULT NULL);
CREATE INDEX `employee_city` ON `employee` (`city`);
CREATE INDEX `employee_company` ON `employee` (`company`);
CREATE INDEX `employee_first_name` ON `employee` (`first_name`);
CREATE INDEX `employee_last_name` ON `employee` (`last_name`);
CREATE INDEX `employee_zip_postal_code` ON `employee` (`zip_postal_code`);
CREATE INDEX `employee_state_province` ON `employee` (`state_province`);


-- -----------------------------------------------------
-- Table `privilege`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `privilege`;
CREATE TABLE IF NOT EXISTS `privilege` (
  `privilege_id` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
  `privilege` VARCHAR(50) NULL DEFAULT NULL);


-- -----------------------------------------------------
-- Table `employee_privilege`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `employee_privilege`;
CREATE TABLE IF NOT EXISTS `employee_privilege` (
  `employee_id` INTEGER NOT NULL,
  `privilege_id` INTEGER NOT NULL,
  CONSTRAINT `fk_employee_privileges_employees1`
    FOREIGN KEY (`employee_id`)
    REFERENCES `employee` (`employee_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_employee_privileges_privileges1`
    FOREIGN KEY (`privilege_id`)
    REFERENCES `privilege` (`privilege_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION);

CREATE INDEX `employee_privilege_employee_id` ON `employee_privilege` (`employee_id`);
CREATE INDEX `employee_privilege_privilege_id` ON `employee_privilege` (`privilege_id`);

-- -----------------------------------------------------
-- Table `inventory_transaction_type`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `inventory_transaction_type`;
CREATE TABLE IF NOT EXISTS `inventory_transaction_type` (
  `inventory_transaction_type_id` INTEGER NOT NULL PRIMARY KEY,
  `inventory_transaction_type_name` VARCHAR(50) NOT NULL);


-- -----------------------------------------------------
-- Table `shipper`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `shipper`;
CREATE TABLE IF NOT EXISTS `shipper` (
  `shipper_id` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
  `company` VARCHAR(50) NULL DEFAULT NULL,
  `last_name` VARCHAR(50) NULL DEFAULT NULL,
  `first_name` VARCHAR(50) NULL DEFAULT NULL,
  `email_address` VARCHAR(50) NULL DEFAULT NULL,
  `job_title` VARCHAR(50) NULL DEFAULT NULL,
  `business_phone` VARCHAR(25) NULL DEFAULT NULL,
  `home_phone` VARCHAR(25) NULL DEFAULT NULL,
  `mobile_phone` VARCHAR(25) NULL DEFAULT NULL,
  `fax_number` VARCHAR(25) NULL DEFAULT NULL,
  `address` LONGTEXT NULL DEFAULT NULL,
  `city` VARCHAR(50) NULL DEFAULT NULL,
  `state_province` VARCHAR(50) NULL DEFAULT NULL,
  `zip_postal_code` VARCHAR(15) NULL DEFAULT NULL,
  `country_region` VARCHAR(50) NULL DEFAULT NULL,
  `web_page` LONGTEXT NULL DEFAULT NULL,
  `notes` LONGTEXT NULL DEFAULT NULL,
  `attachments` LONGBLOB NULL DEFAULT NULL);
CREATE INDEX `shipper_city` ON `shipper` (`city`);
CREATE INDEX `shipper_company` ON `shipper` (`company`);
CREATE INDEX `shipper_first_name` ON `shipper` (`first_name`);
CREATE INDEX `shipper_last_name` ON `shipper` (`last_name`);
CREATE INDEX `shipper_zip_postal_code` ON `shipper` (`zip_postal_code`);
CREATE INDEX `shipper_state_province` ON `shipper` (`state_province`);


-- -----------------------------------------------------
-- Table `order_tax_status`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `order_tax_status`;
CREATE TABLE IF NOT EXISTS `order_tax_status` (
  `order_tax_status_id` INTEGER NOT NULL PRIMARY KEY,
  `order_tax_status_name` VARCHAR(50) NOT NULL);


-- -----------------------------------------------------
-- Table `order_status`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `order_status`;
CREATE TABLE IF NOT EXISTS `order_status` (
  `order_status_id` INTEGER NOT NULL PRIMARY KEY,
  `order_status_name` VARCHAR(50) NOT NULL);


-- -----------------------------------------------------
-- Table `order`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `order`;
CREATE TABLE IF NOT EXISTS `order` (
  `order_id` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
  `employee_id` INTEGER NULL DEFAULT NULL,
  `customer_id` INTEGER NULL DEFAULT NULL,
  `order_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `shipped_date` DATETIME NULL DEFAULT NULL,
  `shipper_id` INTEGER NULL DEFAULT NULL,
  `ship_name` VARCHAR(50) NULL DEFAULT NULL,
  `ship_address` LONGTEXT NULL DEFAULT NULL,
  `ship_city` VARCHAR(50) NULL DEFAULT NULL,
  `ship_state_province` VARCHAR(50) NULL DEFAULT NULL,
  `ship_zip_postal_code` VARCHAR(50) NULL DEFAULT NULL,
  `ship_country_region` VARCHAR(50) NULL DEFAULT NULL,
  `shipping_fee` DECIMAL(19,4) NULL DEFAULT '0.0000',
  `taxes` DECIMAL(19,4) NULL DEFAULT '0.0000',
  `payment_type` VARCHAR(50) NULL DEFAULT NULL,
  `paid_date` DATETIME NULL DEFAULT NULL,
  `notes` LONGTEXT NULL DEFAULT NULL,
  `tax_rate` DOUBLE NULL DEFAULT '0',
  `order_tax_status_id` INTEGER NULL DEFAULT NULL,
  `order_status_id` INTEGER NULL DEFAULT '0',
  CONSTRAINT `fk_order_customers`
    FOREIGN KEY (`customer_id`)
    REFERENCES `customer` (`customer_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_order_employee1`
    FOREIGN KEY (`employee_id`)
    REFERENCES `employee` (`employee_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_order_shipper1`
    FOREIGN KEY (`shipper_id`)
    REFERENCES `shipper` (`shipper_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_order_order_tax_status1`
    FOREIGN KEY (`order_tax_status_id`)
    REFERENCES `order_tax_status` (`order_tax_status_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_order_order_status1`
    FOREIGN KEY (`order_status_id`)
    REFERENCES `order_status` (`order_status_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION);

CREATE INDEX `order_customer_id` ON `order` (`customer_id`);
CREATE INDEX `order_employee_id` ON `order` (`employee_id`);
CREATE INDEX `order_shipper_id` ON `order` (`shipper_id`);
CREATE INDEX `order_tax_status_name` ON `order` (`order_tax_status_id`);
CREATE INDEX `order_ship_zip_postal_code` ON `order` (`ship_zip_postal_code`);

-- -----------------------------------------------------
-- Table `product`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `product`;
CREATE TABLE IF NOT EXISTS `product` (
  `product_id` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
  `product_code` VARCHAR(25) NULL DEFAULT NULL,
  `product_name` VARCHAR(50) NULL DEFAULT NULL,
  `description` LONGTEXT NULL DEFAULT NULL,
  `standard_cost` DECIMAL(19,4) NULL DEFAULT '0.0000',
  `list_price` DECIMAL(19,4) NOT NULL DEFAULT '0.0000',
  `reorder_level` INTEGER NULL DEFAULT NULL,
  `target_level` INTEGER NULL DEFAULT NULL,
  `quantity_per_unit` VARCHAR(50) NULL DEFAULT NULL,
  `discontinued` INTEGER NOT NULL DEFAULT '0',
  `minimum_reorder_quantity` INTEGER NULL DEFAULT NULL,
  `category` VARCHAR(50) NULL DEFAULT NULL,
  `attachments` LONGBLOB NULL DEFAULT NULL);
CREATE INDEX `product_product_code` ON `product` (`product_code`);


-- -----------------------------------------------------
-- Table `purchase_order_status`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `purchase_order_status`;
CREATE TABLE IF NOT EXISTS `purchase_order_status` (
  `purchase_order_status_id` INTEGER NOT NULL PRIMARY KEY,
  `purchase_order_status_name` VARCHAR(50) NULL DEFAULT NULL);


-- -----------------------------------------------------
-- Table `supplier`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `supplier`;
CREATE TABLE IF NOT EXISTS `supplier` (
  `supplier_id` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
  `company` VARCHAR(50) NULL DEFAULT NULL,
  `last_name` VARCHAR(50) NULL DEFAULT NULL,
  `first_name` VARCHAR(50) NULL DEFAULT NULL,
  `email_address` VARCHAR(50) NULL DEFAULT NULL,
  `job_title` VARCHAR(50) NULL DEFAULT NULL,
  `business_phone` VARCHAR(25) NULL DEFAULT NULL,
  `home_phone` VARCHAR(25) NULL DEFAULT NULL,
  `mobile_phone` VARCHAR(25) NULL DEFAULT NULL,
  `fax_number` VARCHAR(25) NULL DEFAULT NULL,
  `address` LONGTEXT NULL DEFAULT NULL,
  `city` VARCHAR(50) NULL DEFAULT NULL,
  `state_province` VARCHAR(50) NULL DEFAULT NULL,
  `zip_postal_code` VARCHAR(15) NULL DEFAULT NULL,
  `country_region` VARCHAR(50) NULL DEFAULT NULL,
  `web_page` LONGTEXT NULL DEFAULT NULL,
  `notes` LONGTEXT NULL DEFAULT NULL,
  `attachments` LONGBLOB NULL DEFAULT NULL);
CREATE INDEX `supplier_city` ON `supplier` (`city`);
CREATE INDEX `supplier_company` ON `supplier` (`company`);
CREATE INDEX `supplier_first_name` ON `supplier` (`first_name`);
CREATE INDEX `supplier_last_name` ON `supplier` (`last_name`);
CREATE INDEX `supplier_zip_postal_code` ON `supplier` (`zip_postal_code`);
CREATE INDEX `supplier_state_province` ON `supplier` (`state_province`);

-- -----------------------------------------------------
-- Table `product_supplier`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `product_supplier`;
CREATE TABLE IF NOT EXISTS `product_supplier` (
  `product_id` INTEGER NOT NULL,
  `supplier_id` INTEGER NOT NULL,
  CONSTRAINT `fk_product_supplier_product`
    FOREIGN KEY (`product_id`)
    REFERENCES `product` (`product_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_product_supplier_supplier`
    FOREIGN KEY (`supplier_id`)
    REFERENCES `supplier` (`supplier_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION);

CREATE INDEX `product_supplier_product_id` ON `product_supplier` (`product_id`);
CREATE INDEX `product_supplier_supplier_id` ON `product_supplier` (`supplier_id`);


-- -----------------------------------------------------
-- Table `purchase_order`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `purchase_order`;
CREATE TABLE IF NOT EXISTS `purchase_order` (
  `purchase_order_id` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
  `supplier_id` INTEGER NULL DEFAULT NULL,
  `created_by` INTEGER NULL DEFAULT NULL,
  `submitted_date` DATETIME NULL DEFAULT NULL,
  `creation_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `purchase_order_status_id` INTEGER NULL DEFAULT '0',
  `expected_date` DATETIME NULL DEFAULT NULL,
  `shipping_fee` DECIMAL(19,4) NOT NULL DEFAULT '0.0000',
  `taxes` DECIMAL(19,4) NOT NULL DEFAULT '0.0000',
  `payment_date` DATETIME NULL DEFAULT NULL,
  `payment_amount` DECIMAL(19,4) NULL DEFAULT '0.0000',
  `payment_method` VARCHAR(50) NULL DEFAULT NULL,
  `notes` LONGTEXT NULL DEFAULT NULL,
  `approved_by` INTEGER NULL DEFAULT NULL,
  `approved_date` DATETIME NULL DEFAULT NULL,
  `submitted_by` INTEGER NULL DEFAULT NULL,
  CONSTRAINT `fk_purchase_order_employees1`
    FOREIGN KEY (`created_by`)
    REFERENCES `employee` (`employee_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_purchase_order_purchase_order_status1`
    FOREIGN KEY (`purchase_order_status_id`)
    REFERENCES `purchase_order_status` (`purchase_order_status_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_purchase_order_suppliers1`
    FOREIGN KEY (`supplier_id`)
    REFERENCES `supplier` (`supplier_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION);

CREATE INDEX `purchase_order_created_by` ON `purchase_order` (`created_by`);
CREATE INDEX `purchase_order_status_id` ON `purchase_order` (`purchase_order_status_id`);
CREATE INDEX `purchase_order_supplier_id` ON `purchase_order` (`supplier_id`);

-- -----------------------------------------------------
-- Table `inventory_transaction`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `inventory_transaction`;
CREATE TABLE IF NOT EXISTS `inventory_transaction` (
  `inventory_transaction_id` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
  `inventory_transaction_type_id` INTEGER NOT NULL,
  `transaction_created_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `transaction_modified_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `product_id` INTEGER NOT NULL,
  `quantity` INTEGER NOT NULL,
  `purchase_order_id` INTEGER NULL DEFAULT NULL,
  `order_id` INTEGER NULL DEFAULT NULL,
  `comments` VARCHAR(255) NULL DEFAULT NULL,
  CONSTRAINT `fk_inventory_transactions_order1`
    FOREIGN KEY (`order_id`)
    REFERENCES `order` (`order_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_inventory_transactions_products1`
    FOREIGN KEY (`product_id`)
    REFERENCES `product` (`product_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_inventory_transactions_purchase_order1`
    FOREIGN KEY (`purchase_order_id`)
    REFERENCES `purchase_order` (`purchase_order_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_inventory_transactions_inventory_transaction_type1`
    FOREIGN KEY (`inventory_transaction_type_id`)
    REFERENCES `inventory_transaction_type` (`inventory_transaction_type_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION);

CREATE INDEX `inventory_transaction_order_id` ON `inventory_transaction` (`order_id`);
CREATE INDEX `inventory_transaction_product_id` ON `inventory_transaction` (`product_id`);
CREATE INDEX `inventory_transaction_purchase_order_id` ON `inventory_transaction` (`purchase_order_id`);
CREATE INDEX `inventory_transaction_inventory_transaction_type_id` ON `inventory_transaction` (`inventory_transaction_type_id`);

-- -----------------------------------------------------
-- Table `invoice`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `invoice`;
CREATE TABLE IF NOT EXISTS `invoice` (
  `invoice_id` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
  `order_id` INTEGER NULL DEFAULT NULL,
  `invoice_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `due_date` DATETIME NULL DEFAULT NULL,
  `tax` DECIMAL(19,4) NULL DEFAULT '0.0000',
  `shipping` DECIMAL(19,4) NULL DEFAULT '0.0000',
  `amount_due` DECIMAL(19,4) NULL DEFAULT '0.0000',
  CONSTRAINT `fk_invoices_order1`
    FOREIGN KEY (`order_id`)
    REFERENCES `order` (`order_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION);

CREATE INDEX `invoice_order_id` ON `invoice` (`order_id`);

-- -----------------------------------------------------
-- Table `order_detail_status`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `order_detail_status`;
CREATE TABLE IF NOT EXISTS `order_detail_status` (
  `order_detail_status_id` INTEGER NOT NULL PRIMARY KEY,
  `order_detail_status_name` VARCHAR(50) NOT NULL);


-- -----------------------------------------------------
-- Table `order_detail`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `order_detail`;
CREATE TABLE IF NOT EXISTS `order_detail` (
  `order_detail_id` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
  `order_id` INTEGER NOT NULL,
  `product_id` INTEGER NULL DEFAULT NULL,
  `quantity` DECIMAL(18,4) NOT NULL DEFAULT '0.0000',
  `unit_price` DECIMAL(19,4) NULL DEFAULT '0.0000',
  `discount` DOUBLE NOT NULL DEFAULT '0',
  `order_detail_status_id` INTEGER NULL DEFAULT NULL,
  `date_allocated` DATETIME NULL DEFAULT NULL,
  `purchase_order_id` INTEGER NULL DEFAULT NULL,
  `inventory_transaction_id` INTEGER NULL DEFAULT NULL,
  CONSTRAINT `fk_order_details_order1`
    FOREIGN KEY (`order_id`)
    REFERENCES `order` (`order_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_order_details_product1`
    FOREIGN KEY (`product_id`)
    REFERENCES `product` (`product_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_order_details_order_detail_status1`
    FOREIGN KEY (`order_detail_status_id`)
    REFERENCES `order_detail_status` (`order_detail_status_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION);

CREATE INDEX `order_detail_inventory_id` ON `order_detail` (`inventory_transaction_id`);
CREATE INDEX `order_detail_product_id` ON `order_detail` (`product_id`);
CREATE INDEX `order_detail_purchase_order_id` ON `order_detail` (`purchase_order_id`);
CREATE INDEX `order_detail_order_id` ON `order_detail` (`order_id`);
CREATE INDEX `order_detail_status_id` ON `order_detail` (`order_detail_status_id`);


-- -----------------------------------------------------
-- Table `purchase_order_detail`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `purchase_order_detail`;
CREATE TABLE IF NOT EXISTS `purchase_order_detail` (
  `purchase_order_detail_id` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
  `purchase_order_id` INTEGER NOT NULL,
  `product_id` INTEGER NULL DEFAULT NULL,
  `quantity` DECIMAL(18,4) NOT NULL,
  `unit_cost` DECIMAL(19,4) NOT NULL,
  `date_received` DATETIME NULL DEFAULT NULL,
  `posted_to_inventory` INTEGER NOT NULL DEFAULT '0',
  `inventory_transaction_id` INTEGER NULL DEFAULT NULL,
  CONSTRAINT `fk_purchase_order_details_inventory_transactions1`
    FOREIGN KEY (`inventory_transaction_id`)
    REFERENCES `inventory_transaction` (`inventory_transaction_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_purchase_order_details_products1`
    FOREIGN KEY (`product_id`)
    REFERENCES `product` (`product_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_purchase_order_details_purchase_order1`
    FOREIGN KEY (`purchase_order_id`)
    REFERENCES `purchase_order` (`purchase_order_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION);
CREATE INDEX `purchase_order_detail_inventory_id` ON `purchase_order_detail` (`inventory_transaction_id`);
CREATE INDEX `purchase_order_detail_purchase_order_id` ON `purchase_order_detail` (`purchase_order_id`);
CREATE INDEX `purchase_order_detail_product_id` ON `purchase_order_detail` (`product_id`);


-- -----------------------------------------------------
-- Table `sales_report`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `sales_report`;
CREATE TABLE IF NOT EXISTS `sales_report` (
  `group_by` VARCHAR(50) NOT NULL PRIMARY KEY,
  `display` VARCHAR(50) NULL DEFAULT NULL,
  `title` VARCHAR(50) NULL DEFAULT NULL,
  `filter_row_source` LONGTEXT NULL DEFAULT NULL,
  `default` INTEGER NOT NULL DEFAULT '0');


-- -----------------------------------------------------
-- Table `setting`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `setting`;
CREATE TABLE IF NOT EXISTS `setting` (
  `setting_id` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
  `setting_data` VARCHAR(255) NULL DEFAULT NULL);

drop table if exists stringRecord;
CREATE TABLE stringRecord (
  stringRecordId INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
  stringRequired varchar(100) not null,
  stringDefaultNull varchar(100) DEFAULT NULL,
  stringDefaultNullable varchar(100) default 'default',
  stringDefaultNotNull varchar(100) not null default 'default');

drop table if exists dateRecord;
create table dateRecord (
  dateRecordId INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
  dateRequired date not null,
  dateDefaultNull date DEFAULT NULL,
  dateDefaultNullable date default '2000-01-02',
  dateDefaultNotNull date not null default '2000-01-02',
  timestampDefaultCurrentNullable timestamp DEFAULT CURRENT_TIMESTAMP,
  timestampDefaultCurrentNotNull timestamp not null default CURRENT_TIMESTAMP);


DROP TABLE IF EXISTS image;
CREATE TABLE `image` (
	`image_id` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
	`imageable_id` INTEGER,
	`imageable_type` VARCHAR(128),
	`path` VARCHAR(128) NOT NULL);

drop table if exists migration;
create table migration (migrationId int NOT NULL primary key, ran TIMESTAMP DEFAULT CURRENT_TIMESTAMP);


-- Add location & identity fields to CUSTOMERS table
-- Run as LCE user against Oracle DB

-- 1. Postal code — 6 digits
ALTER TABLE customers ADD postal_code NUMBER(6);

-- 2. Building number — 4 digits
ALTER TABLE customers ADD building_no NUMBER(4);

-- 3. District — FK referencing districts(district_id)
ALTER TABLE customers ADD district_id VARCHAR2(12);
ALTER TABLE customers ADD CONSTRAINT fk_customers_district
    FOREIGN KEY (district_id) REFERENCES districts(district_id);

-- 4. VAT number — 15 digits, must start and end with 3
ALTER TABLE customers ADD vat_number NUMBER(15);
ALTER TABLE customers ADD CONSTRAINT chk_customers_vat
    CHECK (
        vat_number IS NULL OR (
            LENGTH(TO_CHAR(vat_number)) = 15
            AND SUBSTR(TO_CHAR(vat_number), 1,  1) = '3'
            AND SUBSTR(TO_CHAR(vat_number), 15, 1) = '3'
        )
    );

-- 5. National/Iqama ID — 10 digits
ALTER TABLE customers ADD id_number NUMBER(10);

-- 6. Street name — up to 40 characters
ALTER TABLE customers ADD street_name VARCHAR2(40);

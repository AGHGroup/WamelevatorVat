-- Saudi Arabia Location Tables
-- Data Source: https://github.com/homaily/Saudi-Arabia-Regions-Cities-and-Districts
-- Target: Oracle 10g+

-- Drop tables in reverse FK order (safe to run multiple times)
BEGIN EXECUTE IMMEDIATE 'DROP TABLE districts'; EXCEPTION WHEN OTHERS THEN NULL; END;
/
BEGIN EXECUTE IMMEDIATE 'DROP TABLE cities'; EXCEPTION WHEN OTHERS THEN NULL; END;
/
BEGIN EXECUTE IMMEDIATE 'DROP TABLE regions'; EXCEPTION WHEN OTHERS THEN NULL; END;
/


CREATE TABLE regions (
  region_id       NUMBER(11)    NOT NULL,
  capital_city_id NUMBER(11)    NOT NULL,
  code            VARCHAR2(2)   DEFAULT '' NOT NULL,
  name_ar         NVARCHAR2(64) DEFAULT '' NOT NULL,
  name_en         VARCHAR2(64)  DEFAULT '' NOT NULL,
  center          NUMBER,
  CONSTRAINT pk_regions PRIMARY KEY (region_id)
);
/

CREATE TABLE cities (
  city_id   NUMBER(11)    NOT NULL,
  region_id NUMBER(11)    NOT NULL,
  name_ar   NVARCHAR2(64) DEFAULT '' NOT NULL,
  name_en   VARCHAR2(64)  DEFAULT '' NOT NULL,
  CONSTRAINT pk_cities PRIMARY KEY (city_id),
  CONSTRAINT fk_cities_region FOREIGN KEY (region_id) REFERENCES regions(region_id)
);
/

CREATE TABLE districts (
  district_id VARCHAR2(12)  NOT NULL,
  city_id     NUMBER(11)    NOT NULL,
  name_ar     NVARCHAR2(64) DEFAULT '' NOT NULL,
  name_en     VARCHAR2(64)  DEFAULT '' NOT NULL,
  CONSTRAINT pk_districts PRIMARY KEY (district_id),
  CONSTRAINT fk_districts_city FOREIGN KEY (city_id) REFERENCES cities(city_id)
);
/

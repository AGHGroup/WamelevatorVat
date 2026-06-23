-- Alter CO_NAME: add ZATCA fields and insert/update company row
-- Run on 10.0.0.8  (zatca)     → data = المؤسسة التجارية للمصاعد
-- Run on 10.0.0.44 (wamelevator) → data = شركة عبد الغني حسين حامد للمصاعد
-- Safe to run multiple times

BEGIN EXECUTE IMMEDIATE 'ALTER TABLE CO_NAME ADD (CR_NO       VARCHAR2(20))';   EXCEPTION WHEN OTHERS THEN NULL; END;
/
BEGIN EXECUTE IMMEDIATE 'ALTER TABLE CO_NAME ADD (VAT_NO      VARCHAR2(20))';   EXCEPTION WHEN OTHERS THEN NULL; END;
/
BEGIN EXECUTE IMMEDIATE 'ALTER TABLE CO_NAME ADD (REGION_ID   VARCHAR2(10))';   EXCEPTION WHEN OTHERS THEN NULL; END;
/
BEGIN EXECUTE IMMEDIATE 'ALTER TABLE CO_NAME ADD (CITY_ID     VARCHAR2(10))';   EXCEPTION WHEN OTHERS THEN NULL; END;
/
BEGIN EXECUTE IMMEDIATE 'ALTER TABLE CO_NAME ADD (DISTRICT_ID VARCHAR2(20))';   EXCEPTION WHEN OTHERS THEN NULL; END;
/
BEGIN EXECUTE IMMEDIATE 'ALTER TABLE CO_NAME ADD (STREET      NVARCHAR2(100))'; EXCEPTION WHEN OTHERS THEN NULL; END;
/
BEGIN EXECUTE IMMEDIATE 'ALTER TABLE CO_NAME ADD (BUILDING_NO VARCHAR2(10))';   EXCEPTION WHEN OTHERS THEN NULL; END;
/
BEGIN EXECUTE IMMEDIATE 'ALTER TABLE CO_NAME ADD (POSTAL_CODE VARCHAR2(10))';   EXCEPTION WHEN OTHERS THEN NULL; END;
/

-- ══════════════════════════════════════════════════
-- للتشغيل على 10.0.0.8 (zatca = المؤسسة التجارية)
-- ══════════════════════════════════════════════════
-- MERGE INTO CO_NAME tgt
-- USING (SELECT 1 AS dummy FROM DUAL) src ON (1=1)
-- WHEN MATCHED THEN UPDATE SET
--     NORMAL_NAME = N'المؤسسة التجارية للمصاعد',
--     CR_NO       = '4650017660',
--     VAT_NO      = '300453212100003',
--     REGION_ID   = '3', CITY_ID = '2', DISTRICT_ID = '11302270007',
--     STREET = N'عمير بن الحارث', BUILDING_NO = '2659', POSTAL_CODE = '42331'
-- WHEN NOT MATCHED THEN INSERT
--     (NORMAL_NAME, GENERAL_NAME, CR_NO, VAT_NO, REGION_ID, CITY_ID, DISTRICT_ID, STREET, BUILDING_NO, POSTAL_CODE)
-- VALUES (N'المؤسسة التجارية للمصاعد', N'Commercial Trading Co.', '4650017660',
--     '300453212100003', '3', '2', '11302270007', N'عمير بن الحارث', '2659', '42331');

-- ══════════════════════════════════════════════════════════════
-- للتشغيل على 10.0.0.44 (wamelevator = شركة عبد الغني حسين)
-- ══════════════════════════════════════════════════════════════
MERGE INTO CO_NAME tgt
USING (SELECT 1 AS dummy FROM DUAL) src ON (1=1)
WHEN MATCHED THEN UPDATE SET
    NORMAL_NAME = N'شركة عبد الغني حسين حامد للمصاعد',
    CR_NO       = '4650262799',
    VAT_NO      = '311744595500003',
    REGION_ID   = '3', CITY_ID = '2', DISTRICT_ID = '11302270051',
    STREET = N'شارع ابو إسحاق الهجري', BUILDING_NO = '7359', POSTAL_CODE = '42331'
WHEN NOT MATCHED THEN INSERT
    (NORMAL_NAME, GENERAL_NAME, CR_NO, VAT_NO, REGION_ID, CITY_ID, DISTRICT_ID, STREET, BUILDING_NO, POSTAL_CODE)
VALUES (N'شركة عبد الغني حسين حامد للمصاعد', N'Abdul Ghani Hussein Hamed Elevators', '4650262799',
    '311744595500003', '3', '2', '11302270051', N'شارع ابو إسحاق الهجري', '7359', '42331');

COMMIT;
/

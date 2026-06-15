<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$pdo = DB::connection('oracle')->getPdo();

// Get max existing DISTRICT_ID to avoid collision
$maxId = (int) $pdo->query("SELECT NVL(MAX(TO_NUMBER(DISTRICT_ID)),20000000000) FROM DISTRICTS")->fetchColumn();
$nextId = $maxId + 1;

$districts = [
    ['NAME_AR' => 'حي أبو بكر الصديق',      'NAME_EN' => 'Abu Bakr Al-Siddiq Dist.'],
    ['NAME_AR' => 'حي أبيار علي',             'NAME_EN' => 'Abyar Ali Dist.'],
    ['NAME_AR' => 'حي أحد',                   'NAME_EN' => 'Uhud Dist.'],
    ['NAME_AR' => 'حي أم الدرم',              'NAME_EN' => 'Umm Al-Darm Dist.'],
    ['NAME_AR' => 'حي الأزهري',               'NAME_EN' => 'Al-Azhari Dist.'],
    ['NAME_AR' => 'حي الإسكان',               'NAME_EN' => 'Al-Iskan Dist.'],
    ['NAME_AR' => 'حي البركة',                'NAME_EN' => 'Al-Baraka Dist.'],
    ['NAME_AR' => 'حي البصيرة',               'NAME_EN' => 'Al-Basira Dist.'],
    ['NAME_AR' => 'حي الجماوات',              'NAME_EN' => 'Al-Jamawat Dist.'],
    ['NAME_AR' => 'حي الجمعة',                'NAME_EN' => 'Al-Jumah Dist.'],
    ['NAME_AR' => 'حي الجنائن',               'NAME_EN' => 'Al-Janain Dist.'],
    ['NAME_AR' => 'حي الحرة الشرقية',         'NAME_EN' => 'Al-Harrah Al-Sharqiyah Dist.'],
    ['NAME_AR' => 'حي الحرة الغربية',         'NAME_EN' => 'Al-Harrah Al-Gharbiyah Dist.'],
    ['NAME_AR' => 'حي الحفيرة',               'NAME_EN' => 'Al-Huffairah Dist.'],
    ['NAME_AR' => 'حي الخاتم',                'NAME_EN' => 'Al-Khatim Dist.'],
    ['NAME_AR' => 'حي الدفاع',                'NAME_EN' => 'Al-Difa Dist.'],
    ['NAME_AR' => 'حي الدويمة',               'NAME_EN' => 'Al-Duwaymah Dist.'],
    ['NAME_AR' => 'حي الذيابية',              'NAME_EN' => 'Al-Dhiyabiyah Dist.'],
    ['NAME_AR' => 'حي الرانوناء',             'NAME_EN' => 'Al-Ranuna Dist.'],
    ['NAME_AR' => 'حي الربوة',                'NAME_EN' => 'Al-Rabwah Dist.'],
    ['NAME_AR' => 'حي الرمانة',               'NAME_EN' => 'Al-Rummanah Dist.'],
    ['NAME_AR' => 'حي الروابي',               'NAME_EN' => 'Al-Rawabi Dist.'],
    ['NAME_AR' => 'حي الزاهرة',               'NAME_EN' => 'Al-Zahirah Dist.'],
    ['NAME_AR' => 'حي السلام',                'NAME_EN' => 'Al-Salam Dist.'],
    ['NAME_AR' => 'حي السلطانة',              'NAME_EN' => 'Al-Sultanah Dist.'],
    ['NAME_AR' => 'حي السيح',                 'NAME_EN' => 'Al-Sayh Dist.'],
    ['NAME_AR' => 'حي الشهداء',               'NAME_EN' => 'Al-Shuhada Dist.'],
    ['NAME_AR' => 'حي الشيخان',               'NAME_EN' => 'Al-Shaykhan Dist.'],
    ['NAME_AR' => 'حي الصالحية',              'NAME_EN' => 'Al-Salihiyah Dist.'],
    ['NAME_AR' => 'حي الصناعية',              'NAME_EN' => 'Al-Sinaiyah Dist.'],
    ['NAME_AR' => 'حي العازمية',              'NAME_EN' => 'Al-Azimiyah Dist.'],
    ['NAME_AR' => 'حي العريض',                'NAME_EN' => 'Al-Uraydh Dist.'],
    ['NAME_AR' => 'حي العقيق',                'NAME_EN' => 'Al-Aqiq Dist.'],
    ['NAME_AR' => 'حي العوالي',               'NAME_EN' => 'Al-Awali Dist.'],
    ['NAME_AR' => 'حي الغراء',                'NAME_EN' => 'Al-Gharra Dist.'],
    ['NAME_AR' => 'حي الفيحاء',               'NAME_EN' => 'Al-Fayha Dist.'],
    ['NAME_AR' => 'حي القبلتين',              'NAME_EN' => 'Al-Qiblatayn Dist.'],
    ['NAME_AR' => 'حي القسومة',               'NAME_EN' => 'Al-Qasumah Dist.'],
    ['NAME_AR' => 'حي القيم',                 'NAME_EN' => 'Al-Qiyam Dist.'],
    ['NAME_AR' => 'حي المبعوث',               'NAME_EN' => 'Al-Mab\'uth Dist.'],
    ['NAME_AR' => 'حي المطار',                'NAME_EN' => 'Al-Matar Dist.'],
    ['NAME_AR' => 'حي المناخة',               'NAME_EN' => 'Al-Manakhah Dist.'],
    ['NAME_AR' => 'حي المنتزه',               'NAME_EN' => 'Al-Muntazah Dist.'],
    ['NAME_AR' => 'حي المهد',                 'NAME_EN' => 'Al-Mahd Dist.'],
    ['NAME_AR' => 'حي النزهة',                'NAME_EN' => 'Al-Nuzhah Dist.'],
    ['NAME_AR' => 'حي النقاء',                'NAME_EN' => 'Al-Naqa Dist.'],
    ['NAME_AR' => 'حي الهجرة',                'NAME_EN' => 'Al-Hijrah Dist.'],
    ['NAME_AR' => 'حي الوبرة',                'NAME_EN' => 'Al-Wabrah Dist.'],
    ['NAME_AR' => 'حي بئر عثمان',             'NAME_EN' => 'Bir Uthman Dist.'],
    ['NAME_AR' => 'حي بنبان',                 'NAME_EN' => 'Banban Dist.'],
    ['NAME_AR' => 'حي بني بياضة',             'NAME_EN' => 'Bani Bayadah Dist.'],
    ['NAME_AR' => 'حي بني حارثة',             'NAME_EN' => 'Bani Harithah Dist.'],
    ['NAME_AR' => 'حي بني خدرة',              'NAME_EN' => 'Bani Khudrah Dist.'],
    ['NAME_AR' => 'حي بني ظفر',               'NAME_EN' => 'Bani Dhafar Dist.'],
    ['NAME_AR' => 'حي بني معاوية',            'NAME_EN' => 'Bani Muawiyah Dist.'],
    ['NAME_AR' => 'حي ذو الحليفة',            'NAME_EN' => 'Dhu Al-Hulayfah Dist.'],
    ['NAME_AR' => 'حي سيد الشهداء',           'NAME_EN' => 'Sayyid Al-Shuhada Dist.'],
    ['NAME_AR' => 'حي شوران',                 'NAME_EN' => 'Shawran Dist.'],
    ['NAME_AR' => 'حي طيبة',                  'NAME_EN' => 'Taybah Dist.'],
    ['NAME_AR' => 'حي قباء',                  'NAME_EN' => 'Quba Dist.'],
    ['NAME_AR' => 'حي قربان',                 'NAME_EN' => 'Qurban Dist.'],
    ['NAME_AR' => 'حي مشربة أم إبراهيم',     'NAME_EN' => 'Mashrabat Umm Ibrahim Dist.'],
    ['NAME_AR' => 'حي وادي العقيق',           'NAME_EN' => 'Wadi Al-Aqiq Dist.'],
];

$inserted = 0;
$skipped  = 0;

foreach ($districts as $d) {
    // Skip if already exists for this city
    $exists = $pdo->prepare("SELECT COUNT(*) FROM DISTRICTS WHERE CITY_ID=2 AND NAME_AR=:n");
    $exists->execute([':n' => $d['NAME_AR']]);
    if ($exists->fetchColumn() > 0) { $skipped++; continue; }

    $stmt = $pdo->prepare(
        "INSERT INTO DISTRICTS (DISTRICT_ID, CITY_ID, NAME_AR, NAME_EN) VALUES (:id, 2, :ar, :en)"
    );
    $stmt->execute([':id' => $nextId, ':ar' => $d['NAME_AR'], ':en' => $d['NAME_EN']]);
    $nextId++;
    $inserted++;
}

echo "Done. Inserted: $inserted, Skipped (already exist): $skipped\n";

// Verify
$cnt = $pdo->query("SELECT COUNT(*) FROM DISTRICTS WHERE CITY_ID=2")->fetchColumn();
echo "Total districts for المدينة المنورة (city_id=2): $cnt\n";

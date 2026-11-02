<?php
header('Content-Type: application/json; charset=utf-8');

require_once 'db.php';

try {
    // 1. Self-healing table creation
    $createTableSql = "CREATE TABLE IF NOT EXISTS calendar_events (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        event_date DATE NOT NULL,
        branch VARCHAR(100) DEFAULT 'ALL',
        tithi VARCHAR(100) DEFAULT NULL,
        description TEXT DEFAULT NULL,
        is_highlight TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    $pdo->exec($createTableSql);

    // 2. Seed initial community festivals if empty
    $count = $pdo->query("SELECT COUNT(*) FROM calendar_events")->fetchColumn();
    if ($count == 0) {
        $initialEvents = [
            // 2026 / 2027 Festivals & Community Jayantis
            ['title' => 'मकर संक्रांति एवं पोंगल', 'event_date' => '2026-01-14', 'branch' => 'ALL', 'tithi' => 'माघ कृष्ण प्रतिपदा', 'desc' => 'दान-पुण्य, सूर्य उपासना एवं खिचड़ी वितरण पर्व।', 'hl' => 0],
            ['title' => 'वसंत पंचमी एवं माँ सरस्वती प्राकट्योत्सव', 'event_date' => '2026-01-23', 'branch' => 'सारस्वत', 'tithi' => 'माघ शुक्ल पंचमी', 'desc' => 'माँ सरस्वती पूजन एवं महर्षि सारस्वत जी का पावन प्राकट्य दिवस।', 'hl' => 1],
            ['title' => 'महाशिवरात्रि महोत्सव', 'event_date' => '2026-02-15', 'branch' => 'ALL', 'tithi' => 'फाल्गुन कृष्ण चतुर्दशी', 'desc' => 'भगवान भोलेनाथ की विशेष रुद्राभिषेक व रात्रि जागरण पूजा।', 'hl' => 1],
            ['title' => 'होलिका दहन', 'event_date' => '2026-03-03', 'branch' => 'ALL', 'tithi' => 'फाल्गुन पूर्णिमा', 'desc' => 'पारंपरिक होलिका दहन व भक्त प्रह्लाद विजयोत्सव।', 'hl' => 0],
            ['title' => 'धुलेंडी / होली मिलन', 'event_date' => '2026-03-04', 'branch' => 'ALL', 'tithi' => 'चैत्र कृष्ण प्रतिपदा', 'desc' => 'रंगोत्सव एवं विप्र समाज आपसी स्नेह मिलन समारोह।', 'hl' => 0],
            ['title' => 'हिंदू नववर्ष (गुड़ी पड़वा / नव संवत्सर)', 'event_date' => '2026-03-19', 'branch' => 'ALL', 'tithi' => 'चैत्र शुक्ल प्रतिपदा', 'desc' => 'विक्रम संवत नववर्ष प्रारंभ, गुड़ी पड़वा एवं वासंतीय नवरात्र आरंभ।', 'hl' => 1],
            ['title' => 'श्री राम नवमी', 'event_date' => '2026-03-27', 'branch' => 'ALL', 'tithi' => 'चैत्र शुक्ल नवमी', 'desc' => 'मर्यादा पुरुषोत्तम भगवान श्री राम जन्मोत्सव।', 'hl' => 1],
            ['title' => 'संत सुंदरदास जी जयंती', 'event_date' => '2026-03-28', 'branch' => 'खंडेलवाल', 'tithi' => 'चैत्र शुक्ल दशमी', 'desc' => 'खंडेलवाल विप्र समाज के महान संत कवि सुंदरदास जी जयंती।', 'hl' => 0],
            ['title' => 'हनुमान जन्मोत्सव', 'event_date' => '2026-04-02', 'branch' => 'ALL', 'tithi' => 'चैत्र पूर्णिमा', 'desc' => 'संकटमोचन श्री हनुमान जी का जन्मोत्सव एवं सुंदरकांड पाठ।', 'hl' => 0],
            ['title' => 'भगवान परशुराम जन्मोत्सव (अक्षय तृतीया)', 'event_date' => '2026-04-19', 'branch' => 'ALL', 'tithi' => 'वैशाख शुक्ल तृतीया', 'desc' => 'समस्त विप्र समाज के आराध्यदेव भगवान परशुराम जी की भव्य शोभायात्रा व महाआरती।', 'hl' => 1],
            ['title' => 'महर्षि श्रृंग ऋषि जयंती', 'event_date' => '2026-05-25', 'branch' => 'सिखवाल', 'tithi' => 'ज्येष्ठ शुक्ल नवमी', 'desc' => 'सिखवाल (श्रृंग) विप्र समाज के आदि पुरुष महर्षि श्रृंग ऋषि जयंती।', 'hl' => 1],
            ['title' => 'माँ गायत्री जयंती व गंगा दशहरा', 'event_date' => '2026-05-26', 'branch' => 'ALL', 'tithi' => 'ज्येष्ठ शुक्ल दशमी', 'desc' => 'वेदमाता माँ गायत्री प्राकट्य दिवस एवं सामूहिक गायत्री हवन।', 'hl' => 1],
            ['title' => 'गुरु पूर्णिमा (महर्षि वेदव्यास जयंती)', 'event_date' => '2026-07-29', 'branch' => 'ALL', 'tithi' => 'आषाढ़ पूर्णिमा', 'desc' => 'गुरु पूजन, महर्षि वेदव्यास जयंती एवं गुरु कृपा महोत्सव।', 'hl' => 1],
            ['title' => 'श्रावणी उपाकर्म (रक्षाबंधन - यज्ञोपवीत संस्कार)', 'event_date' => '2026-08-28', 'branch' => 'ALL', 'tithi' => 'श्रावण पूर्णिमा', 'desc' => 'विप्र बंधुओं का सामूहिक श्रावणी कर्म, ऋषि तर्पण एवं नूतन जनेऊ धारण।', 'hl' => 1],
            ['title' => 'श्रीकृष्ण जन्माष्टमी', 'event_date' => '2026-09-04', 'branch' => 'ALL', 'tithi' => 'भाद्रपद कृष्ण अष्टमी', 'desc' => 'योगेश्वर भगवान श्री कृष्ण का पावन जन्मोत्सव व दही हांडी।', 'hl' => 1],
            ['title' => 'महर्षि दधीचि जयंती', 'event_date' => '2026-09-19', 'branch' => 'दाधीच', 'tithi' => 'भाद्रपद शुक्ल अष्टमी', 'desc' => 'परम त्यागी महर्षि दधीचि जी की जयंती, दाधीच समाज पूजन व हवन।', 'hl' => 1],
            ['title' => 'अनंत चतुर्दशी (गणेश विसर्जन)', 'event_date' => '2026-09-25', 'branch' => 'ALL', 'tithi' => 'भाद्रपद शुक्ल चतुर्दशी', 'desc' => 'गणपति बप्पा विसर्जन व अनंत पद्मनाभ व्रत।', 'hl' => 0],
            ['title' => 'शारदीय नवरात्र स्थापना', 'event_date' => '2026-10-11', 'branch' => 'ALL', 'tithi' => 'आश्विन शुक्ल प्रतिपदा', 'desc' => 'माँ दुर्गा की नौ दिवसीय घटस्थापना एवं आराधना।', 'hl' => 0],
            ['title' => 'विजयादशमी (दशहरा)', 'event_date' => '2026-10-20', 'branch' => 'ALL', 'tithi' => 'आश्विन शुक्ल दशमी', 'desc' => 'अधर्म पर धर्म की विजय का पावन पर्व व शमी पूजन।', 'hl' => 1],
            ['title' => 'महर्षि पाराशर जयंती (शरद पूर्णिमा)', 'event_date' => '2026-10-25', 'branch' => 'पारीक', 'tithi' => 'आश्विन शुक्ल पूर्णिमा', 'desc' => 'पारीक विप्र समाज के कुल प्रवर्तक महर्षि पाराशर जी की पावन जयंती।', 'hl' => 1],
            ['title' => 'धनतेरस व श्री महालक्ष्मी पूजन (दीपावली)', 'event_date' => '2026-11-08', 'branch' => 'ALL', 'tithi' => 'कार्तिक कृष्ण त्रयोदशी / अमावस्या', 'desc' => 'दीपोत्सव पर्व, श्री लक्ष्मी-गणेश-कुबेर पूजन।', 'hl' => 1],
            ['title' => 'गोवर्धन पूजा व अन्नकूट महोत्सव', 'event_date' => '2026-11-10', 'branch' => 'ALL', 'tithi' => 'कार्तिक शुक्ल प्रतिपदा', 'desc' => 'विप्र मंडल द्वारा सामूहिक अन्नकूट महाप्रसाद व गोवर्धन पूजा।', 'hl' => 1],
            ['title' => 'तुलसी विवाह व देवउठनी एकादशी', 'event_date' => '2026-11-20', 'branch' => 'ALL', 'tithi' => 'कार्तिक शुक्ल एकादशी', 'desc' => 'भगवान शालिग्राम व तुलसी माता का विवाह उत्सव।', 'hl' => 0],
            ['title' => 'वार्षिक विप्र स्नेह सम्मेलन व प्रतिभा सम्मान', 'event_date' => '2026-12-25', 'branch' => 'ALL', 'tithi' => 'मार्गशीर्ष पूर्णिमा', 'desc' => 'छःन्यात विप्र समाज लातूर का वार्षिक स्नेह मिलन एवं मेधावी छात्र सम्मान।', 'hl' => 1],
        ];

        $ins = $pdo->prepare("INSERT INTO calendar_events (title, event_date, branch, tithi, description, is_highlight) 
                              VALUES (:title, :event_date, :branch, :tithi, :description, :is_highlight)");
        foreach ($initialEvents as $ev) {
            $ins->execute([
                ':title' => $ev['title'],
                ':event_date' => $ev['event_date'],
                ':branch' => $ev['branch'],
                ':tithi' => $ev['tithi'],
                ':description' => $ev['desc'],
                ':is_highlight' => $ev['hl']
            ]);
        }
    }

    $branchFilter = isset($_GET['branch']) ? trim($_GET['branch']) : null;
    $month = isset($_GET['month']) ? (int)$_GET['month'] : null;
    $year = isset($_GET['year']) ? (int)$_GET['year'] : null;
    $upcomingOnly = isset($_GET['upcoming']) && ($_GET['upcoming'] === 'true' || $_GET['upcoming'] === '1');
    $limit = isset($_GET['limit']) ? max(1, (int)$_GET['limit']) : 100;

    $whereClauses = [];
    $params = [];

    if ($upcomingOnly) {
        $whereClauses[] = "event_date >= CURDATE()";
    }

    if ($branchFilter && $branchFilter !== 'ALL') {
        $whereClauses[] = "(branch = :branch OR branch = 'ALL')";
        $params[':branch'] = $branchFilter;
    }

    if ($month && $month >= 1 && $month <= 12) {
        $whereClauses[] = "MONTH(event_date) = :month";
        $params[':month'] = $month;
    }

    if ($year && $year >= 2020) {
        $whereClauses[] = "YEAR(event_date) = :year";
        $params[':year'] = $year;
    }

    $whereSql = !empty($whereClauses) ? " WHERE " . implode(" AND ", $whereClauses) : "";
    $orderSql = " ORDER BY event_date ASC LIMIT :limit";

    $stmt = $pdo->prepare("SELECT * FROM calendar_events" . $whereSql . $orderSql);
    foreach ($params as $k => $v) {
        $stmt->bindValue($k, $v);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();

    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'data' => $events,
        'total' => count($events)
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>

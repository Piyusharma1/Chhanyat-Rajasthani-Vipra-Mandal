<!DOCTYPE html>
<html lang="hi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>परिवार विवरण | छःन्यात राजस्थानी विप्र मंडल</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Yatra+One&family=Tiro+Devanagari+Hindi&family=Noto+Sans+Devanagari:wght@400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- ✅ Main CSS -->
    <link rel="stylesheet" href="css/style.css?v=<?= filemtime($_SERVER['DOCUMENT_ROOT'].'/css/style.css')?>">
    
    <style>
        /* Added hover effect for the ideal card */
        .ideal-card:hover { 
            transform: translateY(-5px); 
            box-shadow: 0 15px 30px rgba(0,0,0,0.15); 
        }
        .ideal-card { 
            transition: transform 0.3s, box-shadow 0.3s; 
        }
    </style>
</head>
<body>
    <!-- ✅ Header -->
    <div data-include="components/header.php"></div>

    <div class="container">
        <nav class="breadcrumb no-print">
            <a href="/">Home</a> &gt; <span id="breadcrumb-branch" class="current">परिवार</span>
        </nav>

        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; margin-bottom: 20px;">
            <h2 id="branch-title">परिवार का नाम</h2>
        </div>

        <!-- ✅ SINGLE IDEAL PHOTO SECTION -->
        <div id="ideal-section-container"></div>

        <section style="margin-bottom: 50px;">
            <h3 style="border-bottom: 2px solid var(--saffron); padding-bottom: 10px; margin-bottom: 20px;">कार्यकारिणी सदस्य</h3>
            <div class="committee-grid" id="committee-grid"></div>
        </section>

        <section>
            <h3 style="border-bottom: 2px solid var(--saffron); padding-bottom: 10px; margin-bottom: 20px;">परिवार निर्देशिका</h3>
            <div class="filter-section no-print">
                <div class="filter-grid">
                    <input type="text" id="filter-head" class="filter-input" placeholder="प्रमुख का नाम...">
                </div>
            </div>
            <div class="family-grid" id="family-grid"></div>
            <div id="pagination" style="text-align: center; margin: 30px 0;"></div>
        </section>
    </div>

    <!-- ✅ Footer -->
    <div data-include="components/footer.php"></div>

    <!-- ✅ Scripts -->
    <script src="js/include.js?v=<?= filemtime($_SERVER['DOCUMENT_ROOT'].'/js/include.js')?>" defer></script>
    <script type="module" src="js/branch.js?v=<?= filemtime($_SERVER['DOCUMENT_ROOT'].'/js/branch.js')?>"></script>
</body>
</html>
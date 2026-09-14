<?php

namespace Database\Seeders;

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\ArticleTag;
use App\Models\User;
use Illuminate\Database\Seeder;

class ArticleSeeder extends Seeder
{
    /**
     * Seed 5 CSR & Environment articles and 5 Insights & Trends articles.
     * All articles are bilingual (English & Indonesian).
     */
    public function run(): void
    {
        $admin = User::where('email', 'admin@cms.local')->first();

        if (! $admin) {
            $this->command->warn('Admin user not found. Please run AdminUserSeeder first.');
            return;
        }

        // ── Ensure categories exist ───────────────────────────────────────────
        $catCsr = ArticleCategory::firstOrCreate(
            ['slug' => 'csr-environment'],
            [
                'name'       => ['en' => 'CSR & Environment', 'id' => 'CSR & Lingkungan'],
                'color'      => '#30aa47',
                'sort_order' => 1,
            ]
        );

        $catInsights = ArticleCategory::firstOrCreate(
            ['slug' => 'insights-trends'],
            [
                'name'       => ['en' => 'Insights & Trends', 'id' => 'Wawasan & Tren'],
                'color'      => '#268839',
                'sort_order' => 2,
            ]
        );

        // ── Tags ─────────────────────────────────────────────────────────────
        $tagCsr     = ArticleTag::firstOrCreate(['slug' => 'csr'],           ['name' => ['en' => 'CSR',            'id' => 'CSR']]);
        $tagEnv     = ArticleTag::firstOrCreate(['slug' => 'environment'],   ['name' => ['en' => 'Environment',    'id' => 'Lingkungan']]);
        $tagCoal    = ArticleTag::firstOrCreate(['slug' => 'coal'],          ['name' => ['en' => 'Coal',           'id' => 'Batu Bara']]);
        $tagSustain = ArticleTag::firstOrCreate(['slug' => 'sustainability'],['name' => ['en' => 'Sustainability', 'id' => 'Keberlanjutan']]);
        $tagTrend   = ArticleTag::firstOrCreate(['slug' => 'trends'],        ['name' => ['en' => 'Trends',         'id' => 'Tren']]);
        $tagMarket  = ArticleTag::firstOrCreate(['slug' => 'market'],        ['name' => ['en' => 'Market',         'id' => 'Pasar']]);
        $tagEnergy  = ArticleTag::firstOrCreate(['slug' => 'energy'],        ['name' => ['en' => 'Energy',         'id' => 'Energi']]);

        // ─────────────────────────────────────────────────────────────────────
        // 5 CSR & ENVIRONMENT ARTICLES
        // ─────────────────────────────────────────────────────────────────────
        $csrArticles = [

            // 1 ── Reforestation
            [
                'slug'    => ['en' => 'reforestation-program-restoring-mining-land', 'id' => 'program-reboisasi-pemulihan-lahan-tambang'],
                'title'   => ['en' => 'Reforestation Program: Restoring Mining Land to Its Natural State', 'id' => 'Program Reboisasi: Mengembalikan Lahan Tambang ke Kondisi Alami'],
                'summary' => [
                    'en' => 'CDE is committed to a large-scale reforestation program that transforms post-mining land into productive green areas, revitalizing local ecosystems and communities.',
                    'id' => 'CDE berkomitmen pada program reboisasi berskala besar yang mengubah lahan pasca tambang menjadi kawasan hijau produktif, merevitalisasi ekosistem dan masyarakat lokal.',
                ],
                'content' => [
                    'en' => '<h2>Our Commitment to Restoring Nature</h2>'
                        . '<p>Mining activities inevitably alter the landscape, but responsible mining companies go beyond extraction — they actively restore what nature has given. '
                        . "CDE's Reforestation Program has planted over 150,000 native tree seedlings across 320 hectares of post-mining land since 2021.</p>"
                        . '<p>Native species such as <em>Shorea leprosula</em>, <em>Meranti merah</em>, and <em>Ulin</em> were selected by our environmental team based on soil composition analysis '
                        . 'and local biodiversity mapping, ensuring the restored ecosystem is self-sustaining in the long run.</p>'
                        . '<h2>Community Involvement</h2>'
                        . '<p>The program actively involves surrounding communities by employing local workers for planting, monitoring, and maintenance activities. '
                        . 'This not only accelerates restoration but also provides sustainable livelihoods for over 400 local families.</p>'
                        . '<h2>Results So Far</h2>'
                        . '<ul><li>320 ha of land restored</li><li>150,000+ seedlings planted</li>'
                        . '<li>Survival rate of 82% after Year 1</li><li>400+ families benefiting from paid conservation work</li></ul>'
                        . '<p>Early data shows a 35% increase in bird species recorded within restored areas — a strong indicator of ecosystem recovery.</p>',
                    'id' => '<h2>Komitmen Kami untuk Memulihkan Alam</h2>'
                        . '<p>Aktivitas penambangan tak terhindarkan mengubah bentang alam, namun perusahaan tambang yang bertanggung jawab secara aktif memulihkan apa yang telah diberikan alam. '
                        . 'Program Reboisasi CDE telah menanam lebih dari 150.000 bibit pohon asli di 320 hektare lahan pasca tambang sejak 2021.</p>'
                        . '<p>Spesies asli seperti <em>Shorea leprosula</em>, <em>Meranti Merah</em>, dan <em>Ulin</em> dipilih berdasarkan analisis komposisi tanah dan pemetaan keanekaragaman hayati lokal.</p>'
                        . '<h2>Keterlibatan Masyarakat</h2>'
                        . '<p>Program ini secara aktif melibatkan masyarakat sekitar dengan mempekerjakan tenaga lokal untuk kegiatan penanaman, pemantauan, dan pemeliharaan, '
                        . 'memberikan mata pencaharian berkelanjutan bagi lebih dari 400 keluarga lokal.</p>'
                        . '<h2>Hasil yang Telah Dicapai</h2>'
                        . '<ul><li>320 ha lahan dipulihkan</li><li>150.000+ bibit ditanam</li>'
                        . '<li>Tingkat kelangsungan hidup 82% setelah Tahun 1</li><li>400+ keluarga mendapat manfaat dari pekerjaan konservasi berbayar</li></ul>'
                        . '<p>Data awal menunjukkan peningkatan 35% jumlah spesies burung di area yang dipulihkan — indikator kuat pemulihan ekosistem.</p>',
                ],
                'tags'        => [$tagCsr->id, $tagEnv->id, $tagSustain->id],
                'is_featured' => true,
                'views_count' => 218,
            ],

            // 2 ── Zero Discharge Water Management
            [
                'slug'    => ['en' => 'zero-discharge-water-management-coal-mining', 'id' => 'pengelolaan-air-zero-discharge-tambang-batu-bara'],
                'title'   => ['en' => 'Zero Discharge: Our Water Management Approach in Coal Mining', 'id' => 'Zero Discharge: Pendekatan Pengelolaan Air Kami di Tambang Batu Bara'],
                'summary' => [
                    'en' => "Water stewardship is at the heart of CDE's environmental policy. Discover how our zero-discharge system prevents mining wastewater from contaminating local rivers and groundwater.",
                    'id' => 'Pengelolaan air adalah inti dari kebijakan lingkungan CDE. Temukan bagaimana sistem zero-discharge kami mencegah air limbah tambang mencemari sungai dan air tanah setempat.',
                ],
                'content' => [
                    'en' => '<h2>Why Water Management Matters</h2>'
                        . '<p>Coal mining generates significant volumes of acid mine drainage (AMD) and sediment-laden water. Without proper treatment, these pollutants can devastate aquatic ecosystems and contaminate drinking water sources.</p>'
                        . '<h2>Our Zero-Discharge System</h2>'
                        . '<p>CDE has invested in a multi-stage water treatment plant that processes 100% of mine-site runoff before any water leaves the operational boundary. '
                        . 'The system includes sedimentation ponds, lime dosing units for pH correction, and polishing filters that meet and exceed Indonesian Government Regulation No. 22 Year 2021 standards.</p>'
                        . '<h2>Monitoring and Transparency</h2>'
                        . '<p>We install real-time water quality sensors at five outflow points monitored continuously by our HSE team. '
                        . 'Monthly water quality data is published on our company website and shared with the local Environmental Agency (DLHK).</p>'
                        . '<p>Since implementing the zero-discharge system in 2022, independent lab tests have confirmed <strong>zero incidents</strong> of river contamination attributable to our operations.</p>',
                    'id' => '<h2>Mengapa Pengelolaan Air Sangat Penting</h2>'
                        . '<p>Penambangan batu bara menghasilkan volume signifikan drainase asam tambang (AMD) dan air yang mengandung sedimen. Tanpa penanganan yang tepat, polutan ini dapat merusak ekosistem perairan dan mencemari sumber air minum.</p>'
                        . '<h2>Sistem Zero-Discharge Kami</h2>'
                        . '<p>CDE telah berinvestasi dalam instalasi pengolahan air multi-tahap yang memproses 100% aliran air dari lokasi tambang sebelum air meninggalkan batas operasional. '
                        . 'Sistem ini mencakup kolam pengendapan, unit dosing kapur untuk koreksi pH, dan filter penyempurnaan yang memenuhi dan melampaui standar PP No. 22 Tahun 2021.</p>'
                        . '<h2>Pemantauan dan Transparansi</h2>'
                        . '<p>Kami memasang sensor kualitas air real-time di lima titik aliran keluar yang dipantau terus-menerus oleh tim HSE kami. '
                        . 'Data kualitas air bulanan dipublikasikan di website perusahaan dan dibagikan kepada DLHK setempat.</p>'
                        . '<p>Sejak menerapkan sistem zero-discharge pada 2022, uji laboratorium independen telah mengkonfirmasi <strong>nol insiden</strong> pencemaran sungai yang disebabkan oleh operasi kami.</p>',
                ],
                'tags'        => [$tagCsr->id, $tagEnv->id, $tagCoal->id],
                'is_featured' => false,
                'views_count' => 174,
            ],

            // 3 ── Dust Suppression
            [
                'slug'    => ['en' => 'dust-suppression-innovation-protecting-air-quality', 'id' => 'inovasi-pengendalian-debu-melindungi-kualitas-udara'],
                'title'   => ['en' => 'Dust Suppression Innovation: Protecting Air Quality Around Our Operations', 'id' => 'Inovasi Pengendalian Debu: Melindungi Kualitas Udara di Sekitar Operasi Kami'],
                'summary' => [
                    'en' => 'Coal dust is a persistent challenge in mining. CDE has pioneered smart IoT-based dust suppression technologies that protect both worker health and community air quality.',
                    'id' => 'Debu batu bara adalah tantangan yang terus-menerus. CDE telah memelopori teknologi pengendalian debu cerdas berbasis IoT yang melindungi kesehatan pekerja dan kualitas udara masyarakat.',
                ],
                'content' => [
                    'en' => '<h2>The Challenge of Coal Dust</h2>'
                        . '<p>Fugitive dust from haul roads, stockpiles, and loading operations poses health risks including respiratory disease and can reduce air quality in nearby communities.</p>'
                        . '<h2>Smart Dust Suppression System</h2>'
                        . '<p>CDE has deployed an IoT-based automated dust suppression network covering 28 km of internal haul roads. '
                        . 'Wind speed sensors, PM10 monitors, and automated water-spray cannons work in concert: when PM10 readings exceed 150 ug/m3 or wind gusts exceed 20 km/h, '
                        . 'the system activates sprinklers at the relevant road segment automatically — no manual intervention needed.</p>'
                        . '<h2>Stockpile Management</h2>'
                        . '<p>Our stockpiles use enclosed conveyors and wind barriers to minimize fugitive emissions. '
                        . 'We also apply dust-binding agents on stockpile surfaces during extended dry periods, reducing dust lift-off by up to 70%.</p>'
                        . '<h2>Health Outcomes</h2>'
                        . '<p>Annual health screenings show a 45% reduction in respiratory complaints among mine workers since 2022, '
                        . 'and community air-quality monitoring at three surrounding villages consistently records PM10 levels below the 75 ug/m3 annual standard.</p>',
                    'id' => '<h2>Tantangan Debu Batu Bara</h2>'
                        . '<p>Debu yang beterbangan dari jalan angkut, stockpile, dan operasi pemuatan menimbulkan risiko kesehatan termasuk penyakit pernapasan dan dapat menurunkan kualitas udara di komunitas sekitar.</p>'
                        . '<h2>Sistem Pengendalian Debu Cerdas</h2>'
                        . '<p>CDE telah menggunakan jaringan pengendalian debu otomatis berbasis IoT yang mencakup 28 km jalan angkut internal. '
                        . 'Sensor kecepatan angin, monitor PM10, dan meriam semprotan air otomatis bekerja sinergis: '
                        . 'ketika pembacaan PM10 melebihi 150 ug/m3 atau hembusan angin melebihi 20 km/jam, sistem mengaktifkan sprinkler secara otomatis tanpa intervensi manual.</p>'
                        . '<h2>Manajemen Stockpile</h2>'
                        . '<p>Stockpile kami menggunakan konveyor tertutup dan penghalang angin untuk meminimalkan emisi debu. '
                        . 'Kami juga mengaplikasikan agen pengikat debu pada permukaan stockpile selama periode kering yang panjang, mengurangi debu yang terbang hingga 70%.</p>'
                        . '<h2>Hasil Kesehatan</h2>'
                        . '<p>Pemeriksaan kesehatan tahunan menunjukkan penurunan 45% keluhan pernapasan di antara pekerja tambang sejak 2022, '
                        . 'dan pemantauan kualitas udara komunitas di tiga desa sekitar secara konsisten mencatat tingkat PM10 di bawah standar tahunan 75 ug/m3.</p>',
                ],
                'tags'        => [$tagCsr->id, $tagEnv->id, $tagCoal->id],
                'is_featured' => false,
                'views_count' => 132,
            ],

            // 4 ── Biodiversity Conservation
            [
                'slug'    => ['en' => 'biodiversity-conservation-protecting-endemic-species', 'id' => 'konservasi-keanekaragaman-hayati-melindungi-spesies-endemik'],
                'title'   => ['en' => 'Biodiversity Conservation: Protecting Endemic Species in Our Operational Area', 'id' => 'Konservasi Keanekaragaman Hayati: Melindungi Spesies Endemik di Area Operasi Kami'],
                'summary' => [
                    'en' => 'CDE actively collaborates with conservation NGOs and local universities to document and protect endemic wildlife species living near its mining concessions in South Kalimantan.',
                    'id' => 'CDE secara aktif berkolaborasi dengan LSM konservasi dan universitas lokal untuk mendokumentasikan dan melindungi spesies satwa liar endemik di dekat konsesi tambang kami di Kalimantan Selatan.',
                ],
                'content' => [
                    'en' => '<h2>A Responsibility Beyond Compliance</h2>'
                        . '<p>Operating in the biodiverse rainforests of South Kalimantan demands environmental stewardship that goes far beyond regulatory minimums. '
                        . "CDE has established a Biodiversity Management Plan (BMP) in partnership with Lambung Mangkurat University and WWF Indonesia.</p>"
                        . '<h2>Key Conservation Measures</h2>'
                        . '<p><strong>Wildlife Corridors:</strong> We have preserved 1,240 ha of undisturbed forest as wildlife corridors connecting fragmented habitats, '
                        . 'allowing endemic species such as the Proboscis Monkey (<em>Nasalis larvatus</em>) to move freely between habitats.</p>'
                        . '<p><strong>Camera Trap Monitoring:</strong> Over 60 camera traps deployed across our concession record biodiversity data monthly. '
                        . "Last year's survey recorded 12 mammal species and 87 bird species, including three IUCN Red List species.</p>"
                        . '<h2>Collaborative Research</h2>'
                        . '<p>CDE funds annual biodiversity research grants for Lambung Mangkurat University students. '
                        . "Four peer-reviewed papers on South Kalimantan endemic species have been published using CDE's operational area as study sites.</p>",
                    'id' => '<h2>Tanggung Jawab di Atas Kepatuhan</h2>'
                        . '<p>Beroperasi di hutan hujan tropis Kalimantan Selatan yang kaya keanekaragaman hayati menuntut pengelolaan lingkungan yang jauh melampaui minimum regulasi. '
                        . 'CDE telah menetapkan Rencana Pengelolaan Keanekaragaman Hayati (RPKH) dalam kemitraan dengan Universitas Lambung Mangkurat dan WWF Indonesia.</p>'
                        . '<h2>Langkah Konservasi Utama</h2>'
                        . '<p><strong>Koridor Satwa Liar:</strong> Kami telah mempertahankan 1.240 ha hutan tidak terganggu sebagai koridor satwa liar yang menghubungkan habitat terfragmentasi, '
                        . 'memungkinkan spesies endemik seperti Bekantan (<em>Nasalis larvatus</em>) bergerak bebas antar habitat.</p>'
                        . '<p><strong>Pemantauan Camera Trap:</strong> Lebih dari 60 camera trap dipasang di seluruh area konsesi kami, merekam data keanekaragaman hayati setiap bulan. '
                        . 'Survei tahun lalu mencatat 12 spesies mamalia dan 87 spesies burung, termasuk tiga spesies Daftar Merah IUCN.</p>'
                        . '<h2>Penelitian Kolaboratif</h2>'
                        . '<p>CDE mendanai hibah penelitian keanekaragaman hayati tahunan untuk mahasiswa Universitas Lambung Mangkurat. '
                        . 'Empat makalah ilmiah tentang spesies endemik Kalimantan Selatan telah diterbitkan dengan area operasi CDE sebagai lokasi penelitian.</p>',
                ],
                'tags'        => [$tagCsr->id, $tagEnv->id, $tagSustain->id],
                'is_featured' => true,
                'views_count' => 196,
            ],

            // 5 ── Community Solar Program
            [
                'slug'    => ['en' => 'community-solar-program-powering-villages-clean-energy', 'id' => 'program-solar-komunitas-memberdayakan-desa-energi-bersih'],
                'title'   => ['en' => 'Community Solar Program: Powering 3 Villages with Clean Energy', 'id' => 'Program Solar Komunitas: Memberdayakan 3 Desa dengan Energi Bersih'],
                'summary' => [
                    'en' => "As part of its CSR commitment, CDE has funded and installed solar micro-grid systems in three remote villages near its operations, providing clean electricity to over 1,200 households.",
                    'id' => 'Sebagai bagian dari komitmen CSR-nya, CDE telah mendanai dan memasang sistem mikro-grid surya di tiga desa terpencil dekat operasinya, menyediakan listrik bersih bagi lebih dari 1.200 rumah tangga.',
                ],
                'content' => [
                    'en' => '<h2>Bringing Light to Remote Communities</h2>'
                        . "<p>Three villages adjacent to CDE's operational area — Desa Mangkauk, Desa Sungai Kupang, and Desa Halong — previously had unreliable or no grid electricity access. "
                        . 'Diesel generators were the only power source, adding to carbon emissions and creating significant household expenses.</p>'
                        . '<h2>The Solar Micro-Grid Solution</h2>'
                        . '<p>In 2023, CDE invested IDR 8.7 billion to design, supply, and install solar PV micro-grid systems in all three villages. '
                        . 'Each system comprises rooftop panels, a centralized battery bank, and smart load management. '
                        . 'Combined installed capacity stands at 480 kWp, reliably powering 1,240 households, the village health center, elementary school, and mosque.</p>'
                        . '<h2>Measurable Impact</h2>'
                        . '<ul>'
                        . '<li>1,240 households now have 24/7 clean electricity access</li>'
                        . '<li>Annual CO2 reduction of approximately 850 tonnes versus diesel baseline</li>'
                        . '<li>Average monthly household electricity expenses reduced by 75%</li>'
                        . '<li>Two local technicians trained and employed to maintain the systems</li>'
                        . '</ul>'
                        . '<h2>Recognition</h2>'
                        . "<p>The program received the <strong>2024 PROPER Gold</strong> rating from Indonesia's Ministry of Environment and Forestry, recognizing it as a model CSR program for the mining sector.</p>",
                    'id' => '<h2>Menerangi Komunitas Terpencil</h2>'
                        . '<p>Tiga desa yang berbatasan dengan area operasi CDE — Desa Mangkauk, Desa Sungai Kupang, dan Desa Halong — sebelumnya memiliki akses listrik jaringan yang tidak andal atau sama sekali tidak ada. '
                        . 'Generator diesel adalah satu-satunya sumber daya, menambah emisi karbon dan menciptakan pengeluaran rumah tangga yang signifikan.</p>'
                        . '<h2>Solusi Mikro-Grid Surya</h2>'
                        . '<p>Pada 2023, CDE menginvestasikan IDR 8,7 miliar untuk merancang, menyediakan, dan memasang sistem mikro-grid PV surya di ketiga desa. '
                        . 'Setiap sistem terdiri dari panel atap, bank baterai terpusat, dan manajemen beban cerdas. '
                        . 'Kapasitas terpasang gabungan mencapai 480 kWp, menyuplai listrik andal bagi 1.240 rumah tangga, puskesmas, sekolah dasar, dan masjid.</p>'
                        . '<h2>Dampak Terukur</h2>'
                        . '<ul>'
                        . '<li>1.240 rumah tangga kini memiliki akses listrik bersih 24/7</li>'
                        . '<li>Reduksi CO2 tahunan sekitar 850 ton dibandingkan baseline diesel</li>'
                        . '<li>Pengeluaran listrik rumah tangga bulanan rata-rata berkurang 75%</li>'
                        . '<li>Dua teknisi lokal dilatih dan dipekerjakan untuk memelihara sistem</li>'
                        . '</ul>'
                        . '<h2>Pengakuan</h2>'
                        . '<p>Program ini mendapatkan penilaian <strong>PROPER Gold 2024</strong> dari Kementerian Lingkungan Hidup dan Kehutanan RI, mengakuinya sebagai program CSR percontohan sektor pertambangan.</p>',
                ],
                'tags'        => [$tagCsr->id, $tagEnv->id, $tagEnergy->id, $tagSustain->id],
                'is_featured' => true,
                'views_count' => 305,
            ],

        ]; // end $csrArticles

        foreach ($csrArticles as $data) {
            $article = Article::firstOrCreate(
                ['slug->en' => $data['slug']['en']],
                [
                    'title'               => $data['title'],
                    'slug'                => $data['slug'],
                    'summary'             => $data['summary'],
                    'content'             => $data['content'],
                    'status'              => ArticleStatus::Published,
                    'article_category_id' => $catCsr->id,
                    'author_id'           => $admin->id,
                    'approved_by'         => $admin->id,
                    'approved_at'         => now()->subDays(rand(5, 60)),
                    'published_at'        => now()->subDays(rand(1, 30)),
                    'views_count'         => $data['views_count'],
                    'is_featured'         => $data['is_featured'],
                ]
            );
            $article->tags()->sync($data['tags']);
        }

        $this->command->info('5 CSR & Environment articles seeded.');

        // ─────────────────────────────────────────────────────────────────────
        // 5 INSIGHTS & TRENDS ARTICLES
        // ─────────────────────────────────────────────────────────────────────
        $insightArticles = [

            // 1 ── Coal Demand Outlook
            [
                'slug'    => ['en' => 'global-coal-demand-outlook-2025-2030', 'id' => 'prospek-permintaan-batu-bara-global-2025-2030'],
                'title'   => ['en' => 'Global Coal Demand Outlook 2025-2030: What Miners Need to Know', 'id' => 'Prospek Permintaan Batu Bara Global 2025-2030: Yang Perlu Diketahui Para Penambang'],
                'summary' => [
                    'en' => 'Despite the global energy transition, coal demand in Asia Pacific remains robust. This analysis explores key demand drivers, price forecasts, and strategic implications for coal producers through 2030.',
                    'id' => 'Meski transisi energi global berlangsung, permintaan batu bara di Asia Pasifik tetap kuat. Analisis ini mengeksplorasi pendorong permintaan utama, prakiraan harga, dan implikasi strategis bagi produsen batu bara hingga 2030.',
                ],
                'content' => [
                    'en' => '<h2>The Resilient Demand Story</h2>'
                        . '<p>Thermal coal demand in Asia — particularly from India, China, and Southeast Asia — remained at near-record levels through 2024. '
                        . "The IEA's Electricity Security Report 2024 confirms that coal-fired generation capacity in Asia will peak no earlier than 2027, "
                        . 'providing a longer runway for miners than many anticipated.</p>'
                        . '<h2>Key Demand Drivers Through 2030</h2>'
                        . "<p><strong>India's Power Deficit:</strong> India's per capita electricity consumption is expected to double by 2030 as 300 million more citizens connect to the grid, sustaining strong thermal coal import demand.</p>"
                        . '<p><strong>ASEAN Industrialization:</strong> Vietnam, Indonesia, the Philippines, and Bangladesh are in active phases of industrial expansion, building new coal-fired plants scheduled to operate into the 2040s.</p>'
                        . '<p><strong>Steel Production:</strong> Metallurgical coal demand is supported by sustained infrastructure investment across Asia, where steel consumption per capita remains well below developed-market levels.</p>'
                        . '<h2>Price Forecast</h2>'
                        . '<p>Newcastle benchmark prices are expected to stabilize in the USD 110-130/tonne range through 2026. '
                        . 'Caloric quality premiums for high-CV coal (6,000+ kcal/kg GAR) are expected to widen as higher-efficiency power plants come online.</p>'
                        . '<h2>Strategic Implications for CDE</h2>'
                        . "<p>CDE's portfolio of high-calorific-value coal products is well-positioned to capture premium pricing. "
                        . 'Our ongoing investment in wash-plant capacity expansion will improve saleable yield and blending flexibility — key competitive advantages in a quality-differentiated market.</p>',
                    'id' => '<h2>Kisah Permintaan yang Tangguh</h2>'
                        . '<p>Permintaan batu bara termal di Asia — khususnya dari India, China, dan Asia Tenggara — tetap berada di level hampir rekor sepanjang 2024. '
                        . 'Laporan Keamanan Listrik IEA 2024 mengkonfirmasi bahwa kapasitas pembangkit listrik berbahan bakar batu bara di Asia akan mencapai puncak tidak lebih awal dari 2027.</p>'
                        . '<h2>Pendorong Permintaan Utama Hingga 2030</h2>'
                        . '<p><strong>Defisit Daya India:</strong> Konsumsi listrik per kapita India diperkirakan akan berlipat ganda pada 2030 seiring 300 juta warga tambahan terhubung ke jaringan, mempertahankan permintaan impor batu bara termal yang kuat.</p>'
                        . '<p><strong>Industrialisasi ASEAN:</strong> Vietnam, Indonesia, Filipina, dan Bangladesh berada dalam fase ekspansi industri aktif, membangun pembangkit batu bara baru yang dijadwalkan beroperasi hingga 2040-an.</p>'
                        . '<p><strong>Produksi Baja:</strong> Permintaan batu bara metalurgi didukung oleh investasi infrastruktur berkelanjutan di seluruh Asia.</p>'
                        . '<h2>Prakiraan Harga</h2>'
                        . '<p>Harga benchmark Newcastle diperkirakan akan stabil di kisaran USD 110-130/ton hingga 2026. '
                        . 'Premium kalori untuk batu bara CV tinggi (6.000+ kkal/kg GAR) diperkirakan akan melebar seiring beroperasinya pembangkit efisiensi tinggi.</p>'
                        . '<h2>Implikasi Strategis bagi CDE</h2>'
                        . '<p>Portofolio produk batu bara nilai kalor tinggi CDE berada dalam posisi yang baik untuk merebut harga premium. '
                        . 'Investasi kami dalam ekspansi kapasitas wash plant akan meningkatkan hasil saleable dan fleksibilitas blending.</p>',
                ],
                'tags'        => [$tagTrend->id, $tagCoal->id, $tagMarket->id],
                'is_featured' => true,
                'views_count' => 412,
            ],

            // 2 ── Digital Transformation
            [
                'slug'    => ['en' => 'digital-transformation-in-coal-mining-operations', 'id' => 'transformasi-digital-dalam-operasi-penambangan-batu-bara'],
                'title'   => ['en' => 'Digital Transformation in Coal Mining: From Manual to Intelligent Operations', 'id' => 'Transformasi Digital dalam Pertambangan Batu Bara: Dari Manual ke Operasi Cerdas'],
                'summary' => [
                    'en' => 'AI-powered dispatch systems, autonomous haulage, and real-time fleet management are reshaping productivity and safety across modern coal mining operations.',
                    'id' => 'Sistem dispatch bertenaga AI, pengangkutan otonom, dan manajemen armada real-time mengubah produktivitas dan keselamatan di operasi penambangan batu bara modern.',
                ],
                'content' => [
                    'en' => '<h2>Why Digital Transformation Cannot Wait</h2>'
                        . '<p>Coal mining margins are under pressure from rising fuel costs, labor shortages, and growing regulatory compliance requirements. '
                        . 'Digital technologies offer the most powerful lever for improving productivity and reducing cost per tonne without compromising safety.</p>'
                        . '<h2>Mine Management Systems (MMS)</h2>'
                        . "<p>Integrated MMS aggregates real-time data from drills, shovels, trucks, and processing plants into a single dashboard. "
                        . "CDE's MMS implementation has reduced truck queue times at crushers by 28% using machine-learning dispatch optimization trained on 18 months of historical fleet data.</p>"
                        . '<h2>Autonomous Haulage</h2>'
                        . '<p>Autonomous haul trucks deployed at scale in Australia are increasingly adopted in Indonesia. '
                        . 'Benefits include 15-20% higher truck utilization, reduced tire wear, and elimination of operator-related safety incidents.</p>'
                        . '<h2>Drone Surveying and Stockpile Measurement</h2>'
                        . '<p>CDE uses LiDAR-equipped UAVs to complete topographic surveys in under 4 hours with accuracy within 0.3% of true volume — '
                        . 'critical for accurate ROM production tracking and inventory reconciliation.</p>'
                        . '<h2>Predictive Maintenance</h2>'
                        . '<p>Vibration sensors on critical assets enable our maintenance team to predict failure 14-21 days in advance. '
                        . 'In 2024, this prevented three major crusher breakdowns that would have collectively cost 11,000 tonnes of production loss.</p>',
                    'id' => '<h2>Mengapa Transformasi Digital Tidak Bisa Ditunda</h2>'
                        . '<p>Margin pertambangan batu bara berada di bawah tekanan dari kenaikan biaya bahan bakar, kekurangan tenaga kerja, dan meningkatnya persyaratan kepatuhan regulasi. '
                        . 'Teknologi digital menawarkan pengungkit paling kuat untuk meningkatkan produktivitas dan mengurangi biaya per ton tanpa mengorbankan keselamatan.</p>'
                        . '<h2>Sistem Manajemen Tambang (MMS)</h2>'
                        . '<p>MMS Terintegrasi mengagregasi data real-time dari bor, shovel, truk, dan pabrik pengolahan ke dalam satu dasbor. '
                        . 'Implementasi MMS CDE telah mengurangi waktu antrian truk di crusher sebesar 28% menggunakan optimasi dispatch berbasis machine-learning.</p>'
                        . '<h2>Pengangkutan Otonom</h2>'
                        . '<p>Truk angkut otonom yang digunakan dalam skala besar di Australia semakin diadopsi di Indonesia. '
                        . 'Manfaatnya meliputi utilisasi truk 15-20% lebih tinggi, pengurangan keausan ban, dan eliminasi insiden keselamatan terkait operator.</p>'
                        . '<h2>Survei Drone dan Pengukuran Stockpile</h2>'
                        . '<p>CDE menggunakan UAV berkemampuan LiDAR untuk menyelesaikan survei topografi dalam waktu kurang dari 4 jam dengan akurasi 0,3% dari volume sebenarnya.</p>'
                        . '<h2>Pemeliharaan Prediktif</h2>'
                        . '<p>Sensor getaran pada aset kritis memungkinkan tim kami memprediksi kegagalan 14-21 hari sebelumnya. '
                        . 'Pada 2024, hal ini mencegah tiga kerusakan crusher besar yang secara kolektif akan menyebabkan kerugian produksi 11.000 ton.</p>',
                ],
                'tags'        => [$tagTrend->id, $tagCoal->id],
                'is_featured' => false,
                'views_count' => 287,
            ],

            // 3 ── ESG Reporting Standards
            [
                'slug'    => ['en' => 'esg-reporting-standards-mining-sector-indonesia', 'id' => 'standar-pelaporan-esg-sektor-pertambangan-indonesia'],
                'title'   => ['en' => 'ESG Reporting Standards: What Indonesian Coal Miners Must Prepare For', 'id' => 'Standar Pelaporan ESG: Yang Harus Dipersiapkan Penambang Batu Bara Indonesia'],
                'summary' => [
                    'en' => 'Global institutional investors are demanding rigorous ESG disclosures. This guide unpacks the evolving ESG framework landscape — from GRI to ISSB — and what it means for Indonesian mining companies.',
                    'id' => 'Investor institusional global menuntut pengungkapan ESG yang ketat. Panduan ini menguraikan lanskap kerangka ESG yang berkembang — dari GRI hingga ISSB — dan artinya bagi perusahaan tambang Indonesia.',
                ],
                'content' => [
                    'en' => '<h2>The ESG Reporting Imperative</h2>'
                        . '<p>ESG reporting has transitioned from a voluntary best practice to a near-mandatory expectation for any mining company seeking to access international capital markets, secure export contracts, or attract institutional investors with sustainability mandates.</p>'
                        . '<h2>Key Frameworks Explained</h2>'
                        . '<p><strong>GRI Standards:</strong> The most widely used framework globally, with Mining Sector Supplement standards (GRI 12) covering tailings management, land rehabilitation, indigenous communities, and worker health and safety.</p>'
                        . '<p><strong>ISSB (IFRS S1 and S2):</strong> Focus on material sustainability risks and climate-related financial disclosures aligned with TCFD, effective for many jurisdictions from 2024.</p>'
                        . '<p><strong>OJK Sustainability Roadmap:</strong> All publicly listed mining companies must publish sustainability reports by 2025; private companies with assets above IDR 5 trillion from 2027.</p>'
                        . '<h2>Common Gaps in Indonesian Mining ESG Reports</h2>'
                        . '<ul><li>Incomplete Scope 3 emissions calculation</li>'
                        . '<li>Absence of quantified biodiversity impact metrics</li>'
                        . '<li>Limited disclosure on indigenous community consultation</li>'
                        . '<li>Missing TCFD-aligned climate scenario analysis</li></ul>'
                        . "<h2>CDE's ESG Journey</h2>"
                        . '<p>CDE has published GRI-aligned sustainability reports since 2022. Our 2024 Sustainability Report includes, for the first time, a Scope 3 emissions inventory and TCFD-compliant climate risk scenario analysis.</p>',
                    'id' => '<h2>Imperatif Pelaporan ESG</h2>'
                        . '<p>Pelaporan ESG telah bertransisi dari praktik terbaik sukarela menjadi ekspektasi yang hampir wajib bagi perusahaan tambang mana pun yang ingin mengakses pasar modal internasional, mengamankan kontrak ekspor, atau menarik investor institusional dengan mandat keberlanjutan.</p>'
                        . '<h2>Kerangka Utama yang Dijelaskan</h2>'
                        . '<p><strong>Standar GRI:</strong> Kerangka yang paling banyak digunakan secara global, dengan Suplemen Sektor Pertambangan (GRI 12) yang mencakup manajemen tailing, rehabilitasi lahan, komunitas adat, dan kesehatan serta keselamatan pekerja.</p>'
                        . '<p><strong>ISSB (IFRS S1 dan S2):</strong> Berfokus pada risiko keberlanjutan material dan pengungkapan keuangan terkait iklim selaras dengan TCFD, berlaku di banyak yurisdiksi mulai 2024.</p>'
                        . '<p><strong>Peta Jalan Keberlanjutan OJK:</strong> Semua perusahaan tambang yang terdaftar di bursa wajib mempublikasikan laporan keberlanjutan pada 2025; perusahaan swasta dengan aset di atas IDR 5 triliun mulai 2027.</p>'
                        . '<h2>Kesenjangan Umum dalam Laporan ESG Pertambangan Indonesia</h2>'
                        . '<ul><li>Perhitungan emisi Scope 3 yang tidak lengkap</li>'
                        . '<li>Tidak adanya metrik dampak keanekaragaman hayati yang dikuantifikasi</li>'
                        . '<li>Pengungkapan terbatas tentang konsultasi komunitas adat</li>'
                        . '<li>Analisis skenario iklim berbasis TCFD yang belum ada</li></ul>'
                        . '<h2>Perjalanan ESG CDE</h2>'
                        . '<p>CDE telah mempublikasikan laporan keberlanjutan yang selaras dengan GRI sejak 2022. Laporan Keberlanjutan 2024 kami mencakup untuk pertama kalinya inventaris emisi Scope 3 dan analisis skenario risiko iklim yang sesuai TCFD.</p>',
                ],
                'tags'        => [$tagTrend->id, $tagSustain->id, $tagMarket->id],
                'is_featured' => true,
                'views_count' => 356,
            ],

            // 4 ── Energy Transition & Exports
            [
                'slug'    => ['en' => 'energy-transition-impact-on-indonesian-coal-exports', 'id' => 'dampak-transisi-energi-pada-ekspor-batu-bara-indonesia'],
                'title'   => ['en' => 'Energy Transition and Indonesian Coal Exports: Navigating the New Reality', 'id' => 'Transisi Energi dan Ekspor Batu Bara Indonesia: Menavigasi Realitas Baru'],
                'summary' => [
                    'en' => 'The global energy transition is reshaping coal trade flows. Indonesian exporters must adapt their strategy, target markets, and product portfolio to thrive in the evolving energy landscape.',
                    'id' => 'Transisi energi global mengubah arus perdagangan batu bara. Eksportir Indonesia harus mengadaptasi strategi, target pasar, dan portofolio produk mereka untuk berkembang dalam lanskap energi yang terus berubah.',
                ],
                'content' => [
                    'en' => '<h2>The Shifting Landscape</h2>'
                        . "<p>Europe's rapid coal phase-out has permanently closed major traditional export markets. "
                        . 'However, this has been more than offset by surging Asian demand, and Indonesia has repositioned itself as the dominant supplier to South and Southeast Asian markets.</p>'
                        . '<h2>New Market Realities</h2>'
                        . "<p><strong>India as the Critical Growth Market:</strong> India overtook China as the world's largest coal importer in 2023. "
                        . "Indonesia supplies approximately 48% of India's thermal coal imports, a share CDE expects to grow as high-CV Indonesian coal is preferred by India's super-critical power fleet.</p>"
                        . '<p><strong>Caloric Shift:</strong> As more efficient power plants come online across Asia, demand is shifting toward higher calorific value coal (6,000+ kcal/kg GAR). '
                        . 'Low-CV producers are increasingly squeezed, while high-CV producers command growing premiums.</p>'
                        . '<p><strong>Policy Risk in China:</strong> China\'s domestic coal policy creates significant volatility. '
                        . 'Indonesian exporters must build diversified customer portfolios across India, Bangladesh, Vietnam, and Japan.</p>'
                        . '<h2>Adapting the Business Model</h2>'
                        . '<p>Forward-thinking coal companies are exploring adjacent business lines — carbon credit generation from mine reclamation, coal-to-methanol projects, '
                        . 'and compressed natural gas from coal seam methane — to hedge against long-term structural demand decline.</p>',
                    'id' => '<h2>Lanskap yang Berubah</h2>'
                        . '<p>Penghapusan batu bara yang cepat di Eropa telah secara permanen menutup pasar ekspor tradisional utama. '
                        . 'Namun, hal ini lebih dari diimbangi oleh lonjakan permintaan Asia, dan Indonesia telah memposisikan ulang dirinya sebagai pemasok dominan untuk pasar Asia Selatan dan Tenggara.</p>'
                        . '<h2>Realitas Pasar Baru</h2>'
                        . '<p><strong>India sebagai Pasar Pertumbuhan Kritis:</strong> India melampaui China sebagai importir batu bara terbesar di dunia pada 2023. '
                        . 'Indonesia menyuplai sekitar 48% impor batu bara termal India.</p>'
                        . '<p><strong>Pergeseran Kalori:</strong> Seiring semakin banyak pembangkit yang lebih efisien beroperasi di Asia, permintaan bergeser ke batu bara dengan nilai kalor lebih tinggi (6.000+ kkal/kg GAR). '
                        . 'Produsen CV rendah semakin terjepit, sementara produsen CV tinggi menikmati premium yang terus bertumbuh.</p>'
                        . '<p><strong>Risiko Kebijakan di China:</strong> Kebijakan batu bara domestik China menciptakan volatilitas signifikan. '
                        . 'Eksportir Indonesia harus membangun portofolio pelanggan yang terdiversifikasi di India, Bangladesh, Vietnam, dan Jepang.</p>'
                        . '<h2>Adaptasi Model Bisnis</h2>'
                        . '<p>Perusahaan batu bara yang berpikiran maju mengeksplorasi lini bisnis yang berdekatan — generasi kredit karbon dari reklamasi tambang, proyek coal-to-methanol, '
                        . 'dan compressed natural gas dari coal seam methane — untuk melindungi diri dari penurunan permintaan struktural jangka panjang.</p>',
                ],
                'tags'        => [$tagTrend->id, $tagCoal->id, $tagEnergy->id, $tagMarket->id],
                'is_featured' => false,
                'views_count' => 298,
            ],

            // 5 ── Safety Culture
            [
                'slug'    => ['en' => 'safety-culture-excellence-zero-harm-journey', 'id' => 'budaya-keselamatan-unggul-perjalanan-menuju-zero-harm'],
                'title'   => ['en' => "Safety Culture Excellence: CDE's Journey Toward Zero Harm", 'id' => 'Budaya Keselamatan Unggul: Perjalanan CDE Menuju Zero Harm'],
                'summary' => [
                    'en' => "Achieving zero workplace fatalities requires deliberate culture-building, leadership commitment, and data-driven safety systems. Here's how CDE achieved 3 consecutive years with zero Lost-Time Injuries.",
                    'id' => 'Mencapai nol kematian di tempat kerja membutuhkan pembangunan budaya yang disengaja, komitmen kepemimpinan, dan sistem keselamatan berbasis data. Inilah cara CDE mencapai 3 tahun berturut-turut tanpa LTI.',
                ],
                'content' => [
                    'en' => '<h2>Safety as a Core Value, Not a Priority</h2>'
                        . "<p>Many organizations treat safety as a priority — but priorities can change. CDE has embedded safety as a non-negotiable core value, "
                        . 'woven into every decision from capital allocation to daily operational planning. '
                        . "This philosophical shift, championed by our Board of Directors since 2021, is the foundation of our safety journey.</p>"
                        . '<h2>Building Psychological Safety</h2>'
                        . '<p>Workers who fear punishment for reporting near-misses will hide incidents. '
                        . "CDE introduced a <strong>No-Blame Reporting System</strong> where anonymous near-miss reports are rewarded with recognition bonuses. "
                        . 'Near-miss reports increased by 340% in Year 1, giving our safety team unprecedented visibility into latent hazards.</p>'
                        . '<h2>Fatal Risk Control Protocols</h2>'
                        . '<p>We identified our eight highest fatal-risk activities (working at height, confined space entry, isolation of energy, traffic management, blasting, etc.) '
                        . 'and developed mandatory protocols with hard controls for each. No deviation is permitted, regardless of production pressure.</p>'
                        . '<h2>Data-Driven Safety Intelligence</h2>'
                        . '<p>Our integrated HSEQ dashboard aggregates leading indicators (safety observations, toolbox talks, permit-to-work compliance) '
                        . 'and lagging indicators in real-time. Predictive analytics flags work areas showing deteriorating indicators up to 6 weeks before statistical likelihood of injury increases.</p>'
                        . '<h2>Results</h2>'
                        . '<ul>'
                        . '<li>3 consecutive years with zero Lost-Time Injuries (LTIs)</li>'
                        . '<li>LTIFR reduced from 0.87 (2020) to 0.00 (2022-2024)</li>'
                        . '<li>Near-miss reporting up 340% year-on-year</li>'
                        . '<li>ISO 45001:2018 certification achieved in 2023</li>'
                        . '</ul>',
                    'id' => '<h2>Keselamatan sebagai Nilai Inti, Bukan Prioritas</h2>'
                        . '<p>Banyak organisasi memperlakukan keselamatan sebagai prioritas — tetapi prioritas bisa berubah. '
                        . 'CDE telah menanamkan keselamatan sebagai nilai inti yang tidak dapat dikompromikan, terjalin dalam setiap keputusan mulai dari alokasi modal hingga perencanaan operasional harian.</p>'
                        . '<h2>Membangun Keamanan Psikologis</h2>'
                        . '<p>Pekerja yang takut mendapat hukuman karena melaporkan hampir-celaka akan menyembunyikan insiden. '
                        . 'CDE memperkenalkan <strong>Sistem Pelaporan Tanpa Menyalahkan</strong> di mana laporan hampir-celaka anonim dihargai dengan bonus pengakuan. '
                        . 'Laporan hampir-celaka meningkat 340% di Tahun 1.</p>'
                        . '<h2>Protokol Kontrol Risiko Fatal</h2>'
                        . '<p>Kami mengidentifikasi delapan aktivitas berisiko fatal tertinggi (bekerja di ketinggian, masuk ruang terbatas, isolasi energi, manajemen lalu lintas, peledakan, dll.) '
                        . 'dan mengembangkan protokol wajib dengan kontrol keras untuk masing-masing. Tidak ada penyimpangan yang diizinkan, terlepas dari tekanan produksi.</p>'
                        . '<h2>Kecerdasan Keselamatan Berbasis Data</h2>'
                        . '<p>Dasbor HSEQ terintegrasi kami mengagregasi indikator terdepan (observasi keselamatan, toolbox talk, kepatuhan permit-to-work) '
                        . 'dan indikator tertinggal secara real-time. Analitik prediktif menandai area kerja yang memburuk hingga 6 minggu sebelum kemungkinan cedera meningkat.</p>'
                        . '<h2>Hasil</h2>'
                        . '<ul>'
                        . '<li>3 tahun berturut-turut tanpa Cedera yang Menyebabkan Kehilangan Waktu (LTI)</li>'
                        . '<li>LTIFR berkurang dari 0,87 (2020) menjadi 0,00 (2022-2024)</li>'
                        . '<li>Pelaporan hampir-celaka meningkat 340% dari tahun ke tahun</li>'
                        . '<li>Sertifikasi ISO 45001:2018 diraih pada 2023</li>'
                        . '</ul>',
                ],
                'tags'        => [$tagTrend->id, $tagCoal->id, $tagSustain->id],
                'is_featured' => false,
                'views_count' => 241,
            ],

        ]; // end $insightArticles

        foreach ($insightArticles as $data) {
            $article = Article::firstOrCreate(
                ['slug->en' => $data['slug']['en']],
                [
                    'title'               => $data['title'],
                    'slug'                => $data['slug'],
                    'summary'             => $data['summary'],
                    'content'             => $data['content'],
                    'status'              => ArticleStatus::Published,
                    'article_category_id' => $catInsights->id,
                    'author_id'           => $admin->id,
                    'approved_by'         => $admin->id,
                    'approved_at'         => now()->subDays(rand(5, 60)),
                    'published_at'        => now()->subDays(rand(1, 30)),
                    'views_count'         => $data['views_count'],
                    'is_featured'         => $data['is_featured'],
                ]
            );
            $article->tags()->sync($data['tags']);
        }

        $this->command->info('5 Insights & Trends articles seeded.');
        $this->command->info('ArticleSeeder completed: 10 articles total.');
    }
}

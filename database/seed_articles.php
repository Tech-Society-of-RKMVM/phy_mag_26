<?php
/**
 * Database Seeder - Physics Department Wall Magazine
 * Prepopulates all 10 original articles, the editorial, site settings, and default admin.
 */

function seed_initial_database($pdo)
{
    // 1. Create Admins Table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `admins` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `username` VARCHAR(100) NOT NULL UNIQUE,
            `password` VARCHAR(255) NOT NULL,
            `full_name` VARCHAR(150) NOT NULL,
            `email` VARCHAR(150) DEFAULT NULL,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // 2. Create Articles Table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `articles` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `slug` VARCHAR(255) NOT NULL UNIQUE,
            `title` VARCHAR(255) NOT NULL,
            `summary` VARCHAR(500) NOT NULL,
            `author_name` VARCHAR(150) NOT NULL,
            `author_batch` VARCHAR(50) NOT NULL,
            `image_path` VARCHAR(255) NOT NULL,
            `content` MEDIUMTEXT NOT NULL,
            `edition_year` INT NOT NULL DEFAULT 2026,
            `published_date` DATE NOT NULL,
            `status` ENUM('published', 'draft') NOT NULL DEFAULT 'published',
            `sort_order` INT NOT NULL DEFAULT 0,
            `views_count` INT NOT NULL DEFAULT 0,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX `idx_edition_status` (`edition_year`, `status`),
            INDEX `idx_slug` (`slug`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // 3. Create Settings Table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `settings` (
            `setting_key` VARCHAR(100) PRIMARY KEY,
            `setting_value` TEXT NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // 4. Seed Default Admin if not exists (username: admin, password: admin123)
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM admins WHERE username = 'admin'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $adminPass = password_hash('admin123', PASSWORD_DEFAULT);
        $insertAdmin = $pdo->prepare("INSERT INTO admins (username, password, full_name, email) VALUES (?, ?, ?, ?)");
        $insertAdmin->execute(['admin', $adminPass, 'Department Administrator', 'physics@rkmvm.ac.in']);
    }

    // 5. Seed Site Settings & Editorial
    $settings = [
        'site_title' => 'Department of Physics - Wall Magazine',
        'college_name' => 'Ramakrishna Mission Vidyamandira',
        'department_name' => 'Department Of Physics',
        'editorial_title' => 'The Editorial',
        'editorial_content' => "<p>It all began with a fire.</p>\n<p>When early humans lit those first flames, they weren't just surviving the cold—they were reaching for something more. A spark of curiosity had been born, and with it began our long journey to understand the world around us.</p>\n<p>That curiosity has never really left us. It's what led us to gaze at the stars, to wonder about falling apples, to try and split the atom, and to ask the hardest questions. But the path hasn't always been straight or kind. In our rush to discover, we've made mistakes. We've harmed the planet, hurt each other, and sometimes used knowledge in ways that should've made us stop and think.</p>\n<p>Eventually, we found our way back to reason—to science. And through it all, physics stood by us by giving us a new perspective of seeing everything, including ourselves. But we should not forget the bigger task—the task of asking — what does this mean? What comes next?</p>\n<p>Every discovery, every equation, has consequences. Black holes. Lasers. Nuclear energy. These all began as ideas, theories, possibilities. Today, they shape our lives. Some have helped us. Some have hurt us. All of them have changed us.</p>\n<p>That's why the implications of science can't be an afterthought. They are at the heart of why we do what we do.</p>\n<p>Being a physicist—or even just someone who thinks deeply—means more than asking \"how.\" It means asking \"what now?\" It means understanding that knowledge carries responsibility.</p>\n<p>At the Department of Physics, RKMVM, we believe science is more than solving puzzles. It's about understanding our place in the world, and choosing to make that world better.</p>\n<p>Let's not just discover. Let's think.</p>\n<p>Let's not just explain. Let's care.</p>",
        'footer_text' => '© 2026 Wall magazine — RKMV physics department'
    ];

    $setStmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
    foreach ($settings as $k => $v) {
        $setStmt->execute([$k, $v]);
    }

    // 6. Seed Articles if empty
    $artCountStmt = $pdo->query("SELECT COUNT(*) FROM articles");
    if ($artCountStmt->fetchColumn() == 0) {
        $articles = [
            [
                'slug' => 'breaking-the-rules-of-time',
                'title' => 'Breaking The Rules of Time',
                'summary' => 'Breaking the rules of time...',
                'author_name' => 'Aman Mondal',
                'author_batch' => 'UG 3',
                'image_path' => 'assets/images/timecrysta.jpg',
                'published_date' => '2026-08-15',
                'edition_year' => 2026,
                'sort_order' => 1,
                'content' => "<p>Imagine a material that never sits still — not even when it's supposed to be at rest! Welcome to the weird and wonderful world of Time Crystals. These aren't your average shiny rocks. While regular crystals — like diamonds or snowflakes — repeat their structure in space, time crystals repeat themselves in time, like a magical pendulum that keeps ticking forever without ever running out of energy.</p>\n<p>The idea of time crystals was first proposed in 2012 by Nobel Prize-winning physicist Frank Wilczek — purely as a theoretical concept. This strange concept became real when scientists managed to create time crystals in laboratories using ultra-cold atoms, laser manipulations, and even Google's quantum computer in 2021.</p>\n<p><strong>Why Are They Special?</strong></p>\n<p>Time crystals break one of nature's most fundamental rules: time symmetry. Instead of behaving the same at all times, they choose certain moments to repeat their motion — defying conventional physics. Time crystals don't violate energy conservation — they exist in a state of constant oscillation without net energy loss, thanks to quantum mechanics!</p>\n<p>Still in early stages, time crystals aren't ready for everyday use. But they hold promise for quantum computing and for pushing the boundaries of what we think is possible in physics. Time Crystals remind us that nature still holds mysteries beyond our grasp — sparking questions, challenging boundaries, and inspiring the next generation of physicists to dream beyond time itself.</p>"
            ],
            [
                'slug' => 'sc-maglev-technology',
                'title' => 'SC Maglev Technology',
                'summary' => 'New age technology for faster transport...',
                'author_name' => 'Department Contributor',
                'author_batch' => 'UG 3',
                'image_path' => 'assets/images/Maglev.webp',
                'published_date' => '2026-08-15',
                'edition_year' => 2026,
                'sort_order' => 2,
                'content' => "<p>Superconducting Maglev (magnetic levitation) represents a quantum leap in modern high-speed transportation. By utilizing superconducting magnets cooled to cryogenic temperatures, these trains can achieve magnetic levitation and frictionless propulsion along dedicated guideways.</p>\n<p>Unlike conventional high-speed rail that relies on physical wheel-rail contact, SC Maglev eliminates friction entirely, unlocking unprecedented speeds surpassing 600 km/h with whisper-quiet efficiency, enhanced safety, and remarkable energy dynamics powered by electromagnetic principles.</p>"
            ],
            [
                'slug' => 'grovergpt-quantum-ai',
                'title' => 'GroverGPT',
                'summary' => 'When AI learns to think like quantum computer...',
                'author_name' => 'Arka Biswas',
                'author_batch' => 'UG 3',
                'image_path' => 'assets/images/llm.webp',
                'published_date' => '2026-08-15',
                'edition_year' => 2026,
                'sort_order' => 3,
                'content' => "<p>What happens when artificial intelligence intersects with quantum mechanics? GroverGPT represents a pioneering paradigm where machine learning models borrow algorithmic intuition from quantum computing principles—specifically Grover's search algorithm.</p>\n<p>In classical computation, searching through an unsorted database of N items requires O(N) operations. Grover's algorithm revolutionized this with quadratic speedup, achieving the search in O(√N) steps through quantum superposition and amplitude amplification.</p>\n<p>By implementing quantum-inspired heuristics within transformer attention heads and high-dimensional vector representations, modern hybrid systems can navigate massive latent spaces far faster, bridging artificial general intelligence concepts with deep physics principles.</p>"
            ],
            [
                'slug' => 'atomic-clock-gps-satellite',
                'title' => 'Atomic Clock',
                'summary' => 'Atomic clock and GPS...',
                'author_name' => 'Bijan Hazra',
                'author_batch' => 'UG 3',
                'image_path' => 'assets/images/atomicclock.webp',
                'published_date' => '2026-08-15',
                'edition_year' => 2026,
                'sort_order' => 4,
                'content' => "<p>Atomic clocks are the most accurate time and frequency standards known to humanity. Utilizing the hyper-fine transition frequencies of atoms such as Cesium-133 and Rubidium, these devices lose less than one second in tens of millions of years.</p>\n<p>The global positioning system (GPS) that guides our modern world relies directly on atomic clocks onboard orbiting satellites. Because radio signals travel at the speed of light, an error of just one microsecond translates to a spatial deviation of 300 meters! Furthermore, Einstein's theories of special and general relativity must be continuously accounted for: time ticks faster at higher gravitational potential, and slower at high orbital velocities.</p>"
            ],
            [
                'slug' => 'einstein-rosen-bridge',
                'title' => 'Einstein–Rosen Bridge',
                'summary' => 'Burrowing through the cosmos...',
                'author_name' => 'Debarghya Chakladar',
                'author_batch' => 'UG 3',
                'image_path' => 'assets/images/bridge2.jpg',
                'published_date' => '2026-08-15',
                'edition_year' => 2026,
                'sort_order' => 5,
                'content' => "<p>In 1935, Albert Einstein and Nathan Rosen published a groundbreaking theoretical paper demonstrating that general relativity allows for geometrical shortcuts connecting distant points in spacetime: the Einstein–Rosen bridge, colloquially known as a wormhole.</p>\n<p>By connecting two distinct Schwarzschild black hole horizons, such theoretical conduits offer intriguing mathematical bridges across cosmic distances. While traversable wormholes would theoretically require exotic matter with negative energy density to remain stable and open, modern quantum gravity and holographic ER=EPR conjectures continue to reveal profound links between spacetime topology and quantum entanglement.</p>"
            ],
            [
                'slug' => 'marvel-of-microchips',
                'title' => 'Marvel of Microchips',
                'summary' => 'The marvelous science behind microchips...',
                'author_name' => 'Manaswi Dutta',
                'author_batch' => 'UG 2',
                'image_path' => 'assets/images/microchip.jpg',
                'published_date' => '2026-08-15',
                'edition_year' => 2026,
                'sort_order' => 6,
                'content' => "<p>Behind every smartphone, supercomputer, and modern scientific instrument lies the triumph of solid-state physics: the semiconductor microchip.</p>\n<p>With transistor gate lengths shrunk down to single-digit nanometers, modern silicon fabrication relies on extreme ultraviolet (EUV) lithography and precise quantum tunneling control. The manipulation of energy band gaps in doped semiconductors revolutionized the 20th and 21st centuries, compressing billions of logic gates into an area smaller than a postage stamp.</p>"
            ],
            [
                'slug' => 'new-age-solar-energy',
                'title' => 'A new solar energy',
                'summary' => 'To utilize solar energy in new ways...',
                'author_name' => 'Surya Mal',
                'author_batch' => 'UG 2',
                'image_path' => 'assets/images/solar.jpg',
                'published_date' => '2026-08-15',
                'edition_year' => 2026,
                'sort_order' => 7,
                'content' => "<p>Solar energy, as we all know, is a great source of renewable energy. Though it has a bunch of advantages; it has some disadvantages also. One of them being how can we use this energy at night. A simple suggestion would be to use batteries to store the energy. But the problem is it is really costly.</p>\n<p>There are two great solutions to this problem: sand batteries and water batteries. For a sand battery, we gather piles of sand at a place and cover it with a thermally isolated system (you can imagine it as a huge thermo flux). At day time, excessive solar energy is used to heat the sand. Sand is cheap, easily available, and stores heat very efficiently. This stored thermal energy is then converted to electricity at night.</p>\n<p>At hilly areas, water batteries (pumped hydroelectric storage) can be used. Two connected water reservoirs are created at different altitudes. Daytime solar energy pumps water up, and at night, gravity-driven hydro turbines generate electricity. They are cost effective, scalable, and provide dependable power.</p>"
            ],
            [
                'slug' => 'principle-of-least-action',
                'title' => 'Principle of Least Action',
                'summary' => 'The odyssey of the universe...',
                'author_name' => 'Soumyajyoti Roy',
                'author_batch' => 'UG 2',
                'image_path' => 'assets/images/action.jpg',
                'published_date' => '2026-08-15',
                'edition_year' => 2026,
                'sort_order' => 8,
                'content' => "<p>From the trajectory of a thrown ball to Fermat's principle of least time in optics and the path integrals of quantum electrodynamics, one profound universal principle stands tall: Nature is inherently economical.</p>\n<p>The Principle of Stationary (Least) Action dictates that out of all conceivable paths a physical system could take through configuration space, it takes the precise path for which the action integral S = ∫ L dt is stationary. Formulated through Euler-Lagrange equations and Hamiltonian mechanics, this principle provides a unified philosophical and mathematical elegance governing classical mechanics, electromagnetism, general relativity, and quantum field theory.</p>"
            ],
            [
                'slug' => 'dawn-of-agi',
                'title' => 'Dawn Of AGI',
                'summary' => 'The next biggest tech revolution...',
                'author_name' => 'Aritra Mondal',
                'author_batch' => 'UG 3',
                'image_path' => 'assets/images/agi.webp',
                'published_date' => '2026-08-15',
                'edition_year' => 2026,
                'sort_order' => 9,
                'content' => "<p>The journey from artificial intelligence (AI) to artificial general intelligence (AGI) is advancing rapidly. Since 2012, AI has seen exponential progress, with breakthroughs such as deep learning models outpacing human performance on benchmarks like MMLU and ARC. GPT-4 and other transformer models excel at tasks requiring complex reasoning, but they remain limited in generalizing across domains, a key benchmark of AGI.</p>\n<p>AGI is an intelligence system that can learn, adapt, and apply know-how independently across any cognitive activity. A 2026 METR study reveals that current models can complete tasks requiring 60 minutes of expert human effort, a capability that has doubled approximately every 7 months since 2019. However, these models struggle with tasks over 4 hours in duration, with success rates falling below 20%—highlighting a gap in long-term reasoning and memory.</p>\n<p>The shift toward AGI would make automation possible for scientific discoveries as well as creative problem solving and policy development. The development of AGI presents profound opportunities alongside alignment challenges, urging physicists, computer scientists, and ethicists to steer these powerful technologies toward human flourishing.</p>"
            ],
            [
                'slug' => 'the-grandfather-paradox',
                'title' => 'The Grandfather Paradox',
                'summary' => 'Were you born or not?...',
                'author_name' => 'Trishit Malik',
                'author_batch' => 'UG 3',
                'image_path' => 'assets/images/grandfather.jpg',
                'published_date' => '2026-08-15',
                'edition_year' => 2026,
                'sort_order' => 10,
                'content' => "<p>The Grandfather Paradox is one of the most famous paradoxes of theoretical physics, challenging the logical consistency of closed timelike curves (time travel to the past). If a traveler visits the past and prevents their biological grandfather from having children, how could the time traveler ever be born to travel back in the first place?</p>\n<p>In the mid-1980s, Russian astrophysicist Igor Novikov proposed the Novikov Self-Consistency Principle, asserting that the probability of any event causing a paradox is strictly zero. Under this view, only self-consistent histories are physically possible.</p>\n<p>Alternatively, the Many-Worlds interpretation of quantum mechanics suggests that traveling back in time branches into an alternate parallel quantum branch, leaving the original timeline intact. Such paradoxes continue to inspire physicists in unraveling quantum thermodynamics and the true nature of time.</p>"
            ]
        ];

        $insArt = $pdo->prepare("
            INSERT INTO articles (slug, title, summary, author_name, author_batch, image_path, content, edition_year, published_date, status, sort_order)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        foreach ($articles as $a) {
            $insArt->execute([
                $a['slug'],
                $a['title'],
                $a['summary'],
                $a['author_name'],
                $a['author_batch'],
                $a['image_path'],
                $a['content'],
                $a['edition_year'],
                $a['published_date'],
                'published',
                $a['sort_order']
            ]);
        }
    }
}

-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: phy_mag_db
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `admins`
--

DROP TABLE IF EXISTS `admins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admins`
--

LOCK TABLES `admins` WRITE;
/*!40000 ALTER TABLE `admins` DISABLE KEYS */;
INSERT INTO `admins` VALUES (1,'admin','$2y$10$IYor1hpn.2IHimfKRUK1fuJU7CdWZQqIAny0qXi1O5JoQBUtHB/Ta','Department Administrator','physics@rkmvm.ac.in','2026-09-04 00:10:50');
/*!40000 ALTER TABLE `admins` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `articles`
--

DROP TABLE IF EXISTS `articles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `articles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `slug` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `summary` varchar(500) NOT NULL,
  `author_name` varchar(150) NOT NULL,
  `author_batch` varchar(50) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `content` mediumtext NOT NULL,
  `edition_year` int(11) NOT NULL DEFAULT 2025,
  `published_date` date NOT NULL,
  `status` enum('published','draft') NOT NULL DEFAULT 'published',
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `views_count` int(11) NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `idx_edition_status` (`edition_year`,`status`),
  KEY `idx_slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `articles`
--

LOCK TABLES `articles` WRITE;
/*!40000 ALTER TABLE `articles` DISABLE KEYS */;
INSERT INTO `articles` VALUES (1,'breaking-the-rules-of-time','Breaking The Rules of Time','Breaking the rules of time...','Aman Mondal','UG 3','assets/images/timecrysta.jpg','<p>Imagine a material that never sits still — not even when it\'s supposed to be at rest! Welcome to the weird and wonderful world of Time Crystals. These aren\'t your average shiny rocks. While regular crystals — like diamonds or snowflakes — repeat their structure in space, time crystals repeat themselves in time, like a magical pendulum that keeps ticking forever without ever running out of energy.</p>\r\n<p>The idea of time crystals was first proposed in 2012 by Nobel Prize-winning physicist Frank Wilczek — purely as a theoretical concept. This strange concept became real when scientists managed to create time crystals in laboratories using ultra-cold atoms, laser manipulations, and even Google\'s quantum computer in 2021.</p>\r\n<p><strong>Why Are They Special?</strong></p>\r\n<p>Time crystals break one of nature\'s most fundamental rules: time symmetry. Instead of behaving the same at all times, they choose certain moments to repeat their motion — defying conventional physics. Time crystals don\'t violate energy conservation — they exist in a state of constant oscillation without net energy loss, thanks to quantum mechanics!</p>\r\n<p>Still in early stages, time crystals aren\'t ready for everyday use. But they hold promise for quantum computing and for pushing the boundaries of what we think is possible in physics. Time Crystals remind us that nature still holds mysteries beyond our grasp — sparking questions, challenging boundaries, and inspiring the next generation of physicists to dream beyond time itself.</p>',2026,'2025-08-15','published',3,7,'2026-09-04 00:10:50','2026-09-04 12:00:24'),(2,'sc-maglev-technology','SC Maglev Technology','New age technology for faster transport...','Vitarka Bhattacharyya','UG 3','assets/images/Maglev.webp','<p>Superconducting Maglev (magnetic levitation) represents a quantum leap in modern high-speed transportation. By utilizing superconducting magnets cooled to cryogenic temperatures, these trains can achieve magnetic levitation and frictionless propulsion along dedicated guideways.</p>\r\n<p>Unlike conventional high-speed rail that relies on physical wheel-rail contact, SC Maglev eliminates friction entirely, unlocking unprecedented speeds surpassing 600 km/h with whisper-quiet efficiency, enhanced safety, and remarkable energy dynamics powered by electromagnetic principles.</p>',2026,'2025-08-15','published',2,2,'2026-09-04 00:10:50','2026-09-04 12:02:03'),(3,'grovergpt-quantum-ai','GroverGPT','When AI learns to think like quantum computer...','Arka Biswas','Passed out 2026','assets/images/llm.webp','<p>What happens when artificial intelligence intersects with quantum mechanics? GroverGPT represents a pioneering paradigm where machine learning models borrow algorithmic intuition from quantum computing principles—specifically Grover\'s search algorithm.</p>\n<p>In classical computation, searching through an unsorted database of N items requires O(N) operations. Grover\'s algorithm revolutionized this with quadratic speedup, achieving the search in O(√N) steps through quantum superposition and amplitude amplification.</p>\n<p>By implementing quantum-inspired heuristics within transformer attention heads and high-dimensional vector representations, modern hybrid systems can navigate massive latent spaces far faster, bridging artificial general intelligence concepts with deep physics principles.</p>',2026,'2025-08-15','published',1,0,'2026-09-04 00:10:50','2026-09-04 12:00:24'),(4,'atomic-clock-gps-satellite','Atomic Clock','Atomic clock and GPS...','Bijan Hazra','UG 3','assets/images/atomicclock.webp','<p>Atomic clocks are the most accurate time and frequency standards known to humanity. Utilizing the hyper-fine transition frequencies of atoms such as Cesium-133 and Rubidium, these devices lose less than one second in tens of millions of years.</p>\n<p>The global positioning system (GPS) that guides our modern world relies directly on atomic clocks onboard orbiting satellites. Because radio signals travel at the speed of light, an error of just one microsecond translates to a spatial deviation of 300 meters! Furthermore, Einstein\'s theories of special and general relativity must be continuously accounted for: time ticks faster at higher gravitational potential, and slower at high orbital velocities.</p>',2026,'2025-08-15','published',4,0,'2026-09-04 00:10:50','2026-09-04 11:57:00'),(5,'einstein-rosen-bridge','Einstein–Rosen Bridge','Burrowing through the cosmos...','Debarghya Chakladar','UG 3','assets/images/bridge2.jpg','<p>In 1935, Albert Einstein and Nathan Rosen published a groundbreaking theoretical paper demonstrating that general relativity allows for geometrical shortcuts connecting distant points in spacetime: the Einstein–Rosen bridge, colloquially known as a wormhole.</p>\n<p>By connecting two distinct Schwarzschild black hole horizons, such theoretical conduits offer intriguing mathematical bridges across cosmic distances. While traversable wormholes would theoretically require exotic matter with negative energy density to remain stable and open, modern quantum gravity and holographic ER=EPR conjectures continue to reveal profound links between spacetime topology and quantum entanglement.</p>',2026,'2025-08-15','published',7,2,'2026-09-04 00:10:50','2026-09-04 12:00:24'),(6,'marvel-of-microchips','Marvel of Microchips','The marvelous science behind microchips...','Manaswi Dutta','UG 2','assets/images/microchip.jpg','<p>Behind every smartphone, supercomputer, and modern scientific instrument lies the triumph of solid-state physics: the semiconductor microchip.</p>\n<p>With transistor gate lengths shrunk down to single-digit nanometers, modern silicon fabrication relies on extreme ultraviolet (EUV) lithography and precise quantum tunneling control. The manipulation of energy band gaps in doped semiconductors revolutionized the 20th and 21st centuries, compressing billions of logic gates into an area smaller than a postage stamp.</p>',2026,'2025-08-15','published',8,1,'2026-09-04 00:10:50','2026-09-04 12:00:24'),(7,'new-age-solar-energy','A new solar energy','To utilize solar energy in new ways...','Surya Mal','UG 2','assets/images/solar.jpg','<p>Solar energy, as we all know, is a great source of renewable energy. Though it has a bunch of advantages; it has some disadvantages also. One of them being how can we use this energy at night. A simple suggestion would be to use batteries to store the energy. But the problem is it is really costly.</p>\n<p>There are two great solutions to this problem: sand batteries and water batteries. For a sand battery, we gather piles of sand at a place and cover it with a thermally isolated system (you can imagine it as a huge thermo flux). At day time, excessive solar energy is used to heat the sand. Sand is cheap, easily available, and stores heat very efficiently. This stored thermal energy is then converted to electricity at night.</p>\n<p>At hilly areas, water batteries (pumped hydroelectric storage) can be used. Two connected water reservoirs are created at different altitudes. Daytime solar energy pumps water up, and at night, gravity-driven hydro turbines generate electricity. They are cost effective, scalable, and provide dependable power.</p>',2026,'2025-08-15','published',9,0,'2026-09-04 00:10:50','2026-09-04 12:00:24'),(8,'principle-of-least-action','Principle of Least Action','The odyssey of the universe...','Soumyajyoti Roy','UG 2','assets/images/action.jpg','<p>From the trajectory of a thrown ball to Fermat\'s principle of least time in optics and the path integrals of quantum electrodynamics, one profound universal principle stands tall: Nature is inherently economical.</p>\n<p>The Principle of Stationary (Least) Action dictates that out of all conceivable paths a physical system could take through configuration space, it takes the precise path for which the action integral S = ∫ L dt is stationary. Formulated through Euler-Lagrange equations and Hamiltonian mechanics, this principle provides a unified philosophical and mathematical elegance governing classical mechanics, electromagnetism, general relativity, and quantum field theory.</p>',2026,'2025-08-15','published',10,0,'2026-09-04 00:10:50','2026-09-04 12:00:24'),(9,'dawn-of-agi','Dawn Of AGI','The next biggest tech revolution...','Aritra Mondal','UG 3','assets/images/agi.webp','<p>The journey from artificial intelligence (AI) to artificial general intelligence (AGI) is advancing rapidly. Since 2012, AI has seen exponential progress, with breakthroughs such as deep learning models outpacing human performance on benchmarks like MMLU and ARC. GPT-4 and other transformer models excel at tasks requiring complex reasoning, but they remain limited in generalizing across domains, a key benchmark of AGI.</p>\n<p>AGI is an intelligence system that can learn, adapt, and apply know-how independently across any cognitive activity. A 2025 METR study reveals that current models can complete tasks requiring 60 minutes of expert human effort, a capability that has doubled approximately every 7 months since 2019. However, these models struggle with tasks over 4 hours in duration, with success rates falling below 20%—highlighting a gap in long-term reasoning and memory.</p>\n<p>The shift toward AGI would make automation possible for scientific discoveries as well as creative problem solving and policy development. The development of AGI presents profound opportunities alongside alignment challenges, urging physicists, computer scientists, and ethicists to steer these powerful technologies toward human flourishing.</p>',2026,'2025-08-15','published',5,0,'2026-09-04 00:10:50','2026-09-04 12:00:24'),(10,'the-grandfather-paradox','The Grandfather Paradox','Were you born or not?...','Trishit Malik','UG 3','assets/images/grandfather.jpg','<p>The Grandfather Paradox is one of the most famous paradoxes of theoretical physics, challenging the logical consistency of closed timelike curves (time travel to the past). If a traveler visits the past and prevents their biological grandfather from having children, how could the time traveler ever be born to travel back in the first place?</p>\n<p>In the mid-1980s, Russian astrophysicist Igor Novikov proposed the Novikov Self-Consistency Principle, asserting that the probability of any event causing a paradox is strictly zero. Under this view, only self-consistent histories are physically possible.</p>\n<p>Alternatively, the Many-Worlds interpretation of quantum mechanics suggests that traveling back in time branches into an alternate parallel quantum branch, leaving the original timeline intact. Such paradoxes continue to inspire physicists in unraveling quantum thermodynamics and the true nature of time.</p>',2026,'2025-08-15','published',6,0,'2026-09-04 00:10:50','2026-09-04 12:00:24'),(11,'neural-firestrome-unleashing-the-boltzman-machine','NEURAL FIRESTROME: UNLEASHING THE BOLTZMAN MACHINE','From statistical physics to generative AI: the network that thinks at rest.','Dibyeandu Biswas','UG 2','assets/images/Screenshot_2026-09-04_002051.png','Think of your brain right after you step into a dimly lit room: billions of neurons fire, cross-talking simultaneously, trading signals across synapses until the fuzzy shapes around you collapse into a clear image. That spontaneous internal chatter is the foundation of a Boltzmann Machine. \r\n\r\nUnlike typical feedforward AI that takes an input and pushes it straight to an output, a Boltzmann Machine behaves like a thinking brain at rest. It is a fully recurrent network of interconnected visible and hidden neurons that constantly \"talk\" to one another in both directions. It doesn’t just classify inputs; it imagines and reconstructs missing data on its own. \r\n\r\nDriven by statistical physics, the network seeks dynamic balance by minimizing its total energy state: \r\n\r\nP(v, h) = e^(-E(v, h)) / Z\r\n\r\nLower energy states represent coherent, stable memories, while high energy signals noise.Because fully connected networks explode computationally, modern systems use Restricted Boltzmann Machines (RBMs)—severing connections within the same layer to create a clean bipartite graph. Trained via Contrastive Divergence, RBMs power modern recommender systems by predicting user preferences and serve as the modular building blocks for Deep Belief Networks.',2026,'2026-09-03','published',11,6,'2026-09-04 00:21:54','2026-09-04 12:11:51'),(12,'quantum-tunneling-when-particles-refuse-to-follow-the-rules','Quantum Tunneling: When Particles Refuse to Follow the Rules','Breaking impossible barriers through the poetry of quantum chance','Bipaswan Banerjee','UG 1','assets/images/Screenshot_2026-09-04_004147.png','The universe, for all its grandeur, is also a bit of a prankster. It likes to\r\ntake the laws we think we understand — gravity, energy, logic — and\r\npoke holes right through them. And sometimes, those holes are quite\r\nliteral. Welcome to the phenomenon of quantum tunneling, where the\r\nimpossible becomes routine, and particles behave like clever little\r\nmagicians slipping through walls.\r\nLet’s start with something simple. Imagine rolling a ball toward a hill.\r\nIn our everyday, Newton-approved world, the ball will climb only if it\r\nhas enough energy. If not, it rolls back. That’s how life works — no\r\ncheating, no shortcuts. But in the quantum world, the rules loosen\r\ntheir tie and get mischievous. Here, an electron can approach an\r\nenergy barrier — one it shouldn’t be able to cross — and somehow\r\nappear on the other side. No climbing, no explosion, no trick door. It\r\njust tunnels through.\r\nHow? Because quantum particles aren’t tiny billiard balls. They’re\r\nwaves of probability, ripples of possibility that stretch and fade but\r\nnever truly vanish. When such a wave meets a barrier, part of it seeps\r\nthrough, like a whisper through a wall. There’s always a tiny chance\r\nthe particle will pop up where logic says it can’t. Multiply that chance\r\nby countless particles, and suddenly the impossible starts happening\r\nall the time.\r\nThis is not fantasy — it’s quantum mechanics, verified by experiment\r\nafter experiment. The Heisenberg’s Uncertainty Principle tells us we\r\ncan never pin down both a particle’s position and momentum\r\nperfectly. That uncertainty gives the universe room to play tricks.\r\nQuantum tunneling is the result of that uncertainty dancing at the\r\nedge of possibility.\r\nAnd what’s more thrilling? This bizarre effect isn’t just a curiosity\r\nfrom the subatomic circus — it’s the foundation of modern\r\ntechnology and cosmic wonder. The Scanning Tunneling Microscope\r\nuses tunneling to map atoms one by one, reading the contours of the\r\ninvisible world. Inside the Sun, tunneling lets protons fuse together to\r\nrelease light — without it, the stars would never shine. Even your\r\nphone’s microchips depend on tunneling phenomena to store and\r\nmove data faster than any classical rulebook allows.\r\nBut perhaps the true thrill of quantum tunneling is what it says about\r\nexistence itself. It whispers that the universe is not built from\r\ncertainty, but from chance. That barriers — whether physical, mental,\r\nor cosmic — are never absolute. Somewhere in the deep rhythm of\r\nreality, there’s always a probability, however small, that something\r\ncan pass through.\r\nQuantum tunneling isn’t just physics — it’s poetry in motion. It’s the\r\nuniverse winking at us, saying, “Don’t be so sure.” The hill may look\r\nimpossible to climb, but the quantum world reminds us: sometimes,\r\nyou don’t have to climb it at all. Sometimes, you just slip right through\r\n— quietly, impossibly, beautifully',2026,'2026-09-03','published',12,1,'2026-09-04 00:43:52','2026-09-04 12:02:20');
/*!40000 ALTER TABLE `articles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `comic_panels`
--

DROP TABLE IF EXISTS `comic_panels`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `comic_panels` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT NULL,
  `image_path` varchar(255) NOT NULL,
  `sort_order` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `comic_panels`
--

LOCK TABLES `comic_panels` WRITE;
/*!40000 ALTER TABLE `comic_panels` DISABLE KEYS */;
INSERT INTO `comic_panels` VALUES (1,'Episode 1: The Quantum Leap','assets/images/bridge.webp',1,'2026-09-04 12:25:48'),(2,'Episode 2: Crystals of Time','assets/images/timecrysta.jpg',2,'2026-09-04 12:25:48'),(3,'Episode 3: The Holographic Universe','assets/images/llm.webp',3,'2026-09-04 12:25:48');
/*!40000 ALTER TABLE `comic_panels` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contributors`
--

DROP TABLE IF EXISTS `contributors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contributors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `role` varchar(100) NOT NULL,
  `batch` varchar(50) NOT NULL,
  `avatar_path` varchar(255) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contributors`
--

LOCK TABLES `contributors` WRITE;
/*!40000 ALTER TABLE `contributors` DISABLE KEYS */;
INSERT INTO `contributors` VALUES (1,'Dr. B. K. Ghosh','Faculty Advisor & Head of Dept.','Faculty',NULL,'Guiding the academic vision and experimental physics curriculum of the department.',1,'2026-09-04 12:25:48'),(2,'Aman Mondal','Editor-in-Chief','UG 3',NULL,'Coordinating student submissions, theme curation, and scientific review.',2,'2026-09-04 12:25:48'),(3,'Manaswi Dutta','Webmaster & Technical Lead','UG 2',NULL,'Designing and developing the digital physics wall magazine portal and interactive experiences.',3,'2026-09-04 12:25:48'),(4,'Arka Biswas','Senior Writer & Illustrator','Passed out 2026',NULL,'Contributing frontier research analysis on quantum computing and AGI.',4,'2026-09-04 12:25:48'),(5,'Debarghya Chakladar','Creative Illustrator','UG 3',NULL,'Crafting original physics diagrams, celestial artwork, and department comics.',5,'2026-09-04 12:25:48'),(6,'Surya Mal','Editorial Contributor','UG 2',NULL,'Writing on renewable energies, applied physics, and materials science.',6,'2026-09-04 12:25:48');
/*!40000 ALTER TABLE `contributors` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `settings` (
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text NOT NULL,
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `settings`
--

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` VALUES ('about_bts_desc','Watch our team in action brainstorming topics, designing the wall magazine, illustrating the articles, and bringing theoretical physics concepts to life!'),('about_bts_title','Behind The Scenes: Making of the Magazine'),('about_bts_video_url','https://www.youtube.com/watch?v=dQw4w9WgXcQ'),('about_hero_subtitle','The Department of Physics Wall Magazine at Ramakrishna Mission Vidyamandira is a creative and intellectual platform for undergraduate and postgraduate students.'),('about_hero_title','About Our Wall Magazine'),('about_vision_text','<p>Physics is more than mathematical equations and laboratory experiments; it is a profound way of understanding nature and human existence. Through this wall magazine, students articulate scientific insights, question boundaries, and share knowledge across diverse topics ranging from quantum optics and solid-state devices to cosmological horizons and computational intelligence.</p>'),('college_name','Ramakrishna Mission Vidyamandira'),('comic_bottom_text','<p>Thank you for reading! Created with passion by the physics editorial & creative arts team.</p>'),('comic_title','Department Of Physics Comic Issue #1'),('comic_top_text','<p>Welcome to the official physics department comic edition! Explore this cosmic adventure illustrated and written by students of the department.</p>'),('department_name','Department Of Physics'),('editorial_content','<p>It began with a fire-a primordial spark of curiosity that launched humanity’s journey to decode the universe. Yet, every milestone carries consequences. From the mechanics of nuclear fission to the quantum precision of lasers, physics alters our reality, proving that profound knowledge demands deep responsibility.</p>\r\n<p>From a physicist’s perspective, the history of the cosmos is a grand narrative of energy, information, and order. We coexist with the Second Law of Thermodynamics, which dictates that the universe inevitably drifts toward maximum entropy and absolute chaos-much like a student&#039;s hostel room before exams. Yet, the Earth is an open, non-equilibrium system. Driven by a continuous flux of high-grade solar photons, our planet acts as a crucible for complex dissipative structures.</p>\r\n<p>Through the mathematical framework of statistical mechanics, we understand that order can blossom locally from underlying microscopic chaos. When energy flows through matter, it triggers macroscopic phase transitions. Over immense spans of cosmic time, colliding particles organized against overwhelming statistical odds, transitioning from simple chemistry to complex biological life.</p>\r\n<p>Fundamentally, humans are thermodynamic anomalies. We are highly ordered configurations of matter that process energy-mostly coffee-to temporarily push back against cosmic decay. Through us, the universe has undergone its most remarkable transition: matter has become self-aware! We are the cosmos observing, measuring, and occasionally calculating the friction of a frictionless elephant on an inclined plane.</p>\r\n<p>As you explore this magazine, remember that we are not outside the laws of nature; we are their extraordinary consequence.\r\n \r\nLet us not just explain equations!</p>\r\n<p>Let us care about their trajectory!</p>\r\n<p>At RKMVM, we believe science is far more than solving isolated puzzles; it is a conscious choice to understand our unique, responsible place in this beautifully chaotic cosmos.</p>\r\n<p>So, grab your reference frames, ignore air resistance, and</p>\r\n<p>Let&#039;s Explore! \r\n After all, Potential Energy is completely useless unless you let it transform into something dynamic.</p>'),('editorial_title','The Editorial'),('footer_text','©DEPARTMENT OF PHYSICS, RKMV'),('site_title','Department of Physics - Wall Magazine');
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-09-04 12:28:37

<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Gym & Hardware Categories (Comprehensive Category Coverage)
        $categoriesData = [
            // Gym Categories
            'Free Weights' => 'Dumbbells, barbells, bumper plates, kettlebells, and collars.',
            'Cardio Equipment' => 'Treadmills, rowers, air bikes, ellipticals, and stair climbers.',
            'Strength Machines' => 'Cable crossovers, leg presses, smith machines, and selectorized equipment.',
            'Fitness Accessories' => 'Lifting belts, resistance bands, yoga mats, gym chalk, and wrist wraps.',
            'Supplements & Nutrition' => 'Whey protein, creatine monohydrate, pre-workouts, and shakers.',
            'Recovery & Mobility' => 'Massage guns, foam rollers, compression bands, and mobility balls.',
            // Hardware Store Categories (Laboratory 5 Activity Standard)
            'Lumber' => 'Treated dimensional lumber, marine plywood sheets, and timber framing.',
            'Hardware' => 'Fasteners, hex bolts, screws, heavy-duty hinges, anchors, and padlocks.',
            'Tools' => 'Power drills, hand hammers, tape measures, saws, and socket wrenches.',
            'Electrical' => 'Copper building wire, circuit breakers, conduit, switches, and receptacles.',
            'Plumbing' => 'PVC pipes, brass ball valves, fittings, drain assemblies, and teflon tape.',
            'Paint' => 'Interior/exterior latex paints, primers, brushes, rollers, and wood varnishes.',
        ];

        $categoryMap = [];
        foreach ($categoriesData as $name => $description) {
            $categoryMap[$name] = Category::firstOrCreate(
                ['name' => $name],
                ['description' => $description]
            );
        }

        // 2. Gym & Hardware Suppliers
        $suppliersData = [
            'Strength Supply Direct' => ['email' => 'sales@strengthsupply.com', 'phone' => '(555) 101-2020', 'address' => '500 Ironworks Way, Columbus, OH'],
            'Commercial Gym Outfitters' => ['email' => 'orders@gymoutfitters.com', 'phone' => '(555) 202-3030', 'address' => '1200 Fitness Blvd, Chicago, IL'],
            'Titan Fitness Depot' => ['email' => 'titan@fitnessdepot.com', 'phone' => '(555) 303-4040', 'address' => '840 Barbell Lane, Austin, TX'],
            'Global Nutrition Labs' => ['email' => 'distro@globalnutrition.com', 'phone' => '(555) 404-5050', 'address' => '310 Protein Way, Salt Lake City, UT'],
            'Pacific Lumber & Timber Co.' => ['email' => 'orders@pacifictimber.com', 'phone' => '(02) 8888-1234', 'address' => 'Port Area, Manila, Philippines'],
            'Manila Hardware & Industrial Supply' => ['email' => 'sales@manilahardware.com', 'phone' => '(02) 8777-5678', 'address' => 'Binondo Commercial Hub, Manila'],
            'Metro Tool & Equipment Corp.' => ['email' => 'info@metrotoolcorp.ph', 'phone' => '(02) 8555-9012', 'address' => 'Industrial Ave, Quezon City, Philippines'],
        ];

        $supplierMap = [];
        foreach ($suppliersData as $name => $info) {
            $supplierMap[$name] = Supplier::firstOrCreate(
                ['name' => $name],
                [
                    'email' => $info['email'],
                    'phone' => $info['phone'],
                    'address' => $info['address'],
                    'lead_time_days' => 4,
                ]
            );
        }

        // 3. Gym & Hardware Equipment Brands
        $brandsData = [
            'Rogue Fitness', 'Eleiko', 'Hammer Strength', 'Life Fitness', 'Concept2', 'Optimum Nutrition', 'Therabody',
            'DeWalt', 'Bosch', 'Stanley', 'Boysen', 'Philflex',
        ];
        $brandMap = [];
        foreach ($brandsData as $bName) {
            $brandMap[$bName] = Brand::firstOrCreate(['name' => $bName]);
        }

        // 4. Sample Gym Products across all 6 categories (Healthy, Low Stock, Out of Stock)
        $products = [
            // Free Weights
            [
                'name' => 'Rubber Hex Dumbbell Set (5 - 50 lbs)',
                'sku' => 'GYM-DB-550',
                'category' => 'Free Weights',
                'brand' => 'Rogue Fitness',
                'supplier' => 'Strength Supply Direct',
                'quantity' => 12,
                'reorder_level' => 3,
                'unit_price' => 850.00,
                'description' => 'Heavy duty commercial rubber hex dumbbells with knurled ergonomic chrome handles.',
            ],
            [
                'name' => 'Olympic Cast Iron Bumper Plates (45 lb Pair)',
                'sku' => 'GYM-PLT-45',
                'category' => 'Free Weights',
                'brand' => 'Eleiko',
                'supplier' => 'Strength Supply Direct',
                'quantity' => 4, // LOW STOCK (<= 6)
                'reorder_level' => 6,
                'unit_price' => 195.00,
                'description' => 'IWF standard high-density virgin rubber bumper plates with stainless steel hub inserts.',
            ],
            [
                'name' => '20kg Olympic Cerakote Barbell (Men\'s 28mm)',
                'sku' => 'GYM-BAR-20K',
                'category' => 'Free Weights',
                'brand' => 'Rogue Fitness',
                'supplier' => 'Strength Supply Direct',
                'quantity' => 0, // OUT OF STOCK
                'reorder_level' => 5,
                'unit_price' => 315.00,
                'description' => '190,000 PSI tensile strength steel bar with advanced Cerakote ceramic finish for corrosion resistance.',
            ],
            [
                'name' => 'Cast Iron Competition Kettlebell (24kg / 53lb)',
                'sku' => 'GYM-KB-24K',
                'category' => 'Free Weights',
                'brand' => 'Rogue Fitness',
                'supplier' => 'Titan Fitness Depot',
                'quantity' => 18,
                'reorder_level' => 5,
                'unit_price' => 89.00,
                'description' => 'Single-piece cast iron competition kettlebell with uniform exterior diameter and color-coded handle.',
            ],

            // Cardio Equipment
            [
                'name' => 'Commercial Curved Manual Treadmill',
                'sku' => 'GYM-TRD-CV1',
                'category' => 'Cardio Equipment',
                'brand' => 'Life Fitness',
                'supplier' => 'Commercial Gym Outfitters',
                'quantity' => 5,
                'reorder_level' => 2,
                'unit_price' => 3499.00,
                'description' => 'Self-powered curved treadmill that simulates outdoor running and delivers high metabolic burn without electricity.',
            ],
            [
                'name' => 'Concept2 Model D Indoor Rower (PM5 Monitor)',
                'sku' => 'GYM-ROW-C2D',
                'category' => 'Cardio Equipment',
                'brand' => 'Concept2',
                'supplier' => 'Commercial Gym Outfitters',
                'quantity' => 2, // LOW STOCK (<= 3)
                'reorder_level' => 3,
                'unit_price' => 990.00,
                'description' => 'The world standard in rowing machines featuring flywheel damper control and Performance Monitor 5.',
            ],
            [
                'name' => 'Heavy Duty Commercial Air Bike',
                'sku' => 'GYM-BIK-AIR',
                'category' => 'Cardio Equipment',
                'brand' => 'Rogue Fitness',
                'supplier' => 'Commercial Gym Outfitters',
                'quantity' => 8,
                'reorder_level' => 2,
                'unit_price' => 799.00,
                'description' => 'Belt-driven steel fan air resistance bike designed for high-intensity interval conditioning.',
            ],

            // Strength Machines
            [
                'name' => 'Dual Adjustable Pulley Functional Trainer',
                'sku' => 'GYM-CBL-DAP',
                'category' => 'Strength Machines',
                'brand' => 'Hammer Strength',
                'supplier' => 'Commercial Gym Outfitters',
                'quantity' => 3,
                'reorder_level' => 1,
                'unit_price' => 4200.00,
                'description' => 'Commercial dual weight stack cable machine with 360-degree rotating pulleys and integrated pull-up bars.',
            ],
            [
                'name' => 'Commercial 45-Degree Incline Leg Press',
                'sku' => 'GYM-LEG-45P',
                'category' => 'Strength Machines',
                'brand' => 'Hammer Strength',
                'supplier' => 'Commercial Gym Outfitters',
                'quantity' => 2,
                'reorder_level' => 1,
                'unit_price' => 2850.00,
                'description' => 'Heavy duty plate-loaded 45-degree linear leg press with oversized footplate and safety lockouts.',
            ],
            [
                'name' => 'Adjustable Flat / Incline / Decline Utility Bench',
                'sku' => 'GYM-BNC-FID',
                'category' => 'Strength Machines',
                'brand' => 'Rogue Fitness',
                'supplier' => 'Titan Fitness Depot',
                'quantity' => 1, // LOW STOCK (<= 4)
                'reorder_level' => 4,
                'unit_price' => 445.00,
                'description' => '11-gauge steel adjustable workout bench with non-slip vinyl upholstery and wheels for portability.',
            ],
            [
                'name' => 'Counterbalanced Commercial Smith Machine',
                'sku' => 'GYM-SMT-001',
                'category' => 'Strength Machines',
                'brand' => 'Life Fitness',
                'supplier' => 'Commercial Gym Outfitters',
                'quantity' => 0, // OUT OF STOCK
                'reorder_level' => 2,
                'unit_price' => 3600.00,
                'description' => 'Smooth linear bearing guided barbell rack with counterbalanced 15lb starting resistance.',
            ],

            // Fitness Accessories
            [
                'name' => 'High-Density Non-Slip Yoga & Stretch Mat (6mm)',
                'sku' => 'GYM-MAT-6MM',
                'category' => 'Fitness Accessories',
                'brand' => 'Rogue Fitness',
                'supplier' => 'Titan Fitness Depot',
                'quantity' => 45,
                'reorder_level' => 15,
                'unit_price' => 38.00,
                'description' => 'Commercial grade closed-cell vinyl foam mat resistant to sweat, tears, and heavy floor work.',
            ],
            [
                'name' => 'Latex Resistance Loop Exercise Bands (5-Pack)',
                'sku' => 'GYM-BND-5PK',
                'category' => 'Fitness Accessories',
                'brand' => 'Rogue Fitness',
                'supplier' => 'Titan Fitness Depot',
                'quantity' => 60,
                'reorder_level' => 20,
                'unit_price' => 24.50,
                'description' => 'Tiered resistance mini bands for glute activation, warm-ups, and mobility training.',
            ],
            [
                'name' => 'Leather Lever Weightlifting Belt (10mm Thick)',
                'sku' => 'GYM-BLT-10L',
                'category' => 'Fitness Accessories',
                'brand' => 'Eleiko',
                'supplier' => 'Titan Fitness Depot',
                'quantity' => 3, // LOW STOCK (<= 6)
                'reorder_level' => 6,
                'unit_price' => 125.00,
                'description' => 'IPF approved genuine steerhide leather powerlifting belt with quick-release steel lever clasp.',
            ],
            [
                'name' => 'Gym Chalk Block Box (8 x 2oz Magnesium Carbonate)',
                'sku' => 'GYM-CHK-8PK',
                'category' => 'Fitness Accessories',
                'brand' => 'Rogue Fitness',
                'supplier' => 'Titan Fitness Depot',
                'quantity' => 30,
                'reorder_level' => 10,
                'unit_price' => 18.00,
                'description' => 'Pure magnesium carbonate chalk blocks for dry grip on heavy deadlifts, pull-ups, and gymnastics.',
            ],

            // Supplements & Nutrition
            [
                'name' => '100% Whey Gold Standard Protein Powder (5 lbs)',
                'sku' => 'GYM-PRO-5CH',
                'category' => 'Supplements & Nutrition',
                'brand' => 'Optimum Nutrition',
                'supplier' => 'Global Nutrition Labs',
                'quantity' => 35,
                'reorder_level' => 10,
                'unit_price' => 74.99,
                'description' => 'Double rich chocolate whey protein isolate powder delivering 24g protein and 5.5g BCAAs per scoop.',
            ],
            [
                'name' => 'Micronized Creatine Monohydrate (600g / 120 Serv)',
                'sku' => 'GYM-CRE-600',
                'category' => 'Supplements & Nutrition',
                'brand' => 'Optimum Nutrition',
                'supplier' => 'Global Nutrition Labs',
                'quantity' => 4, // LOW STOCK (<= 8)
                'reorder_level' => 8,
                'unit_price' => 34.50,
                'description' => '100% pure micronized creatine powder supporting muscle strength, explosive power, and recovery.',
            ],
            [
                'name' => 'High-Performance Pre-Workout Energy Formula',
                'sku' => 'GYM-PWO-30S',
                'category' => 'Supplements & Nutrition',
                'brand' => 'Optimum Nutrition',
                'supplier' => 'Global Nutrition Labs',
                'quantity' => 22,
                'reorder_level' => 8,
                'unit_price' => 39.99,
                'description' => 'Formulated with caffeine, beta-alanine, and citrulline for intense energy, endurance, and pump.',
            ],

            // Recovery & Mobility
            [
                'name' => 'Deep Tissue Percussive Massage Gun (Theragun Pro)',
                'sku' => 'GYM-REC-GUN',
                'category' => 'Recovery & Mobility',
                'brand' => 'Therabody',
                'supplier' => 'Commercial Gym Outfitters',
                'quantity' => 6,
                'reorder_level' => 2,
                'unit_price' => 399.00,
                'description' => 'Professional percussive therapy device with 16mm amplitude, 6 attachment heads, and Bluetooth app control.',
            ],
            [
                'name' => 'Grid Trigger-Point Deep Tissue Foam Roller',
                'sku' => 'GYM-FLR-13G',
                'category' => 'Recovery & Mobility',
                'brand' => 'Therabody',
                'supplier' => 'Titan Fitness Depot',
                'quantity' => 25,
                'reorder_level' => 8,
                'unit_price' => 36.95,
                'description' => 'Multi-density hollow core foam roller for myofascial release, tight muscle knots, and flexibility.',
            ],
            [
                'name' => 'Floss Compression Mobility Recovery Bands (Pair)',
                'sku' => 'GYM-REC-FLS',
                'category' => 'Recovery & Mobility',
                'brand' => 'Rogue Fitness',
                'supplier' => 'Titan Fitness Depot',
                'quantity' => 2, // LOW STOCK (<= 5)
                'reorder_level' => 5,
                'unit_price' => 22.00,
                'description' => 'Natural latex compression wrap bands used for blood flow restriction, joint flossing, and swelling reduction.',
            ],

            // ==========================================
            // HARDWARE STORE PRODUCTS (Laboratory 5)
            // ==========================================

            // Lumber
            [
                'name' => '2x4 Treated Kiln-Dried Framing Lumber (8ft)',
                'sku' => 'LMB-2X4-08P',
                'category' => 'Lumber',
                'brand' => null,
                'supplier' => 'Pacific Lumber & Timber Co.',
                'quantity' => 120,
                'reorder_level' => 25,
                'unit_price' => 285.00,
                'description' => 'Structural #2 grade kiln-dried construction lumber, ideal for framing and load-bearing studs.',
            ],
            [
                'name' => '3/4-inch Marine Plywood Sheet (4x8ft)',
                'sku' => 'LMB-PLY-34M',
                'category' => 'Lumber',
                'brand' => null,
                'supplier' => 'Pacific Lumber & Timber Co.',
                'quantity' => 6, // LOW STOCK (<= 15)
                'reorder_level' => 15,
                'unit_price' => 1450.00,
                'description' => 'Waterproof phenolic-bonded marine plywood sheet for moisture-resistant construction.',
            ],
            [
                'name' => '1x2 S4S Kiln-Dried Wood Furring Strips (10ft)',
                'sku' => 'LMB-FUR-1X2',
                'category' => 'Lumber',
                'brand' => null,
                'supplier' => 'Pacific Lumber & Timber Co.',
                'quantity' => 0, // OUT OF STOCK
                'reorder_level' => 30,
                'unit_price' => 85.00,
                'description' => 'Smooth surfaced four sides (S4S) wood battens for ceiling grids and drywall backing.',
            ],

            // Hardware
            [
                'name' => 'Grade 8 Heavy Duty Hex Bolt & Nut Set (1/2" x 3")',
                'sku' => 'HDW-BLT-G8',
                'category' => 'Hardware',
                'brand' => 'Stanley',
                'supplier' => 'Manila Hardware & Industrial Supply',
                'quantity' => 250,
                'reorder_level' => 50,
                'unit_price' => 45.00,
                'description' => 'High-strength yellow zinc plated carbon steel bolts engineered for structural load applications.',
            ],
            [
                'name' => 'Stainless Steel Wood Screws #8 x 2" (100-pack)',
                'sku' => 'HDW-SCR-SS8',
                'category' => 'Hardware',
                'brand' => 'Stanley',
                'supplier' => 'Manila Hardware & Industrial Supply',
                'quantity' => 12, // LOW STOCK (<= 20)
                'reorder_level' => 20,
                'unit_price' => 180.00,
                'description' => 'Rust-proof 304 grade stainless steel flat-head Phillips screws for exterior carpentry.',
            ],
            [
                'name' => 'Heavy Duty Solid Brass Padlock (50mm)',
                'sku' => 'HDW-LCK-BR50',
                'category' => 'Hardware',
                'brand' => 'Stanley',
                'supplier' => 'Manila Hardware & Industrial Supply',
                'quantity' => 35,
                'reorder_level' => 10,
                'unit_price' => 395.00,
                'description' => 'Weather-resistant solid brass padlock with hardened steel shackle and double locking pins.',
            ],

            // Tools
            [
                'name' => '20V Max Cordless Brushless Compact Drill Kit',
                'sku' => 'TLS-DRL-20V',
                'category' => 'Tools',
                'brand' => 'DeWalt',
                'supplier' => 'Metro Tool & Equipment Corp.',
                'quantity' => 18,
                'reorder_level' => 5,
                'unit_price' => 4899.00,
                'description' => 'High-performance brushless motor delivers 340 unit watts out; includes 2 Li-ion batteries and charger.',
            ],
            [
                'name' => '16oz Anti-Vibe Fiberglass Curved Claw Hammer',
                'sku' => 'TLS-HMR-16F',
                'category' => 'Tools',
                'brand' => 'Stanley',
                'supplier' => 'Metro Tool & Equipment Corp.',
                'quantity' => 40,
                'reorder_level' => 8,
                'unit_price' => 420.00,
                'description' => 'Drop-forged carbon steel head with shock-absorbing non-slip ergonomic rubber grip.',
            ],
            [
                'name' => '25ft Heavy Duty Magnetic Tape Measure',
                'sku' => 'TLS-TAP-25M',
                'category' => 'Tools',
                'brand' => 'Bosch',
                'supplier' => 'Metro Tool & Equipment Corp.',
                'quantity' => 4, // LOW STOCK (<= 10)
                'reorder_level' => 10,
                'unit_price' => 350.00,
                'description' => 'Nylon-coated blade with 13ft standout, dual-sided markings, and ultra-strong magnetic hook.',
            ],

            // Electrical
            [
                'name' => '12/2 THHN Solid Copper Building Wire (150m Roll)',
                'sku' => 'ELC-WIR-122',
                'category' => 'Electrical',
                'brand' => 'Philflex',
                'supplier' => 'Manila Hardware & Industrial Supply',
                'quantity' => 15,
                'reorder_level' => 5,
                'unit_price' => 3850.00,
                'description' => '99.9% pure oxygen-free copper electrical building wire with flame-retardant PVC jacket.',
            ],
            [
                'name' => '20A 1-Pole Miniature Circuit Breaker (DIN Rail)',
                'sku' => 'ELC-BRK-20A',
                'category' => 'Electrical',
                'brand' => 'Bosch',
                'supplier' => 'Manila Hardware & Industrial Supply',
                'quantity' => 55,
                'reorder_level' => 15,
                'unit_price' => 280.00,
                'description' => 'Thermal magnetic overload and short circuit protection breaker rated for 6kA breaking capacity.',
            ],
            [
                'name' => '15A 125V Duplex Receptacle Wall Outlet (White)',
                'sku' => 'ELC-OUT-15A',
                'category' => 'Electrical',
                'brand' => 'Philflex',
                'supplier' => 'Manila Hardware & Industrial Supply',
                'quantity' => 0, // OUT OF STOCK
                'reorder_level' => 25,
                'unit_price' => 110.00,
                'description' => 'Impact-resistant thermoplastic wall socket with side and back push-in terminals.',
            ],

            // Plumbing
            [
                'name' => '3/4-inch Schedule 40 Blue PVC Water Pipe (10ft)',
                'sku' => 'PLM-PVC-34',
                'category' => 'Plumbing',
                'brand' => null,
                'supplier' => 'Manila Hardware & Industrial Supply',
                'quantity' => 85,
                'reorder_level' => 20,
                'unit_price' => 195.00,
                'description' => 'Potable water certified rigid PVC pipe with high tensile and burst strength ratings.',
            ],
            [
                'name' => 'Solid Brass Quarter-Turn Ball Valve (3/4")',
                'sku' => 'PLM-VLV-BL34',
                'category' => 'Plumbing',
                'brand' => 'Stanley',
                'supplier' => 'Manila Hardware & Industrial Supply',
                'quantity' => 7, // LOW STOCK (<= 12)
                'reorder_level' => 12,
                'unit_price' => 320.00,
                'description' => 'Corrosion-resistant forged brass ball valve with chrome-plated brass ball and vinyl grip lever.',
            ],
            [
                'name' => 'Heavy Duty PTFE Thread Seal Teflon Tape (1/2" x 520")',
                'sku' => 'PLM-TEF-05',
                'category' => 'Plumbing',
                'brand' => null,
                'supplier' => 'Manila Hardware & Industrial Supply',
                'quantity' => 150,
                'reorder_level' => 30,
                'unit_price' => 35.00,
                'description' => 'High-density industrial grade teflon pipe joint tape for leak-proof plumbing threads.',
            ],

            // Paint
            [
                'name' => 'Permacoat Flat Latex White Architectural Paint (4L)',
                'sku' => 'PNT-LTX-WHT4',
                'category' => 'Paint',
                'brand' => 'Boysen',
                'supplier' => 'Manila Hardware & Industrial Supply',
                'quantity' => 28,
                'reorder_level' => 10,
                'unit_price' => 680.00,
                'description' => '100% acrylic water-based interior paint offering maximum hiding, mildew resistance, and easy clean.',
            ],
            [
                'name' => '4-inch Premium Polyester Paint Roller Kit with Tray',
                'sku' => 'PNT-ROL-4IN',
                'category' => 'Paint',
                'brand' => 'Boysen',
                'supplier' => 'Metro Tool & Equipment Corp.',
                'quantity' => 5, // LOW STOCK (<= 15)
                'reorder_level' => 15,
                'unit_price' => 165.00,
                'description' => 'High-density woven fabric mini-roller for trim, doors, and smooth uniform wall finish.',
            ],
            [
                'name' => 'Clear Gloss Polyurethane Interior Wood Varnish (1L)',
                'sku' => 'PNT-VAR-1L',
                'category' => 'Paint',
                'brand' => 'Boysen',
                'supplier' => 'Pacific Lumber & Timber Co.',
                'quantity' => 22,
                'reorder_level' => 6,
                'unit_price' => 340.00,
                'description' => 'Ultra-hard protective coating for timber, flooring, cabinets, and decorative wood surfaces.',
            ],
        ];

        $adminUser = User::first();

        foreach ($products as $p) {
            $cat = $categoryMap[$p['category']] ?? null;
            $sup = $supplierMap[$p['supplier']] ?? null;
            $brd = $brandMap[$p['brand']] ?? null;

            $product = Product::updateOrCreate(
                ['sku' => $p['sku']],
                [
                    'name' => $p['name'],
                    'category' => $p['category'],
                    'category_id' => $cat?->id,
                    'supplier' => $p['supplier'],
                    'supplier_id' => $sup?->id,
                    'brand_id' => $brd?->id,
                    'quantity' => $p['quantity'],
                    'reorder_level' => $p['reorder_level'],
                    'unit_price' => $p['unit_price'],
                    'description' => $p['description'],
                ]
            );

            // Record initial stock transaction without double-counting
            if ($p['quantity'] > 0) {
                InventoryTransaction::withoutEvents(function () use ($product, $p, $adminUser) {
                    InventoryTransaction::firstOrCreate(
                        [
                            'product_id' => $product->id,
                            'reference_number' => 'INIT-' . $p['sku'],
                        ],
                        [
                            'user_id' => $adminUser?->id,
                            'type' => 'stock_in',
                            'quantity' => $p['quantity'],
                            'unit_price' => $p['unit_price'],
                            'reference' => 'INIT-' . $p['sku'],
                            'notes' => 'Initial gym inventory count',
                        ]
                    );
                });
            }
        }
    }
}

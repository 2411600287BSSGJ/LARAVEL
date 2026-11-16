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
        // 1. Gym Equipment Categories
        $categoriesData = [
            'Free Weights' => 'Dumbbells, barbells, bumper plates, kettlebells, and collars.',
            'Cardio Equipment' => 'Treadmills, rowers, air bikes, ellipticals, and stair climbers.',
            'Strength Machines' => 'Cable crossovers, leg presses, smith machines, and selectorized equipment.',
            'Fitness Accessories' => 'Lifting belts, resistance bands, yoga mats, gym chalk, and wrist wraps.',
            'Supplements & Nutrition' => 'Whey protein, creatine monohydrate, pre-workouts, and shakers.',
            'Recovery & Mobility' => 'Massage guns, foam rollers, compression bands, and mobility balls.',
        ];

        $categoryMap = [];
        foreach ($categoriesData as $name => $description) {
            $categoryMap[$name] = Category::firstOrCreate(
                ['name' => $name],
                ['description' => $description]
            );
        }

        // 2. Gym Suppliers
        $suppliersData = [
            'Strength Supply Direct' => ['email' => 'sales@strengthsupply.com', 'phone' => '(555) 101-2020', 'address' => '500 Ironworks Way, Columbus, OH'],
            'Commercial Gym Outfitters' => ['email' => 'orders@gymoutfitters.com', 'phone' => '(555) 202-3030', 'address' => '1200 Fitness Blvd, Chicago, IL'],
            'Titan Fitness Depot' => ['email' => 'titan@fitnessdepot.com', 'phone' => '(555) 303-4040', 'address' => '840 Barbell Lane, Austin, TX'],
            'Global Nutrition Labs' => ['email' => 'distro@globalnutrition.com', 'phone' => '(555) 404-5050', 'address' => '310 Protein Way, Salt Lake City, UT'],
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

        // 3. Gym Equipment Brands
        $brandsData = ['Rogue Fitness', 'Eleiko', 'Hammer Strength', 'Life Fitness', 'Concept2', 'Optimum Nutrition', 'Therabody'];
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

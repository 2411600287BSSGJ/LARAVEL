<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create or ensure categories exist
        $categoriesData = [
            'Lumber' => 'Dimensional lumber, sheet goods, studs, and treated framing wood.',
            'Hardware' => 'Fasteners, screws, bolts, anchors, hinges, and builder hardware.',
            'Tools' => 'Hand tools, power tools, measuring devices, and job site accessories.',
            'Electrical' => 'Wiring, circuit switches, boxes, outlets, and conduit supplies.',
            'Plumbing' => 'Pipes, brass valves, fittings, primers, and drainage supplies.',
            'Paint' => 'Interior and exterior paints, primers, brushes, rollers, and sealants.',
        ];

        $categoryMap = [];
        foreach ($categoriesData as $name => $description) {
            $categoryMap[$name] = Category::firstOrCreate(
                ['name' => $name],
                ['description' => $description]
            );
        }

        // 2. Create or ensure suppliers exist
        $suppliersData = [
            'Pacific Timber Co.' => ['email' => 'sales@pacifictimber.com', 'phone' => '(555) 234-5678', 'address' => '100 Forest Way, Portland, OR'],
            'Fastener Supply Direct' => ['email' => 'orders@fastenersupply.com', 'phone' => '(555) 345-6789', 'address' => '450 Industrial Parkway, Cleveland, OH'],
            'Builders Hardware Corp' => ['email' => 'support@buildershardware.com', 'phone' => '(555) 456-7890', 'address' => '820 Commerce Blvd, Chicago, IL'],
            'Apex Tool Group' => ['email' => 'rep@apextools.com', 'phone' => '(555) 567-8901', 'address' => '910 Precision Lane, Milwaukee, WI'],
            'VoltEdge Electrical' => ['email' => 'sales@voltedge.com', 'phone' => '(555) 678-9012', 'address' => '330 Circuit Drive, Austin, TX'],
            'HydroFlow Plumbing' => ['email' => 'info@hydroflow.com', 'phone' => '(555) 789-0123', 'address' => '715 Riverview Ave, Charlotte, NC'],
            'Spectrum Coatings' => ['email' => 'colors@spectrumcoatings.com', 'phone' => '(555) 890-1234', 'address' => '220 Colorway St, Atlanta, GA'],
        ];

        $supplierMap = [];
        foreach ($suppliersData as $name => $info) {
            $supplierMap[$name] = Supplier::firstOrCreate(
                ['name' => $name],
                [
                    'email' => $info['email'],
                    'phone' => $info['phone'],
                    'address' => $info['address'],
                    'lead_time_days' => 5,
                ]
            );
        }

        // 3. Create Brands
        $brandsData = ['TimberCraft', 'GripTite', 'Stanley Black', 'DeWalt Pro', 'Southwire', 'SharkBite', 'Behr Premium'];
        $brandMap = [];
        foreach ($brandsData as $bName) {
            $brandMap[$bName] = Brand::firstOrCreate(['name' => $bName]);
        }

        // 4. Sample Products: At least 18 items across all 6 categories, with varied stock scenarios
        $products = [
            // Lumber
            [
                'name' => '2x4x8 Kiln-Dried Douglas Fir Stud',
                'sku' => 'LUM-DF-248',
                'category' => 'Lumber',
                'brand' => 'TimberCraft',
                'supplier' => 'Pacific Timber Co.',
                'quantity' => 150,
                'reorder_level' => 30,
                'unit_price' => 6.98,
                'description' => 'Premium grade kiln-dried structural lumber stud suitable for framing and general construction.',
            ],
            [
                'name' => '3/4-in x 4-ft x 8-ft CDX Plywood Sheet',
                'sku' => 'LUM-PLY-348',
                'category' => 'Lumber',
                'brand' => 'TimberCraft',
                'supplier' => 'Pacific Timber Co.',
                'quantity' => 8, // LOW STOCK (<= 15)
                'reorder_level' => 15,
                'unit_price' => 38.50,
                'description' => 'Exterior grade sheathing plywood ideal for subfloors, wall sheathing, and roof decking.',
            ],
            [
                'name' => '1x6x8 Clear Pine Finish Board',
                'sku' => 'LUM-PN-168',
                'category' => 'Lumber',
                'brand' => 'TimberCraft',
                'supplier' => 'Pacific Timber Co.',
                'quantity' => 0, // OUT OF STOCK
                'reorder_level' => 10,
                'unit_price' => 14.25,
                'description' => 'Fine grain smooth clear pine board designed for furniture, trim, and decorative shelving.',
            ],

            // Hardware
            [
                'name' => '3-in #10 Wood Construction Screws 100pk',
                'sku' => 'HDW-SCR-310',
                'category' => 'Hardware',
                'brand' => 'GripTite',
                'supplier' => 'Fastener Supply Direct',
                'quantity' => 95,
                'reorder_level' => 20,
                'unit_price' => 14.99,
                'description' => 'Heavy duty exterior coated wood screws with star drive bit included.',
            ],
            [
                'name' => '1/2-in x 3-in Zinc Carriage Bolts 25pk',
                'sku' => 'HDW-BLT-123',
                'category' => 'Hardware',
                'brand' => 'GripTite',
                'supplier' => 'Fastener Supply Direct',
                'quantity' => 6, // LOW STOCK (<= 12)
                'reorder_level' => 12,
                'unit_price' => 18.25,
                'description' => 'Corrosion-resistant zinc plated carriage bolts for timber connections and deck building.',
            ],
            [
                'name' => '3-1/2-in Satin Nickel Door Hinges 3pk',
                'sku' => 'HDW-HNG-35S',
                'category' => 'Hardware',
                'brand' => 'GripTite',
                'supplier' => 'Builders Hardware Corp',
                'quantity' => 42,
                'reorder_level' => 15,
                'unit_price' => 11.50,
                'description' => 'Smooth operating residential door hinges complete with mounting screws.',
            ],

            // Tools
            [
                'name' => 'Heavy Duty 20oz Steel Rip Claw Hammer',
                'sku' => 'TLS-HMR-20Z',
                'category' => 'Tools',
                'brand' => 'Apex Tool Group',
                'supplier' => 'Apex Tool Group',
                'quantity' => 28,
                'reorder_level' => 8,
                'unit_price' => 26.95,
                'description' => 'One-piece forged steel hammer with shock reduction anti-vibration grip.',
            ],
            [
                'name' => '25-ft Magnetic Measuring Tape',
                'sku' => 'TLS-TAP-25M',
                'category' => 'Tools',
                'brand' => 'Apex Tool Group',
                'supplier' => 'Apex Tool Group',
                'quantity' => 40,
                'reorder_level' => 10,
                'unit_price' => 19.99,
                'description' => 'Durable dual-sided printed measuring tape with 14ft standout and magnetic hook.',
            ],
            [
                'name' => '20V Max Brushless Cordless Drill Kit',
                'sku' => 'TLS-DRL-20V',
                'category' => 'Tools',
                'brand' => 'DeWalt Pro',
                'supplier' => 'Apex Tool Group',
                'quantity' => 3, // LOW STOCK (<= 5)
                'reorder_level' => 5,
                'unit_price' => 139.00,
                'description' => 'Compact brushless drill with 2 lithium-ion batteries, charger, and contractor carrying bag.',
            ],

            // Electrical
            [
                'name' => '14/2 NM-B Romex Electrical Cable 250ft',
                'sku' => 'ELE-ROM-142',
                'category' => 'Electrical',
                'brand' => 'Southwire',
                'supplier' => 'VoltEdge Electrical',
                'quantity' => 22,
                'reorder_level' => 8,
                'unit_price' => 115.00,
                'description' => 'UL listed non-metallic sheathed copper electrical wire for indoor branch circuits.',
            ],
            [
                'name' => '15 Amp Single-Pole Decorator Light Switch',
                'sku' => 'ELE-SWT-15A',
                'category' => 'Electrical',
                'brand' => 'Southwire',
                'supplier' => 'VoltEdge Electrical',
                'quantity' => 120,
                'reorder_level' => 25,
                'unit_price' => 3.49,
                'description' => 'Modern smooth paddle rocker switch rated for residential 120V lighting circuits.',
            ],
            [
                'name' => '15A Tamper-Resistant Duplex Outlet 10pk',
                'sku' => 'ELE-OUT-15T',
                'category' => 'Electrical',
                'brand' => 'Southwire',
                'supplier' => 'VoltEdge Electrical',
                'quantity' => 0, // OUT OF STOCK
                'reorder_level' => 15,
                'unit_price' => 24.50,
                'description' => 'Safety shutter tamper-resistant wall electrical outlets meeting modern building codes.',
            ],

            // Plumbing
            [
                'name' => '3/4-in x 10-ft Schedule 40 PVC Pipe',
                'sku' => 'PLM-PVC-3410',
                'category' => 'Plumbing',
                'brand' => 'SharkBite',
                'supplier' => 'HydroFlow Plumbing',
                'quantity' => 75,
                'reorder_level' => 20,
                'unit_price' => 8.75,
                'description' => 'Rigid PVC pressure pipe suitable for potable water distribution and irrigation.',
            ],
            [
                'name' => '1/2-in Push-to-Connect Brass Ball Valve',
                'sku' => 'PLM-VLV-12B',
                'category' => 'Plumbing',
                'brand' => 'SharkBite',
                'supplier' => 'HydroFlow Plumbing',
                'quantity' => 4, // LOW STOCK (<= 10)
                'reorder_level' => 10,
                'unit_price' => 16.80,
                'description' => 'Full port brass shut-off valve compatible with copper, PEX, and CPVC piping.',
            ],
            [
                'name' => 'Multi-Purpose PVC Primer and Cement Pack',
                'sku' => 'PLM-GLU-8OZ',
                'category' => 'Plumbing',
                'brand' => 'SharkBite',
                'supplier' => 'HydroFlow Plumbing',
                'quantity' => 35,
                'reorder_level' => 12,
                'unit_price' => 13.20,
                'description' => 'Dual pack purple primer and medium clear solvent cement for PVC pipe bonding.',
            ],

            // Paint
            [
                'name' => 'Premium Acrylic Interior Latex Flat Paint (1 Gal)',
                'sku' => 'PNT-INT-WHT1',
                'category' => 'Paint',
                'brand' => 'Behr Premium',
                'supplier' => 'Spectrum Coatings',
                'quantity' => 38,
                'reorder_level' => 10,
                'unit_price' => 42.99,
                'description' => 'One-coat hide paint and primer in one, pure white finish with high scrub resistance.',
            ],
            [
                'name' => 'Heavy Duty 9-in Paint Roller Kit (5-Piece)',
                'sku' => 'PNT-ROL-9KT',
                'category' => 'Paint',
                'brand' => 'Behr Premium',
                'supplier' => 'Spectrum Coatings',
                'quantity' => 50,
                'reorder_level' => 15,
                'unit_price' => 17.50,
                'description' => 'Complete painting kit including metal tray, roller frame, 2 microfiber covers, and 2-in angle brush.',
            ],
            [
                'name' => '2-in Professional Angle Sash Paint Brush',
                'sku' => 'PNT-BRS-2AS',
                'category' => 'Paint',
                'brand' => 'Behr Premium',
                'supplier' => 'Spectrum Coatings',
                'quantity' => 5, // LOW STOCK (<= 10)
                'reorder_level' => 10,
                'unit_price' => 11.25,
                'description' => 'Chiseled synthetic filament brush engineered for sharp trim cutting and clean edging.',
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
                            'notes' => 'Initial inventory seed count',
                        ]
                    );
                });
            }
        }
    }
}

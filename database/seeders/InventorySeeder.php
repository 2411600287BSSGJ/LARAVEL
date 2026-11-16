<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Transaction;
use App\Services\ReorderService;
use Illuminate\Database\Seeder;

class InventorySeeder extends Seeder
{
    public function run(): void
    {
        $brands = [];
        foreach ([
            'IronPeak' => 'Strength and free-weight equipment',
            'CardioCore' => 'Cardiovascular training equipment',
            'FlexFit' => 'Functional training accessories',
            'FuelWorks' => 'Sports nutrition and supplements',
            'PulseForge' => 'Functional training and strength systems',
        ] as $name => $description) {
            $brands[$name] = Brand::firstOrCreate(['name' => $name], ['description' => $description]);
        }

        $categories = [];
        foreach ([
            'Strength Equipment',
            'Cardio Equipment',
            'Free Weights',
            'Supplements',
            'Accessories',
        ] as $name) {
            $categories[$name] = Category::firstOrCreate(['name' => $name]);
        }

        $suppliers = [];
        foreach ([
            'IronPeak Supply' => ['contact' => 'Alex Morgan', 'email' => 'orders@ironpeak.example', 'lead_time' => 5],
            'CardioCore Distribution' => ['contact' => 'Jamie Lee', 'email' => 'sales@cardiocore.example', 'lead_time' => 7],
            'Gym Essentials Wholesale' => ['contact' => 'Taylor Reed', 'email' => 'hello@gymessentials.example', 'lead_time' => 3],
            'FuelWorks Nutrition' => ['contact' => 'Casey Park', 'email' => 'wholesale@fuelworks.example', 'lead_time' => 4],
        ] as $name => $details) {
            $suppliers[$name] = Supplier::firstOrCreate(
                ['email' => $details['email']],
                [
                    'name' => $name,
                    'contact_person' => $details['contact'],
                    'lead_time_days' => $details['lead_time'],
                ],
            );
        }

        $products = [
            ['sku' => 'IP-BB-001', 'name' => 'Olympic Barbell 20 kg', 'brand' => 'IronPeak', 'category' => 'Strength Equipment', 'supplier' => 'IronPeak Supply', 'quantity' => 14, 'reorder' => 6, 'price' => 189.00],
            ['sku' => 'IP-BN-002', 'name' => 'Adjustable Weight Bench', 'brand' => 'IronPeak', 'category' => 'Strength Equipment', 'supplier' => 'IronPeak Supply', 'quantity' => 3, 'reorder' => 5, 'price' => 249.00],
            ['sku' => 'IP-DP-003', 'name' => 'Rubber Dumbbell Pair 10 kg', 'brand' => 'IronPeak', 'category' => 'Free Weights', 'supplier' => 'Gym Essentials Wholesale', 'quantity' => 4, 'reorder' => 10, 'price' => 68.50],
            ['sku' => 'IP-KB-004', 'name' => 'Competition Kettlebell 16 kg', 'brand' => 'IronPeak', 'category' => 'Free Weights', 'supplier' => 'Gym Essentials Wholesale', 'quantity' => 11, 'reorder' => 5, 'price' => 54.00],
            ['sku' => 'CC-TR-101', 'name' => 'Commercial Treadmill', 'brand' => 'CardioCore', 'category' => 'Cardio Equipment', 'supplier' => 'CardioCore Distribution', 'quantity' => 2, 'reorder' => 3, 'price' => 3299.00],
            ['sku' => 'CC-RW-102', 'name' => 'Air Resistance Rower', 'brand' => 'CardioCore', 'category' => 'Cardio Equipment', 'supplier' => 'CardioCore Distribution', 'quantity' => 5, 'reorder' => 2, 'price' => 1199.00],
            ['sku' => 'CC-BK-103', 'name' => 'Indoor Cycling Bike', 'brand' => 'CardioCore', 'category' => 'Cardio Equipment', 'supplier' => 'CardioCore Distribution', 'quantity' => 0, 'reorder' => 2, 'price' => 899.00],
            ['sku' => 'FF-BD-201', 'name' => 'Resistance Band Set', 'brand' => 'FlexFit', 'category' => 'Accessories', 'supplier' => 'Gym Essentials Wholesale', 'quantity' => 18, 'reorder' => 8, 'price' => 24.99],
            ['sku' => 'FF-YM-202', 'name' => 'Non-Slip Training Mat', 'brand' => 'FlexFit', 'category' => 'Accessories', 'supplier' => 'Gym Essentials Wholesale', 'quantity' => 6, 'reorder' => 12, 'price' => 32.00],
            ['sku' => 'FF-SB-203', 'name' => 'Stainless Steel Shaker Bottle', 'brand' => 'FlexFit', 'category' => 'Accessories', 'supplier' => 'Gym Essentials Wholesale', 'quantity' => 30, 'reorder' => 10, 'price' => 18.00],
            ['sku' => 'FW-WP-301', 'name' => 'Whey Protein 2 lb', 'brand' => 'FuelWorks', 'category' => 'Supplements', 'supplier' => 'FuelWorks Nutrition', 'quantity' => 7, 'reorder' => 10, 'price' => 39.95],
            ['sku' => 'FW-CR-302', 'name' => 'Creatine Monohydrate 300 g', 'brand' => 'FuelWorks', 'category' => 'Supplements', 'supplier' => 'FuelWorks Nutrition', 'quantity' => 15, 'reorder' => 8, 'price' => 27.50],
            ['sku' => 'IP-SR-005', 'name' => 'Commercial Squat Rack', 'brand' => 'IronPeak', 'category' => 'Strength Equipment', 'supplier' => 'IronPeak Supply', 'quantity' => 2, 'reorder' => 3, 'price' => 1299.00],
            ['sku' => 'IP-PL-006', 'name' => 'Olympic Plate Set 100 kg', 'brand' => 'IronPeak', 'category' => 'Free Weights', 'supplier' => 'IronPeak Supply', 'quantity' => 8, 'reorder' => 4, 'price' => 349.00],
            ['sku' => 'CC-EL-104', 'name' => 'Elliptical Trainer', 'brand' => 'CardioCore', 'category' => 'Cardio Equipment', 'supplier' => 'CardioCore Distribution', 'quantity' => 0, 'reorder' => 2, 'price' => 1899.00],
            ['sku' => 'CC-SM-105', 'name' => 'Stair Climber', 'brand' => 'CardioCore', 'category' => 'Cardio Equipment', 'supplier' => 'CardioCore Distribution', 'quantity' => 3, 'reorder' => 2, 'price' => 2499.00],
            ['sku' => 'FF-TR-204', 'name' => 'Suspension Trainer Kit', 'brand' => 'FlexFit', 'category' => 'Accessories', 'supplier' => 'Gym Essentials Wholesale', 'quantity' => 4, 'reorder' => 6, 'price' => 89.00],
            ['sku' => 'FF-JR-205', 'name' => 'Speed Rope', 'brand' => 'FlexFit', 'category' => 'Accessories', 'supplier' => 'Gym Essentials Wholesale', 'quantity' => 20, 'reorder' => 10, 'price' => 14.50],
            ['sku' => 'FW-BP-303', 'name' => 'Plant Protein Blend 2 lb', 'brand' => 'FuelWorks', 'category' => 'Supplements', 'supplier' => 'FuelWorks Nutrition', 'quantity' => 1, 'reorder' => 5, 'price' => 34.95],
            ['sku' => 'PF-CB-401', 'name' => 'Cable Crossover Machine', 'brand' => 'PulseForge', 'category' => 'Strength Equipment', 'supplier' => 'IronPeak Supply', 'quantity' => 1, 'reorder' => 2, 'price' => 2799.00],
        ];

        foreach ($products as $data) {
            $product = Product::firstOrCreate(
                ['sku' => $data['sku']],
                [
                    'name' => $data['name'],
                    'brand_id' => $brands[$data['brand']]->id,
                    'category_id' => $categories[$data['category']]->id,
                    'supplier_id' => $suppliers[$data['supplier']]->id,
                    'quantity' => $data['quantity'],
                    'reorder_level' => $data['reorder'],
                    'unit_price' => $data['price'],
                ],
            );

            if ($product->wasRecentlyCreated && $product->quantity > 0) {
                Transaction::firstOrCreate(
                    ['reference' => 'OPENING-'.$product->sku],
                    [
                        'product_id' => $product->id,
                        'type' => 'in',
                        'quantity' => $product->quantity,
                        'unit_price' => $product->unit_price,
                        'notes' => 'Seeded opening inventory balance.',
                    ],
                );
            }
        }

        app(ReorderService::class)->checkAllReorderPoints();
    }
}

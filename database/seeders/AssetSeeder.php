<?php

namespace Database\Seeders;

use App\Models\AssetCategory;
use App\Models\AssetSetting;
use Illuminate\Database\Seeder;

class AssetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create default asset categories
        $categories = [
            [
                'name' => 'IT Equipment',
                'description' => 'Information Technology hardware and software assets',
                'children' => [
                    ['name' => 'Computers', 'description' => 'Desktop and laptop computers'],
                    ['name' => 'Servers', 'description' => 'Server hardware and equipment'],
                    ['name' => 'Network Equipment', 'description' => 'Routers, switches, and networking gear'],
                    ['name' => 'Printers', 'description' => 'Printing devices and scanners'],
                    ['name' => 'Software', 'description' => 'Software licenses and applications'],
                ],
            ],
            [
                'name' => 'Furniture',
                'description' => 'Office and institutional furniture',
                'children' => [
                    ['name' => 'Desks', 'description' => 'Office desks and workstations'],
                    ['name' => 'Chairs', 'description' => 'Office and classroom chairs'],
                    ['name' => 'Storage', 'description' => 'Cabinets, shelves, and storage units'],
                    ['name' => 'Tables', 'description' => 'Meeting and conference tables'],
                ],
            ],
            [
                'name' => 'Classroom Equipment',
                'description' => 'Educational and classroom equipment',
                'children' => [
                    ['name' => 'Projectors', 'description' => 'Display projectors and screens'],
                    ['name' => 'Audio Systems', 'description' => 'Sound systems and microphones'],
                    ['name' => 'Whiteboards', 'description' => 'Interactive and traditional whiteboards'],
                    ['name' => 'Laboratory Equipment', 'description' => 'Scientific and technical lab equipment'],
                ],
            ],
            [
                'name' => 'Vehicles',
                'description' => 'Transportation assets',
                'children' => [
                    ['name' => 'Cars', 'description' => 'Passenger vehicles'],
                    ['name' => 'Buses', 'description' => 'Student and staff transportation'],
                    ['name' => 'Maintenance Vehicles', 'description' => 'Maintenance and utility vehicles'],
                ],
            ],
            [
                'name' => 'Maintenance Equipment',
                'description' => 'Facilities maintenance and grounds equipment',
                'children' => [
                    ['name' => 'Tools', 'description' => 'Hand tools and power tools'],
                    ['name' => 'Lawn Equipment', 'description' => 'Mowers and landscaping equipment'],
                    ['name' => 'HVAC Equipment', 'description' => 'Heating, ventilation, and air conditioning'],
                ],
            ],
        ];

        foreach ($categories as $categoryData) {
            $parent = AssetCategory::create([
                'name' => $categoryData['name'],
                'description' => $categoryData['description'],
            ]);

            if (isset($categoryData['children'])) {
                foreach ($categoryData['children'] as $childData) {
                    AssetCategory::create([
                        'name' => $childData['name'],
                        'description' => $childData['description'],
                        'parent_id' => $parent->id,
                    ]);
                }
            }
        }

        // Create default asset settings
        // AssetSetting::create([
        //     'key' => 'asset_tag_prefix',
        //     'value' => 'COL-',
        //     'description' => 'Prefix used for auto-generated asset tags',
        // ]);

        AssetSetting::create([
            'key' => 'default_depreciation_rate',
            'value' => '0.15',
            'description' => 'Default annual depreciation rate (15%)',
        ]);

        AssetSetting::create([
            'key' => 'asset_insurance_required',
            'value' => 'true',
            'description' => 'Whether assets require insurance documentation',
        ]);

        AssetSetting::create([
            'key' => 'minimum_asset_value',
            'value' => '100.00',
            'description' => 'Minimum value for assets to be tracked in the system',
        ]);
    }
}

<?php

namespace Database\Seeders;

use App\Models\Balance;
use App\Models\Product;
use App\Models\Provider;
use App\Models\Tag;
use App\Models\User;
use App\Models\UserAsset;
use Illuminate\Database\Seeder;

class ShopSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🛒 Creando tienda energética española de Aragón...');

        // 1. Crear tags básicos
        $this->command->info('🏷️ Creando tags...');
        $energyTags = [
            'Solar' => ['type' => 'energy_source', 'color' => '#F59E0B'],
            'Eólica' => ['type' => 'energy_source', 'color' => '#3B82F6'],
            'Renovable' => ['type' => 'sustainability', 'color' => '#10B981'],
            'Premium' => ['type' => 'price_range', 'color' => '#8B5CF6'],
            'Zaragoza' => ['type' => 'region', 'color' => '#6B7280'],
            'Huesca' => ['type' => 'region', 'color' => '#6B7280'],
            'Teruel' => ['type' => 'region', 'color' => '#6B7280'],
            'Aragón' => ['type' => 'region', 'color' => '#6B7280'],
        ];

        $tags = [];
        foreach ($energyTags as $name => $attributes) {
            $tags[$name] = Tag::updateOrCreate(
                ['slug' => \Illuminate\Support\Str::slug($name)],
                array_merge([
                    'name' => $name,
                    'is_featured' => true,
                    'usage_count' => rand(5, 50),
                ], $attributes)
            );
        }

        // 2. Crear proveedores
        $this->command->info('🏭 Creando proveedores...');
        
        $spanishData = config('spanish_data');
        
        $solarProvider = Provider::updateOrCreate(
            ['name' => 'Cooperativa Solar de Zaragoza'],
            [
                'description' => 'Cooperativa especializada en energía solar fotovoltaica en la provincia de Zaragoza.',
                'type' => 'energy',
                'is_active' => true,
                'email' => 'info@cooperativasolarzaragoza.coop.es',
                'phone' => '+976 123 456',
                'address' => 'Calle Alfonso I, 25, Zaragoza',
                'rating' => 4.8,
                'total_reviews' => 245,
                'certifications' => ['renewable_energy'],
                'operating_regions' => ['Zaragoza'],
            ]
        );

        $eolicProvider = Provider::updateOrCreate(
            ['name' => 'Cooperativa Eólica de Huesca'],
            [
                'description' => 'Cooperativa especializada en energía eólica en la provincia de Huesca.',
                'type' => 'energy',
                'is_active' => true,
                'email' => 'info@cooperativaeolicahuesca.coop.es',
                'phone' => '+974 234 567',
                'address' => 'Calle del Coso Alto, 23, Huesca',
                'rating' => 4.6,
                'total_reviews' => 189,
                'certifications' => ['renewable_energy'],
                'operating_regions' => ['Huesca'],
            ]
        );

        $hydroProvider = Provider::updateOrCreate(
            ['name' => 'Cooperativa Hidroeléctrica de Teruel'],
            [
                'description' => 'Cooperativa especializada en energía hidroeléctrica en la provincia de Teruel.',
                'type' => 'energy',
                'is_active' => true,
                'email' => 'info@cooperativahidroelectrica.coop.es',
                'phone' => '+978 345 678',
                'address' => 'Calle de la Rúa, 18, Teruel',
                'rating' => 4.7,
                'total_reviews' => 156,
                'certifications' => ['renewable_energy'],
                'operating_regions' => ['Teruel'],
            ]
        );

        // 3. Crear productos
        $this->command->info('⚡ Creando productos...');

        $solarKwh = Product::updateOrCreate(
            ['slug' => 'energia-solar-premium-1000-kwh'],
            [
                'provider_id' => $solarProvider->id,
                'name' => 'Energía Solar Premium - 1000 kWh',
                'description' => 'Paquete de 1000 kWh de energía solar fotovoltaica de la Cooperativa Solar de Zaragoza.',
                'type' => 'energy_kwh',
                'base_purchase_price' => 120.00,
                'base_sale_price' => 150.00,
                'unit' => 'kWh',
                'is_active' => true,
                'renewable_percentage' => 100,
                'carbon_footprint' => 0.045,
                'geographical_zone' => 'Zaragoza',
            ]
        );

        $eolicKwh = Product::updateOrCreate(
            ['slug' => 'energia-eolica-verde-1000-kwh'],
            [
                'provider_id' => $eolicProvider->id,
                'name' => 'Energía Eólica Verde - 1000 kWh',
                'description' => 'Paquete de 1000 kWh de energía eólica de la Cooperativa Eólica de Huesca.',
                'type' => 'energy_kwh',
                'base_purchase_price' => 110.00,
                'base_sale_price' => 140.00,
                'unit' => 'kWh',
                'is_active' => true,
                'renewable_percentage' => 100,
                'carbon_footprint' => 0.038,
                'geographical_zone' => 'Huesca',
            ]
        );

        $hydroKwh = Product::updateOrCreate(
            ['slug' => 'energia-hidroelectrica-sostenible-1000-kwh'],
            [
                'provider_id' => $hydroProvider->id,
                'name' => 'Energía Hidroeléctrica Sostenible - 1000 kWh',
                'description' => 'Paquete de 1000 kWh de energía hidroeléctrica de la Cooperativa Hidroeléctrica de Teruel.',
                'type' => 'energy_kwh',
                'base_purchase_price' => 105.00,
                'base_sale_price' => 135.00,
                'unit' => 'kWh',
                'is_active' => true,
                'renewable_percentage' => 100,
                'carbon_footprint' => 0.042,
                'geographical_zone' => 'Teruel',
            ]
        );

        // 4. Asignar tags
        $solarKwh->tags()->sync([
            $tags['Solar']->id => [
                'relevance_score' => 10, 
                'is_primary' => true,
                'sort_order' => 1,
                'metadata' => null
            ],
            $tags['Renovable']->id => [
                'relevance_score' => 9,
                'is_primary' => false,
                'sort_order' => 2,
                'metadata' => null
            ],
            $tags['Zaragoza']->id => [
                'relevance_score' => 7,
                'is_primary' => false,
                'sort_order' => 3,
                'metadata' => null
            ],
        ]);

        $eolicKwh->tags()->sync([
            $tags['Eólica']->id => [
                'relevance_score' => 10, 
                'is_primary' => true,
                'sort_order' => 1,
                'metadata' => null
            ],
            $tags['Renovable']->id => [
                'relevance_score' => 9,
                'is_primary' => false,
                'sort_order' => 2,
                'metadata' => null
            ],
            $tags['Huesca']->id => [
                'relevance_score' => 7,
                'is_primary' => false,
                'sort_order' => 3,
                'metadata' => null
            ],
        ]);

        $hydroKwh->tags()->sync([
            $tags['Renovable']->id => [
                'relevance_score' => 10, 
                'is_primary' => true,
                'sort_order' => 1,
                'metadata' => null
            ],
            $tags['Teruel']->id => [
                'relevance_score' => 7,
                'is_primary' => false,
                'sort_order' => 2,
                'metadata' => null
            ],
        ]);

        // 5. Crear usuarios con balances
        $this->command->info('👥 Creando usuarios...');
        
        $users = User::all();
        if ($users->isEmpty()) {
            $users = User::factory(5)->aragon()->create();
        }

        foreach ($users as $user) {
            $balance = Balance::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'type' => 'energy_kwh',
                    'currency' => 'kWh'
                ],
                [
                    'amount' => rand(100, 1000),
                    'is_frozen' => false,
                    'daily_limit' => 1000,
                    'monthly_limit' => 10000,
                ]
            );
        }

        $this->command->info('✅ Tienda energética española de Aragón creada exitosamente');
        $this->command->info('🏭 Proveedores: ' . Provider::count());
        $this->command->info('⚡ Productos: ' . Product::count());
        $this->command->info('👥 Usuarios: ' . User::count());
        $this->command->info('💰 Balances: ' . Balance::count());
    }
}
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
        $this->command->info('🛒 Creando tienda energética...');

        // 1. Crear tags básicos
        $this->command->info('🏷️ Creando tags...');
        $energyTags = [
            'Solar' => ['type' => 'energy_source', 'color' => '#F59E0B'],
            'Eólica' => ['type' => 'energy_source', 'color' => '#3B82F6'],
            'Renovable' => ['type' => 'sustainability', 'color' => '#10B981'],
            'Premium' => ['type' => 'price_range', 'color' => '#8B5CF6'],
            'Madrid' => ['type' => 'region', 'color' => '#6B7280'],
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
        
        $solarProvider = Provider::create([
            'name' => 'Energía Solar Cooperativa',
            'description' => 'Cooperativa especializada en energía solar fotovoltaica.',
            'type' => 'energy',
            'is_active' => true,
            'email' => 'info@solarcoop.es',
            'phone' => '+34 912 345 678',
            'address' => 'Madrid',
            'rating' => 4.8,
            'total_reviews' => 245,
            'certifications' => ['renewable_energy'],
            'operating_regions' => ['Madrid'],
        ]);

        // 3. Crear productos
        $this->command->info('⚡ Creando productos...');

        $solarKwh = Product::create([
            'provider_id' => $solarProvider->id,
            'name' => 'Energía Solar Premium - 1000 kWh',
            'slug' => 'energia-solar-premium-1000-kwh',
            'description' => 'Paquete de 1000 kWh de energía solar fotovoltaica.',
            'type' => 'energy_kwh',
            'base_purchase_price' => 120.00,
            'base_sale_price' => 150.00,
            'unit' => 'kWh',
            'is_active' => true,
            'renewable_percentage' => 100,
            'carbon_footprint' => 0.045,
            'geographical_zone' => 'Madrid',
        ]);

        // 4. Asignar tags
        $solarKwh->tags()->attach([
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
            $tags['Madrid']->id => [
                'relevance_score' => 7,
                'is_primary' => false,
                'sort_order' => 3,
                'metadata' => null
            ],
        ]);

        // 5. Crear usuarios con balances
        $this->command->info('👥 Creando usuarios...');
        
        $users = User::factory(3)->create();

        foreach ($users as $user) {
            Balance::create([
                'user_id' => $user->id,
                'type' => 'wallet',
                'amount' => rand(100, 5000),
                'currency' => 'EUR',
            ]);

            UserAsset::factory()
                ->forUser($user)
                ->forProduct($solarKwh)
                ->active()
                ->create();
        }

        $this->command->info('✅ ¡Tienda energética creada!');
    }
}
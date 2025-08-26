<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Affiliate;
use App\Models\User;
use Carbon\Carbon;

class AffiliateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🤝 Creando afiliados españoles para la cooperativa energética...');

        // Obtener usuarios disponibles
        $users = User::all();

        if ($users->isEmpty()) {
            $this->command->error('❌ No hay usuarios disponibles. Ejecuta RolesAndAdminSeeder primero.');
            return;
        }

        // 1. Afiliados de Alto Rendimiento
        $this->createHighPerformanceAffiliates($users);

        // 2. Afiliados de Rendimiento Medio
        $this->createMediumPerformanceAffiliates($users);

        // 3. Afiliados Principiantes
        $this->createBeginnerAffiliates($users);

        // 4. Afiliados Especializados en Energía
        $this->createEnergySpecialistAffiliates($users);

        // 5. Afiliados Corporativos
        $this->createCorporateAffiliates($users);

        // 6. Afiliados de Tecnología
        $this->createTechnologyAffiliates($users);

        $this->command->info('✅ AffiliateSeeder completado. Se crearon ' . Affiliate::count() . ' afiliados españoles.');
    }

    /**
     * Crear afiliados de alto rendimiento
     */
    private function createHighPerformanceAffiliates($users): void
    {
        $this->command->info('🏆 Creando afiliados de alto rendimiento...');

        $highPerformanceAffiliates = [
            [
                'affiliate_code' => 'AF001',
                'status' => 'active',
                'tier' => 'diamond',
                'commission_rate' => 25.00,
                'total_earnings' => 15000.00,
                'pending_earnings' => 2500.00,
                'paid_earnings' => 12500.00,
                'total_referrals' => 45,
                'active_referrals' => 38,
                'converted_referrals' => 32,
                'conversion_rate' => 71.11,
                'joined_date' => Carbon::now()->subMonths(18),
                'last_activity_date' => Carbon::now()->subDays(2),
                'payment_instructions' => 'Transferencia bancaria preferida. Pago mensual.',
                'payment_methods' => ['bank_transfer', 'paypal', 'stripe'],
                'marketing_materials' => ['banners', 'brochures', 'videos', 'social_media'],
                'performance_metrics' => [
                    'monthly_growth' => 15.5,
                    'customer_satisfaction' => 4.8,
                    'response_time' => '2h',
                    'success_rate' => 89.5
                ],
                'notes' => 'Afiliado estrella con excelente historial de conversiones.',
            ],
            [
                'affiliate_code' => 'AF002',
                'status' => 'active',
                'tier' => 'platinum',
                'commission_rate' => 20.00,
                'total_earnings' => 12000.00,
                'pending_earnings' => 1800.00,
                'paid_earnings' => 10200.00,
                'total_referrals' => 38,
                'active_referrals' => 32,
                'converted_referrals' => 26,
                'conversion_rate' => 68.42,
                'joined_date' => Carbon::now()->subMonths(15),
                'last_activity_date' => Carbon::now()->subDays(5),
                'payment_instructions' => 'Pago quincenal por transferencia bancaria.',
                'payment_methods' => ['bank_transfer', 'paypal'],
                'marketing_materials' => ['banners', 'brochures', 'email_templates'],
                'performance_metrics' => [
                    'monthly_growth' => 12.3,
                    'customer_satisfaction' => 4.6,
                    'response_time' => '4h',
                    'success_rate' => 85.2
                ],
                'notes' => 'Alto rendimiento en el sector residencial.',
            ],
        ];

        foreach ($highPerformanceAffiliates as $affiliateData) {
            $user = $users->random();
            $this->createAffiliate($user, $affiliateData);
        }
    }

    /**
     * Crear afiliados de rendimiento medio
     */
    private function createMediumPerformanceAffiliates($users): void
    {
        $this->command->info('📊 Creando afiliados de rendimiento medio...');

        $mediumPerformanceAffiliates = [
            [
                'affiliate_code' => 'AF003',
                'status' => 'active',
                'tier' => 'gold',
                'commission_rate' => 15.00,
                'total_earnings' => 8000.00,
                'pending_earnings' => 1200.00,
                'paid_earnings' => 6800.00,
                'total_referrals' => 28,
                'active_referrals' => 22,
                'converted_referrals' => 18,
                'conversion_rate' => 64.29,
                'joined_date' => Carbon::now()->subMonths(12),
                'last_activity_date' => Carbon::now()->subDays(8),
                'payment_instructions' => 'Pago mensual por transferencia bancaria.',
                'payment_methods' => ['bank_transfer'],
                'marketing_materials' => ['banners', 'brochures'],
                'performance_metrics' => [
                    'monthly_growth' => 8.7,
                    'customer_satisfaction' => 4.3,
                    'response_time' => '6h',
                    'success_rate' => 78.9
                ],
                'notes' => 'Rendimiento estable, buen potencial de crecimiento.',
            ],
            [
                'affiliate_code' => 'AF004',
                'status' => 'active',
                'tier' => 'gold',
                'commission_rate' => 15.00,
                'total_earnings' => 7500.00,
                'pending_earnings' => 1100.00,
                'paid_earnings' => 6400.00,
                'total_referrals' => 25,
                'active_referrals' => 20,
                'converted_referrals' => 16,
                'conversion_rate' => 64.00,
                'joined_date' => Carbon::now()->subMonths(10),
                'last_activity_date' => Carbon::now()->subDays(12),
                'payment_instructions' => 'Pago mensual por PayPal.',
                'payment_methods' => ['paypal'],
                'marketing_materials' => ['banners', 'social_media'],
                'performance_metrics' => [
                    'monthly_growth' => 7.2,
                    'customer_satisfaction' => 4.1,
                    'response_time' => '8h',
                    'success_rate' => 76.5
                ],
                'notes' => 'Especializado en proyectos comerciales.',
            ],
            [
                'affiliate_code' => 'AF005',
                'status' => 'active',
                'tier' => 'silver',
                'commission_rate' => 12.00,
                'total_earnings' => 6000.00,
                'pending_earnings' => 900.00,
                'paid_earnings' => 5100.00,
                'total_referrals' => 22,
                'active_referrals' => 18,
                'converted_referrals' => 14,
                'conversion_rate' => 63.64,
                'joined_date' => Carbon::now()->subMonths(8),
                'last_activity_date' => Carbon::now()->subDays(15),
                'payment_instructions' => 'Pago mensual por transferencia bancaria.',
                'payment_methods' => ['bank_transfer'],
                'marketing_materials' => ['banners'],
                'performance_metrics' => [
                    'monthly_growth' => 6.8,
                    'customer_satisfaction' => 4.0,
                    'response_time' => '10h',
                    'success_rate' => 74.2
                ],
                'notes' => 'Enfocado en el mercado de Aragón.',
            ],
        ];

        foreach ($mediumPerformanceAffiliates as $affiliateData) {
            $user = $users->random();
            $this->createAffiliate($user, $affiliateData);
        }
    }

    /**
     * Crear afiliados principiantes
     */
    private function createBeginnerAffiliates($users): void
    {
        $this->command->info('🌱 Creando afiliados principiantes...');

        $beginnerAffiliates = [
            [
                'affiliate_code' => 'AF006',
                'status' => 'active',
                'tier' => 'bronze',
                'commission_rate' => 8.00,
                'total_earnings' => 2500.00,
                'pending_earnings' => 400.00,
                'paid_earnings' => 2100.00,
                'total_referrals' => 15,
                'active_referrals' => 12,
                'converted_referrals' => 8,
                'conversion_rate' => 53.33,
                'joined_date' => Carbon::now()->subMonths(6),
                'last_activity_date' => Carbon::now()->subDays(20),
                'payment_instructions' => 'Pago mensual por transferencia bancaria.',
                'payment_methods' => ['bank_transfer'],
                'marketing_materials' => ['banners'],
                'performance_metrics' => [
                    'monthly_growth' => 5.2,
                    'customer_satisfaction' => 3.8,
                    'response_time' => '12h',
                    'success_rate' => 68.5
                ],
                'notes' => 'Principiante prometedor, necesita más apoyo.',
            ],
            [
                'affiliate_code' => 'AF007',
                'status' => 'active',
                'tier' => 'bronze',
                'commission_rate' => 8.00,
                'total_earnings' => 1800.00,
                'pending_earnings' => 300.00,
                'paid_earnings' => 1500.00,
                'total_referrals' => 12,
                'active_referrals' => 10,
                'converted_referrals' => 6,
                'conversion_rate' => 50.00,
                'joined_date' => Carbon::now()->subMonths(4),
                'last_activity_date' => Carbon::now()->subDays(25),
                'payment_instructions' => 'Pago mensual por PayPal.',
                'payment_methods' => ['paypal'],
                'marketing_materials' => ['banners'],
                'performance_metrics' => [
                    'monthly_growth' => 4.8,
                    'customer_satisfaction' => 3.6,
                    'response_time' => '15h',
                    'success_rate' => 65.2
                ],
                'notes' => 'Nuevo en el programa, mostrando entusiasmo.',
            ],
            [
                'affiliate_code' => 'AF008',
                'status' => 'pending_approval',
                'tier' => 'bronze',
                'commission_rate' => 8.00,
                'total_earnings' => 0.00,
                'pending_earnings' => 0.00,
                'paid_earnings' => 0.00,
                'total_referrals' => 0,
                'active_referrals' => 0,
                'converted_referrals' => 0,
                'conversion_rate' => 0.00,
                'joined_date' => Carbon::now()->subDays(30),
                'last_activity_date' => Carbon::now()->subDays(30),
                'payment_instructions' => 'Pendiente de configuración.',
                'payment_methods' => [],
                'marketing_materials' => [],
                'performance_metrics' => [
                    'monthly_growth' => 0.0,
                    'customer_satisfaction' => 0.0,
                    'response_time' => 'N/A',
                    'success_rate' => 0.0
                ],
                'notes' => 'Solicitud pendiente de revisión.',
            ],
        ];

        foreach ($beginnerAffiliates as $affiliateData) {
            $user = $users->random();
            $this->createAffiliate($user, $affiliateData);
        }
    }

    /**
     * Crear afiliados especializados en energía
     */
    private function createEnergySpecialistAffiliates($users): void
    {
        $this->command->info('⚡ Creando afiliados especializados en energía...');

        $energySpecialistAffiliates = [
            [
                'affiliate_code' => 'AF009',
                'status' => 'active',
                'tier' => 'gold',
                'commission_rate' => 18.00,
                'total_earnings' => 9500.00,
                'pending_earnings' => 1400.00,
                'paid_earnings' => 8100.00,
                'total_referrals' => 32,
                'active_referrals' => 28,
                'converted_referrals' => 24,
                'conversion_rate' => 75.00,
                'joined_date' => Carbon::now()->subMonths(14),
                'last_activity_date' => Carbon::now()->subDays(3),
                'payment_instructions' => 'Pago mensual por transferencia bancaria.',
                'payment_methods' => ['bank_transfer', 'paypal'],
                'marketing_materials' => ['banners', 'brochures', 'videos', 'technical_docs'],
                'performance_metrics' => [
                    'monthly_growth' => 11.8,
                    'customer_satisfaction' => 4.7,
                    'response_time' => '3h',
                    'success_rate' => 87.3
                ],
                'notes' => 'Especialista en energía solar y eólica.',
            ],
            [
                'affiliate_code' => 'AF010',
                'status' => 'active',
                'tier' => 'silver',
                'commission_rate' => 13.00,
                'total_earnings' => 6800.00,
                'pending_earnings' => 1000.00,
                'paid_earnings' => 5800.00,
                'total_referrals' => 26,
                'active_referrals' => 22,
                'converted_referrals' => 18,
                'conversion_rate' => 69.23,
                'joined_date' => Carbon::now()->subMonths(9),
                'last_activity_date' => Carbon::now()->subDays(7),
                'payment_instructions' => 'Pago mensual por transferencia bancaria.',
                'payment_methods' => ['bank_transfer'],
                'marketing_materials' => ['banners', 'brochures', 'technical_specs'],
                'performance_metrics' => [
                    'monthly_growth' => 8.9,
                    'customer_satisfaction' => 4.4,
                    'response_time' => '5h',
                    'success_rate' => 81.6
                ],
                'notes' => 'Especialista en eficiencia energética.',
            ],
        ];

        foreach ($energySpecialistAffiliates as $affiliateData) {
            $user = $users->random();
            $this->createAffiliate($user, $affiliateData);
        }
    }

    /**
     * Crear afiliados corporativos
     */
    private function createCorporateAffiliates($users): void
    {
        $this->command->info('🏢 Creando afiliados corporativos...');

        $corporateAffiliates = [
            [
                'affiliate_code' => 'AF011',
                'status' => 'active',
                'tier' => 'platinum',
                'commission_rate' => 16.00,
                'total_earnings' => 11000.00,
                'pending_earnings' => 1700.00,
                'paid_earnings' => 9300.00,
                'total_referrals' => 35,
                'active_referrals' => 30,
                'converted_referrals' => 25,
                'conversion_rate' => 71.43,
                'joined_date' => Carbon::now()->subMonths(16),
                'last_activity_date' => Carbon::now()->subDays(4),
                'payment_instructions' => 'Pago mensual por transferencia bancaria corporativa.',
                'payment_methods' => ['bank_transfer'],
                'marketing_materials' => ['banners', 'brochures', 'corporate_presentations'],
                'performance_metrics' => [
                    'monthly_growth' => 13.2,
                    'customer_satisfaction' => 4.5,
                    'response_time' => '4h',
                    'success_rate' => 84.7
                ],
                'notes' => 'Empresa corporativa con amplia red de contactos.',
            ],
            [
                'affiliate_code' => 'AF012',
                'status' => 'active',
                'tier' => 'gold',
                'commission_rate' => 14.00,
                'total_earnings' => 8200.00,
                'pending_earnings' => 1200.00,
                'paid_earnings' => 7000.00,
                'total_referrals' => 29,
                'active_referrals' => 24,
                'converted_referrals' => 19,
                'conversion_rate' => 65.52,
                'joined_date' => Carbon::now()->subMonths(11),
                'last_activity_date' => Carbon::now()->subDays(10),
                'payment_instructions' => 'Pago mensual por transferencia bancaria.',
                'payment_methods' => ['bank_transfer'],
                'marketing_materials' => ['banners', 'corporate_brochures'],
                'performance_metrics' => [
                    'monthly_growth' => 9.1,
                    'customer_satisfaction' => 4.2,
                    'response_time' => '6h',
                    'success_rate' => 79.8
                ],
                'notes' => 'Empresa de consultoría energética.',
            ],
        ];

        foreach ($corporateAffiliates as $affiliateData) {
            $user = $users->random();
            $this->createAffiliate($user, $affiliateData);
        }
    }

    /**
     * Crear afiliados de tecnología
     */
    private function createTechnologyAffiliates($users): void
    {
        $this->command->info('💻 Creando afiliados de tecnología...');

        $technologyAffiliates = [
            [
                'affiliate_code' => 'AF013',
                'status' => 'active',
                'tier' => 'silver',
                'commission_rate' => 12.00,
                'total_earnings' => 5800.00,
                'pending_earnings' => 800.00,
                'paid_earnings' => 5000.00,
                'total_referrals' => 20,
                'active_referrals' => 16,
                'converted_referrals' => 13,
                'conversion_rate' => 65.00,
                'joined_date' => Carbon::now()->subMonths(7),
                'last_activity_date' => Carbon::now()->subDays(18),
                'payment_instructions' => 'Pago mensual por PayPal.',
                'payment_methods' => ['paypal'],
                'marketing_materials' => ['banners', 'digital_content', 'social_media'],
                'performance_metrics' => [
                    'monthly_growth' => 7.8,
                    'customer_satisfaction' => 4.1,
                    'response_time' => '8h',
                    'success_rate' => 76.3
                ],
                'notes' => 'Especialista en marketing digital y tecnología.',
            ],
            [
                'affiliate_code' => 'AF014',
                'status' => 'active',
                'tier' => 'bronze',
                'commission_rate' => 9.00,
                'total_earnings' => 3200.00,
                'pending_earnings' => 500.00,
                'paid_earnings' => 2700.00,
                'total_referrals' => 18,
                'active_referrals' => 14,
                'converted_referrals' => 10,
                'conversion_rate' => 55.56,
                'joined_date' => Carbon::now()->subMonths(5),
                'last_activity_date' => Carbon::now()->subDays(22),
                'payment_instructions' => 'Pago mensual por transferencia bancaria.',
                'payment_methods' => ['bank_transfer'],
                'marketing_materials' => ['banners', 'digital_content'],
                'performance_metrics' => [
                    'monthly_growth' => 6.2,
                    'customer_satisfaction' => 3.9,
                    'response_time' => '10h',
                    'success_rate' => 72.1
                ],
                'notes' => 'Startup tecnológica en crecimiento.',
            ],
        ];

        foreach ($technologyAffiliates as $affiliateData) {
            $user = $users->random();
            $this->createAffiliate($user, $affiliateData);
        }
    }

    /**
     * Crear un afiliado
     */
    private function createAffiliate($user, $affiliateData): void
    {
        // Verificar si ya existe un afiliado con ese código
        $existing = Affiliate::where('affiliate_code', $affiliateData['affiliate_code'])->exists();
        
        if ($existing) {
            return;
        }

        Affiliate::create(array_merge($affiliateData, [
            'user_id' => $user->id,
            'referred_by' => null, // Por ahora sin referidos
            'approved_by' => $affiliateData['status'] === 'active' ? $user->id : null,
            'approved_at' => $affiliateData['status'] === 'active' ? Carbon::now()->subDays(rand(1, 30)) : null,
        ]));
    }

    /**
     * Generar estadísticas de afiliados
     */
    private function generateAffiliateStats(): array
    {
        $total = Affiliate::count();
        $active = Affiliate::where('status', 'active')->count();
        $pending = Affiliate::where('status', 'pending_approval')->count();
        $totalEarnings = Affiliate::sum('total_earnings');
        $totalReferrals = Affiliate::sum('total_referrals');

        return [
            'total_affiliates' => $total,
            'active_affiliates' => $active,
            'pending_approval' => $pending,
            'total_earnings' => $totalEarnings,
            'total_referrals' => $totalReferrals,
        ];
    }
}

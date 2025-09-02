<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Contact;
use App\Models\User;
use App\Models\Organization;
use Carbon\Carbon;
use Illuminate\Support\Str;

class ContactSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('📞 Creando contactos para el sistema...');
        
        // Limpiar contactos existentes
        Contact::query()->delete();
        
        // Obtener usuarios y organizaciones disponibles
        $users = User::all();
        $organizations = Organization::all();
        
        if ($users->isEmpty()) {
            $this->command->warn('⚠️ No hay usuarios disponibles. No se pueden crear contactos.');
            return;
        }
        
        $this->command->info("👥 Usuarios disponibles: {$users->count()}");
        $this->command->info("🏢 Organizaciones disponibles: {$organizations->count()}");
        
        // Crear contactos para diferentes tipos y escenarios
        $this->createMainContacts($users, $organizations);
        $this->createSupportContacts($users, $organizations);
        $this->createSalesContacts($users, $organizations);
        $this->createTechnicalContacts($users, $organizations);
        $this->createBillingContacts($users, $organizations);
        $this->createMediaContacts($users, $organizations);
        $this->createEmergencyContacts($users, $organizations);
        
        $this->command->info('✅ ContactSeeder completado. Se crearon ' . Contact::count() . ' contactos.');
    }
    
    private function createMainContacts($users, $organizations): void
    {
        $this->command->info('🏢 Creando contactos principales...');
        
        $mainContactData = [
            [
                'email' => 'info@cooperativa-energetica.es',
                'phone' => '+34 91 123 45 67',
                'address' => 'Calle Mayor, 123, 28001 Madrid, España',
                'latitude' => 40.4168,
                'longitude' => -3.7038,
                'contact_type' => 'main',
                'is_primary' => true,
                'is_draft' => false,
            ],
            [
                'email' => 'atencion@cooperativa-energetica.es',
                'phone' => '+34 91 123 45 68',
                'address' => 'Calle Mayor, 123, 28001 Madrid, España',
                'latitude' => 40.4168,
                'longitude' => -3.7038,
                'contact_type' => 'main',
                'is_primary' => false,
                'is_draft' => false,
            ],
        ];
        
        foreach ($mainContactData as $contactData) {
            $organization = $organizations->random();
            $user = $users->random();
            
            Contact::create(array_merge($contactData, [
                'icon_address' => 'heroicon-o-map-pin',
                'icon_phone' => 'heroicon-o-phone',
                'icon_email' => 'heroicon-o-envelope',
                'business_hours' => [
                    'Lunes' => '9:00-18:00',
                    'Martes' => '9:00-18:00',
                    'Miércoles' => '9:00-18:00',
                    'Jueves' => '9:00-18:00',
                    'Viernes' => '9:00-18:00',
                    'Sábado' => '9:00-14:00',
                    'Domingo' => 'Cerrado'
                ],
                'additional_info' => 'Contacto principal para consultas generales y atención al cliente.',
                'organization_id' => $organization->id,
                'created_by_user_id' => $user->id,
                'created_at' => Carbon::now()->subDays(rand(0, 30)),
                'updated_at' => Carbon::now()->subDays(rand(0, 30))
            ]));
        }
        
        $this->command->info("🏢 Contactos principales creados");
    }
    
    private function createSupportContacts($users, $organizations): void
    {
        $this->command->info('🆘 Creando contactos de soporte...');
        
        $supportContactData = [
            [
                'email' => 'soporte@cooperativa-energetica.es',
                'phone' => '+34 91 123 45 69',
                'address' => 'Calle Mayor, 123, 28001 Madrid, España',
                'latitude' => 40.4168,
                'longitude' => -3.7038,
                'contact_type' => 'support',
                'is_primary' => false,
                'is_draft' => false,
            ],
            [
                'email' => 'ayuda@cooperativa-energetica.es',
                'phone' => '+34 91 123 45 70',
                'address' => 'Calle Mayor, 123, 28001 Madrid, España',
                'latitude' => 40.4168,
                'longitude' => -3.7038,
                'contact_type' => 'support',
                'is_primary' => false,
                'is_draft' => false,
            ],
        ];
        
        foreach ($supportContactData as $contactData) {
            $organization = $organizations->random();
            $user = $users->random();
            
            Contact::create(array_merge($contactData, [
                'icon_address' => 'heroicon-o-map-pin',
                'icon_phone' => 'heroicon-o-phone',
                'icon_email' => 'heroicon-o-envelope',
                'business_hours' => [
                    'Lunes' => '8:00-20:00',
                    'Martes' => '8:00-20:00',
                    'Miércoles' => '8:00-20:00',
                    'Jueves' => '8:00-20:00',
                    'Viernes' => '8:00-20:00',
                    'Sábado' => '9:00-18:00',
                    'Domingo' => '10:00-16:00'
                ],
                'additional_info' => 'Soporte técnico disponible 24/7 para emergencias.',
                'organization_id' => $organization->id,
                'created_by_user_id' => $user->id,
                'created_at' => Carbon::now()->subDays(rand(0, 30)),
                'updated_at' => Carbon::now()->subDays(rand(0, 30))
            ]));
        }
        
        $this->command->info("🆘 Contactos de soporte creados");
    }
    
    private function createSalesContacts($users, $organizations): void
    {
        $this->command->info('💰 Creando contactos de ventas...');
        
        $salesContactData = [
            [
                'email' => 'ventas@cooperativa-energetica.es',
                'phone' => '+34 91 123 45 71',
                'address' => 'Calle Mayor, 123, 28001 Madrid, España',
                'latitude' => 40.4168,
                'longitude' => -3.7038,
                'contact_type' => 'sales',
                'is_primary' => false,
                'is_draft' => false,
            ],
            [
                'email' => 'nuevos@cooperativa-energetica.es',
                'phone' => '+34 91 123 45 72',
                'address' => 'Calle Mayor, 123, 28001 Madrid, España',
                'latitude' => 40.4168,
                'longitude' => -3.7038,
                'contact_type' => 'sales',
                'is_primary' => false,
                'is_draft' => false,
            ],
        ];
        
        foreach ($salesContactData as $contactData) {
            $organization = $organizations->random();
            $user = $users->random();
            
            Contact::create(array_merge($contactData, [
                'icon_address' => 'heroicon-o-map-pin',
                'icon_phone' => 'heroicon-o-phone',
                'icon_email' => 'heroicon-o-envelope',
                'business_hours' => [
                    'Lunes' => '9:00-19:00',
                    'Martes' => '9:00-19:00',
                    'Miércoles' => '9:00-19:00',
                    'Jueves' => '9:00-19:00',
                    'Viernes' => '9:00-19:00',
                    'Sábado' => '10:00-16:00',
                    'Domingo' => 'Cerrado'
                ],
                'additional_info' => 'Información sobre planes y tarifas para nuevos clientes.',
                'organization_id' => $organization->id,
                'created_by_user_id' => $user->id,
                'created_at' => Carbon::now()->subDays(rand(0, 30)),
                'updated_at' => Carbon::now()->subDays(rand(0, 30))
            ]));
        }
        
        $this->command->info("💰 Contactos de ventas creados");
    }
    
    private function createTechnicalContacts($users, $organizations): void
    {
        $this->command->info('🔧 Creando contactos técnicos...');
        
        $technicalContactData = [
            [
                'email' => 'tecnico@cooperativa-energetica.es',
                'phone' => '+34 91 123 45 73',
                'address' => 'Calle Mayor, 123, 28001 Madrid, España',
                'latitude' => 40.4168,
                'longitude' => -3.7038,
                'contact_type' => 'technical',
                'is_primary' => false,
                'is_draft' => false,
            ],
            [
                'email' => 'mantenimiento@cooperativa-energetica.es',
                'phone' => '+34 91 123 45 74',
                'address' => 'Calle Mayor, 123, 28001 Madrid, España',
                'latitude' => 40.4168,
                'longitude' => -3.7038,
                'contact_type' => 'technical',
                'is_primary' => false,
                'is_draft' => false,
            ],
        ];
        
        foreach ($technicalContactData as $contactData) {
            $organization = $organizations->random();
            $user = $users->random();
            
            Contact::create(array_merge($contactData, [
                'icon_address' => 'heroicon-o-map-pin',
                'icon_phone' => 'heroicon-o-phone',
                'icon_email' => 'heroicon-o-envelope',
                'business_hours' => [
                    'Lunes' => '7:00-17:00',
                    'Martes' => '7:00-17:00',
                    'Miércoles' => '7:00-17:00',
                    'Jueves' => '7:00-17:00',
                    'Viernes' => '7:00-17:00',
                    'Sábado' => '8:00-14:00',
                    'Domingo' => 'Cerrado'
                ],
                'additional_info' => 'Equipo técnico especializado en instalaciones y mantenimiento.',
                'organization_id' => $organization->id,
                'created_by_user_id' => $user->id,
                'created_at' => Carbon::now()->subDays(rand(0, 30)),
                'updated_at' => Carbon::now()->subDays(rand(0, 30))
            ]));
        }
        
        $this->command->info("🔧 Contactos técnicos creados");
    }
    
    private function createBillingContacts($users, $organizations): void
    {
        $this->command->info('📊 Creando contactos de facturación...');
        
        $billingContactData = [
            [
                'email' => 'facturacion@cooperativa-energetica.es',
                'phone' => '+34 91 123 45 75',
                'address' => 'Calle Mayor, 123, 28001 Madrid, España',
                'latitude' => 40.4168,
                'longitude' => -3.7038,
                'contact_type' => 'billing',
                'is_primary' => false,
                'is_draft' => false,
            ],
            [
                'email' => 'cobros@cooperativa-energetica.es',
                'phone' => '+34 91 123 45 76',
                'address' => 'Calle Mayor, 123, 28001 Madrid, España',
                'latitude' => 40.4168,
                'longitude' => -3.7038,
                'contact_type' => 'billing',
                'is_primary' => false,
                'is_draft' => false,
            ],
        ];
        
        foreach ($billingContactData as $contactData) {
            $organization = $organizations->random();
            $user = $users->random();
            
            Contact::create(array_merge($contactData, [
                'icon_address' => 'heroicon-o-map-pin',
                'icon_phone' => 'heroicon-o-phone',
                'icon_email' => 'heroicon-o-envelope',
                'business_hours' => [
                    'Lunes' => '9:00-17:00',
                    'Martes' => '9:00-17:00',
                    'Miércoles' => '9:00-17:00',
                    'Jueves' => '9:00-17:00',
                    'Viernes' => '9:00-17:00',
                    'Sábado' => 'Cerrado',
                    'Domingo' => 'Cerrado'
                ],
                'additional_info' => 'Atención especializada en temas de facturación y pagos.',
                'organization_id' => $organization->id,
                'created_by_user_id' => $user->id,
                'created_at' => Carbon::now()->subDays(rand(0, 30)),
                'updated_at' => Carbon::now()->subDays(rand(0, 30))
            ]));
        }
        
        $this->command->info("📊 Contactos de facturación creados");
    }
    
    private function createMediaContacts($users, $organizations): void
    {
        $this->command->info('📰 Creando contactos de medios...');
        
        $mediaContactData = [
            [
                'email' => 'prensa@cooperativa-energetica.es',
                'phone' => '+34 91 123 45 77',
                'address' => 'Calle Mayor, 123, 28001 Madrid, España',
                'latitude' => 40.4168,
                'longitude' => -3.7038,
                'contact_type' => 'media',
                'is_primary' => false,
                'is_draft' => false,
            ],
            [
                'email' => 'marketing@cooperativa-energetica.es',
                'phone' => '+34 91 123 45 78',
                'address' => 'Calle Mayor, 123, 28001 Madrid, España',
                'latitude' => 40.4168,
                'longitude' => -3.7038,
                'contact_type' => 'media',
                'is_primary' => false,
                'is_draft' => false,
            ],
        ];
        
        foreach ($mediaContactData as $contactData) {
            $organization = $organizations->random();
            $user = $users->random();
            
            Contact::create(array_merge($contactData, [
                'icon_address' => 'heroicon-o-map-pin',
                'icon_phone' => 'heroicon-o-phone',
                'icon_email' => 'heroicon-o-envelope',
                'business_hours' => [
                    'Lunes' => '9:00-18:00',
                    'Martes' => '9:00-18:00',
                    'Miércoles' => '9:00-18:00',
                    'Jueves' => '9:00-18:00',
                    'Viernes' => '9:00-18:00',
                    'Sábado' => 'Cerrado',
                    'Domingo' => 'Cerrado'
                ],
                'additional_info' => 'Contacto para medios de comunicación y marketing.',
                'organization_id' => $organization->id,
                'created_by_user_id' => $user->id,
                'created_at' => Carbon::now()->subDays(rand(0, 30)),
                'updated_at' => Carbon::now()->subDays(rand(0, 30))
            ]));
        }
        
        $this->command->info("📰 Contactos de medios creados");
    }
    
    private function createEmergencyContacts($users, $organizations): void
    {
        $this->command->info('🚨 Creando contactos de emergencia...');
        
        $emergencyContactData = [
            [
                'email' => 'emergencias@cooperativa-energetica.es',
                'phone' => '+34 91 123 45 79',
                'address' => 'Calle Mayor, 123, 28001 Madrid, España',
                'latitude' => 40.4168,
                'longitude' => -3.7038,
                'contact_type' => 'emergency',
                'is_primary' => false,
                'is_draft' => false,
            ],
            [
                'email' => 'incidencias@cooperativa-energetica.es',
                'phone' => '+34 91 123 45 80',
                'address' => 'Calle Mayor, 123, 28001 Madrid, España',
                'latitude' => 40.4168,
                'longitude' => -3.7038,
                'contact_type' => 'emergency',
                'is_primary' => false,
                'is_draft' => false,
            ],
        ];
        
        foreach ($emergencyContactData as $contactData) {
            $organization = $organizations->random();
            $user = $users->random();
            
            Contact::create(array_merge($contactData, [
                'icon_address' => 'heroicon-o-map-pin',
                'icon_phone' => 'heroicon-o-phone',
                'icon_email' => 'heroicon-o-envelope',
                'business_hours' => [
                    'Lunes' => '24 horas',
                    'Martes' => '24 horas',
                    'Miércoles' => '24 horas',
                    'Jueves' => '24 horas',
                    'Viernes' => '24 horas',
                    'Sábado' => '24 horas',
                    'Domingo' => '24 horas'
                ],
                'additional_info' => 'Línea de emergencia disponible 24/7 para incidentes críticos.',
                'organization_id' => $organization->id,
                'created_by_user_id' => $user->id,
                'created_at' => Carbon::now()->subDays(rand(0, 30)),
                'updated_at' => Carbon::now()->subDays(rand(0, 30))
            ]));
        }
        
        $this->command->info("🚨 Contactos de emergencia creados");
    }
}


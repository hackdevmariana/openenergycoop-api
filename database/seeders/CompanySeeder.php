<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Company;
use Carbon\Carbon;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🏢 Creando empresas españolas para la cooperativa energética...');

        // 1. Empresas de Energía Renovable
        $this->createRenewableEnergyCompanies();

        // 2. Empresas de Servicios Públicos
        $this->createUtilityCompanies();

        // 3. Cooperativas Energéticas
        $this->createEnergyCooperatives();

        // 4. Empresas de Tecnología Energética
        $this->createEnergyTechCompanies();

        // 5. Empresas de Instalación y Mantenimiento
        $this->createInstallationCompanies();

        // 6. Empresas de Consultoría Energética
        $this->createConsultingCompanies();

        // 7. Empresas de Distribución
        $this->createDistributionCompanies();

        // 8. Empresas de Almacenamiento Energético
        $this->createStorageCompanies();

        // 9. Empresas de Eficiencia Energética
        $this->createEfficiencyCompanies();

        // 10. Empresas de Investigación y Desarrollo
        $this->createResearchCompanies();

        $this->command->info('✅ CompanySeeder completado. Se crearon ' . Company::count() . ' empresas españolas.');
    }

    /**
     * Crear empresas de energía renovable
     */
    private function createRenewableEnergyCompanies(): void
    {
        $this->command->info('☀️ Creando empresas de energía renovable...');

        $renewableCompanies = [
            [
                'name' => 'Solar Energía Aragón S.L.',
                'cif' => 'B12345678',
                'contact_person' => 'María del Carmen García López',
                'company_address' => 'Calle del Sol, 15, 50001 Zaragoza, Aragón',
            ],
            [
                'name' => 'Eólica del Ebro S.A.',
                'cif' => 'B23456789',
                'contact_person' => 'Carlos Rodríguez Martín',
                'company_address' => 'Avenida del Viento, 45, 22001 Huesca, Aragón',
            ],
            [
                'name' => 'Hidroeléctrica Teruel S.Coop.',
                'cif' => 'B34567890',
                'contact_person' => 'Ana Martínez Fernández',
                'company_address' => 'Plaza de la Energía, 8, 44001 Teruel, Aragón',
            ],
            [
                'name' => 'Biomasa Rural S.L.',
                'cif' => 'B45678901',
                'contact_person' => 'Luis Pérez González',
                'company_address' => 'Camino Rural, 23, 50002 Zaragoza, Aragón',
            ],
            [
                'name' => 'Geotermia Ibérica S.A.',
                'cif' => 'B56789012',
                'contact_person' => 'Sofia Jiménez Ruiz',
                'company_address' => 'Calle Térmica, 67, 22002 Huesca, Aragón',
            ],
        ];

        foreach ($renewableCompanies as $company) {
            $this->createCompany($company);
        }

        // Crear empresas adicionales usando el factory
        Company::factory()
            ->renewable()
            ->count(8)
            ->create()
            ->each(function ($company) {
                $this->command->line("   ✅ Creada empresa renovable: {$company->name}");
            });
    }

    /**
     * Crear empresas de servicios públicos
     */
    private function createUtilityCompanies(): void
    {
        $this->command->info('⚡ Creando empresas de servicios públicos...');

        $utilityCompanies = [
            [
                'name' => 'Endesa Distribución S.A.',
                'cif' => 'B67890123',
                'contact_person' => 'Pedro Sánchez Moreno',
                'company_address' => 'Paseo de la Independencia, 123, 50003 Zaragoza, Aragón',
            ],
            [
                'name' => 'Iberdrola Aragón S.L.',
                'cif' => 'B78901234',
                'contact_person' => 'Carmen López Vega',
                'company_address' => 'Gran Vía, 89, 22003 Huesca, Aragón',
            ],
            [
                'name' => 'Gas Natural Aragón S.A.',
                'cif' => 'B89012345',
                'contact_person' => 'Roberto Fernández Castro',
                'company_address' => 'Calle del Gas, 12, 44002 Teruel, Aragón',
            ],
            [
                'name' => 'Agua del Ebro S.Coop.',
                'cif' => 'B90123456',
                'contact_person' => 'Isabel Torres Ruiz',
                'company_address' => 'Avenida del Agua, 34, 50004 Zaragoza, Aragón',
            ],
        ];

        foreach ($utilityCompanies as $company) {
            $this->createCompany($company);
        }

        // Crear empresas adicionales usando el factory
        Company::factory()
            ->utility()
            ->count(6)
            ->create()
            ->each(function ($company) {
                $this->command->line("   ✅ Creada empresa de servicios: {$company->name}");
            });
    }

    /**
     * Crear cooperativas energéticas
     */
    private function createEnergyCooperatives(): void
    {
        $this->command->info('🤝 Creando cooperativas energéticas...');

        $cooperativeCompanies = [
            [
                'name' => 'Cooperativa Energética del Ebro S.Coop.',
                'cif' => 'B01234567',
                'contact_person' => 'David Moreno Jiménez',
                'company_address' => 'Plaza de la Cooperación, 7, 50005 Zaragoza, Aragón',
            ],
            [
                'name' => 'Cooperativa Solar de las Tres Provincias S.Coop.',
                'cif' => 'B12345670',
                'contact_person' => 'Laura Vega Castro',
                'company_address' => 'Calle de la Solidaridad, 56, 22004 Huesca, Aragón',
            ],
            [
                'name' => 'Cooperativa Eólica Comunitaria S.Coop.',
                'cif' => 'B23456701',
                'contact_person' => 'Miguel Ángel Ruiz Díaz',
                'company_address' => 'Avenida Comunitaria, 78, 44003 Teruel, Aragón',
            ],
            [
                'name' => 'Cooperativa de Autoconsumo Solar S.Coop.',
                'cif' => 'B34567012',
                'contact_person' => 'Elena Sánchez Martín',
                'company_address' => 'Calle del Autoconsumo, 90, 50006 Zaragoza, Aragón',
            ],
        ];

        foreach ($cooperativeCompanies as $company) {
            $this->createCompany($company);
        }

        // Crear cooperativas adicionales usando el factory
        Company::factory()
            ->cooperative()
            ->count(5)
            ->create()
            ->each(function ($company) {
                $this->command->line("   ✅ Creada cooperativa: {$company->name}");
            });
    }

    /**
     * Crear empresas de tecnología energética
     */
    private function createEnergyTechCompanies(): void
    {
        $this->command->info('💻 Creando empresas de tecnología energética...');

        $techCompanies = [
            [
                'name' => 'Smart Grid Solutions S.L.',
                'cif' => 'B45670123',
                'contact_person' => 'Francisco Javier López Martínez',
                'company_address' => 'Parque Tecnológico, Edificio A, 50007 Zaragoza, Aragón',
            ],
            [
                'name' => 'IoT Energy Systems S.A.',
                'cif' => 'B56701234',
                'contact_person' => 'María José García Pérez',
                'company_address' => 'Centro de Innovación, Planta 3, 22005 Huesca, Aragón',
            ],
            [
                'name' => 'Digital Energy Platform S.L.',
                'cif' => 'B67012345',
                'contact_person' => 'Antonio Rodríguez Sánchez',
                'company_address' => 'Zona Industrial, Nave 12, 44004 Teruel, Aragón',
            ],
            [
                'name' => 'AI Energy Analytics S.A.',
                'cif' => 'B70123456',
                'contact_person' => 'Cristina Martín López',
                'company_address' => 'Campus Universitario, Edificio C, 50008 Zaragoza, Aragón',
            ],
        ];

        foreach ($techCompanies as $company) {
            $this->createCompany($company);
        }

        // Crear empresas adicionales usando el factory
        Company::factory()
            ->count(7)
            ->create()
            ->each(function ($company) {
                $this->command->line("   ✅ Creada empresa tecnológica: {$company->name}");
            });
    }

    /**
     * Crear empresas de instalación y mantenimiento
     */
    private function createInstallationCompanies(): void
    {
        $this->command->info('🔧 Creando empresas de instalación y mantenimiento...');

        $installationCompanies = [
            [
                'name' => 'Instalaciones Solares Aragón S.L.',
                'cif' => 'B81234567',
                'contact_person' => 'Javier Martínez García',
                'company_address' => 'Polígono Industrial, Calle 15, 50009 Zaragoza, Aragón',
            ],
            [
                'name' => 'Mantenimiento Eólico del Norte S.A.',
                'cif' => 'B92345678',
                'contact_person' => 'Rosa María Pérez López',
                'company_address' => 'Zona Eólica, Nave 8, 22006 Huesca, Aragón',
            ],
            [
                'name' => 'Servicios Técnicos Energéticos S.Coop.',
                'cif' => 'B03456789',
                'contact_person' => 'Alberto Sánchez Rodríguez',
                'company_address' => 'Centro de Servicios, 45, 44005 Teruel, Aragón',
            ],
            [
                'name' => 'Instalaciones Eléctricas Avanzadas S.L.',
                'cif' => 'B14567890',
                'contact_person' => 'Nuria García Martín',
                'company_address' => 'Parque Empresarial, Edificio D, 50010 Zaragoza, Aragón',
            ],
        ];

        foreach ($installationCompanies as $company) {
            $this->createCompany($company);
        }

        // Crear empresas adicionales usando el factory
        Company::factory()
            ->count(6)
            ->create()
            ->each(function ($company) {
                $this->command->line("   ✅ Creada empresa de instalación: {$company->name}");
            });
    }

    /**
     * Crear empresas de consultoría energética
     */
    private function createConsultingCompanies(): void
    {
        $this->command->info('📊 Creando empresas de consultoría energética...');

        $consultingCompanies = [
            [
                'name' => 'Consultoría Energética Aragón S.L.',
                'cif' => 'B25678901',
                'contact_person' => 'Carmen Elena Torres Sánchez',
                'company_address' => 'Oficinas Centrales, Piso 5, 50011 Zaragoza, Aragón',
            ],
            [
                'name' => 'Asesoría Energética del Ebro S.A.',
                'cif' => 'B36789012',
                'contact_person' => 'Manuel José Ruiz García',
                'company_address' => 'Centro Comercial, Local 23, 22007 Huesca, Aragón',
            ],
            [
                'name' => 'Estudios Energéticos Ibéricos S.L.',
                'cif' => 'B47890123',
                'contact_person' => 'Isabel María López Pérez',
                'company_address' => 'Plaza Mayor, 12, 44006 Teruel, Aragón',
            ],
            [
                'name' => 'Auditoría Energética Integral S.A.',
                'cif' => 'B58901234',
                'contact_person' => 'Carlos Alberto Martín Torres',
                'company_address' => 'Avenida de la Energía, 89, 50012 Zaragoza, Aragón',
            ],
        ];

        foreach ($consultingCompanies as $company) {
            $this->createCompany($company);
        }

        // Crear empresas adicionales usando el factory
        Company::factory()
            ->count(5)
            ->create()
            ->each(function ($company) {
                $this->command->line("   ✅ Creada empresa de consultoría: {$company->name}");
            });
    }

    /**
     * Crear empresas de distribución
     */
    private function createDistributionCompanies(): void
    {
        $this->command->info('🚚 Creando empresas de distribución energética...');

        $distributionCompanies = [
            [
                'name' => 'Distribución Energética Aragón S.L.',
                'cif' => 'B69012345',
                'contact_person' => 'Ana Belén García López',
                'company_address' => 'Centro Logístico, Nave 15, 50013 Zaragoza, Aragón',
            ],
            [
                'name' => 'Transporte Energético del Norte S.A.',
                'cif' => 'B70123456',
                'contact_person' => 'Luis Miguel Pérez Martín',
                'company_address' => 'Zona de Transporte, Calle 8, 22008 Huesca, Aragón',
            ],
            [
                'name' => 'Logística Energética Ibérica S.Coop.',
                'cif' => 'B81234567',
                'contact_person' => 'María del Pilar Sánchez García',
                'company_address' => 'Polígono Logístico, Nave 3, 44007 Teruel, Aragón',
            ],
        ];

        foreach ($distributionCompanies as $company) {
            $this->createCompany($company);
        }

        // Crear empresas adicionales usando el factory
        Company::factory()
            ->count(4)
            ->create()
            ->each(function ($company) {
                $this->command->line("   ✅ Creada empresa de distribución: {$company->name}");
            });
    }

    /**
     * Crear empresas de almacenamiento energético
     */
    private function createStorageCompanies(): void
    {
        $this->command->info('🔋 Creando empresas de almacenamiento energético...');

        $storageCompanies = [
            [
                'name' => 'Almacenamiento Energético Aragón S.L.',
                'cif' => 'B92345678',
                'contact_person' => 'Diego José Martínez Pérez',
                'company_address' => 'Centro de Almacenamiento, Nave 7, 50014 Zaragoza, Aragón',
            ],
            [
                'name' => 'Baterías del Ebro S.A.',
                'cif' => 'B03456789',
                'contact_person' => 'Elena María Torres López',
                'company_address' => 'Parque Tecnológico, Edificio B, 22009 Huesca, Aragón',
            ],
            [
                'name' => 'Sistemas de Almacenamiento Verde S.Coop.',
                'cif' => 'B14567890',
                'contact_person' => 'Francisco Javier Ruiz Sánchez',
                'company_address' => 'Zona Industrial Verde, Nave 5, 44008 Teruel, Aragón',
            ],
        ];

        foreach ($storageCompanies as $company) {
            $this->createCompany($company);
        }

        // Crear empresas adicionales usando el factory
        Company::factory()
            ->count(4)
            ->create()
            ->each(function ($company) {
                $this->command->line("   ✅ Creada empresa de almacenamiento: {$company->name}");
            });
    }

    /**
     * Crear empresas de eficiencia energética
     */
    private function createEfficiencyCompanies(): void
    {
        $this->command->info('💡 Creando empresas de eficiencia energética...');

        $efficiencyCompanies = [
            [
                'name' => 'Eficiencia Energética Aragón S.L.',
                'cif' => 'B25678901',
                'contact_person' => 'Sara María García Torres',
                'company_address' => 'Centro de Eficiencia, Piso 3, 50015 Zaragoza, Aragón',
            ],
            [
                'name' => 'Optimización Energética del Norte S.A.',
                'cif' => 'B36789012',
                'contact_person' => 'Pedro Manuel López Ruiz',
                'company_address' => 'Oficinas de Optimización, Local 12, 22010 Huesca, Aragón',
            ],
            [
                'name' => 'Gestión Energética Inteligente S.Coop.',
                'cif' => 'B47890123',
                'contact_person' => 'Carmen Rosa Martín García',
                'company_address' => 'Centro de Gestión, Edificio E, 44009 Teruel, Aragón',
            ],
        ];

        foreach ($efficiencyCompanies as $company) {
            $this->createCompany($company);
        }

        // Crear empresas adicionales usando el factory
        Company::factory()
            ->count(4)
            ->create()
            ->each(function ($company) {
                $this->command->line("   ✅ Creada empresa de eficiencia: {$company->name}");
            });
    }

    /**
     * Crear empresas de investigación y desarrollo
     */
    private function createResearchCompanies(): void
    {
        $this->command->info('🔬 Creando empresas de investigación y desarrollo...');

        $researchCompanies = [
            [
                'name' => 'Investigación Energética Aragón S.L.',
                'cif' => 'B58901234',
                'contact_person' => 'Dr. Miguel Ángel Sánchez López',
                'company_address' => 'Centro de Investigación, Laboratorio A, 50016 Zaragoza, Aragón',
            ],
            [
                'name' => 'Desarrollo Tecnológico Energético S.A.',
                'cif' => 'B69012345',
                'contact_person' => 'Dra. Ana Isabel García Martínez',
                'company_address' => 'Parque Científico, Edificio F, 22011 Huesca, Aragón',
            ],
            [
                'name' => 'Innovación Energética Ibérica S.Coop.',
                'cif' => 'B70123456',
                'contact_person' => 'Dr. Carlos Manuel Ruiz Torres',
                'company_address' => 'Centro de Innovación, Laboratorio B, 44010 Teruel, Aragón',
            ],
        ];

        foreach ($researchCompanies as $company) {
            $this->createCompany($company);
        }

        // Crear empresas adicionales usando el factory
        Company::factory()
            ->count(3)
            ->create()
            ->each(function ($company) {
                $this->command->line("   ✅ Creada empresa de investigación: {$company->name}");
            });
    }

    /**
     * Crear una empresa individual
     */
    private function createCompany(array $data): void
    {
        $company = Company::create($data);
        $this->command->line("   ✅ Creada empresa: {$company->name} (CIF: {$company->cif})");
    }
}

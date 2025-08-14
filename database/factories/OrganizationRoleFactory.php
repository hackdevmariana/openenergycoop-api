<?php

namespace Database\Factories;

use App\Models\Organization;
use App\Models\OrganizationRole;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrganizationRole>
 */
class OrganizationRoleFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = OrganizationRole::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->unique()->jobTitle();
        
        return [
            'organization_id' => Organization::factory(),
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => $this->faker->optional(0.8)->sentence(),
            'permissions' => $this->faker->optional(0.7)->randomElements([
                'project.view',
                'project.create',
                'project.update',
                'project.delete',
                'user.view',
                'user.create',
                'user.update',
                'user.delete',
                'report.view',
                'report.create',
                'report.export',
                'settings.view',
                'settings.update',
                'billing.view',
                'billing.create',
                'billing.update',
                'customer.view',
                'customer.create',
                'customer.update',
                'customer.delete',
            ], $this->faker->numberBetween(2, 8)),
        ];
    }

    /**
     * Indicate that the role is for project management.
     */
    public function projectManager(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Gestor de Proyectos',
            'slug' => 'gestor-proyectos',
            'description' => 'Responsable de gestionar y coordinar proyectos de la organización',
            'permissions' => [
                'project.view',
                'project.create',
                'project.update',
                'project.delete',
                'user.view',
                'report.view',
                'report.create',
                'report.export',
            ],
        ]);
    }

    /**
     * Indicate that the role is for technical installation.
     */
    public function technicalInstaller(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Técnico de Instalación',
            'slug' => 'tecnico-instalacion',
            'description' => 'Responsable de realizar instalaciones técnicas y mantenimientos',
            'permissions' => [
                'project.view',
                'project.update',
                'report.view',
                'report.create',
            ],
        ]);
    }

    /**
     * Indicate that the role is for customer service.
     */
    public function customerService(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Atención al Cliente',
            'slug' => 'atencion-cliente',
            'description' => 'Responsable de la atención y gestión de clientes',
            'permissions' => [
                'customer.view',
                'customer.create',
                'customer.update',
                'project.view',
                'report.view',
            ],
        ]);
    }

    /**
     * Indicate that the role is for sales.
     */
    public function sales(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Comercial',
            'slug' => 'comercial',
            'description' => 'Responsable de ventas y captación de clientes',
            'permissions' => [
                'customer.view',
                'customer.create',
                'customer.update',
                'project.view',
                'project.create',
                'report.view',
                'report.create',
            ],
        ]);
    }

    /**
     * Indicate that the role is for administration.
     */
    public function administrator(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Administrador',
            'slug' => 'administrador',
            'description' => 'Responsable de la administración y gestión general',
            'permissions' => [
                'project.view',
                'project.create',
                'project.update',
                'project.delete',
                'user.view',
                'user.create',
                'user.update',
                'user.delete',
                'report.view',
                'report.create',
                'report.export',
                'settings.view',
                'settings.update',
                'billing.view',
                'billing.create',
                'billing.update',
                'customer.view',
                'customer.create',
                'customer.update',
                'customer.delete',
            ],
        ]);
    }

    /**
     * Indicate that the role is for billing.
     */
    public function billing(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Facturación',
            'slug' => 'facturacion',
            'description' => 'Responsable de la gestión de facturación y cobros',
            'permissions' => [
                'billing.view',
                'billing.create',
                'billing.update',
                'customer.view',
                'report.view',
                'report.export',
            ],
        ]);
    }

    /**
     * Indicate that the role is for reporting.
     */
    public function reporting(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Reportes',
            'slug' => 'reportes',
            'description' => 'Responsable de la generación y análisis de reportes',
            'permissions' => [
                'report.view',
                'report.create',
                'report.export',
                'project.view',
                'customer.view',
            ],
        ]);
    }

    /**
     * Indicate that the role has no permissions.
     */
    public function noPermissions(): static
    {
        return $this->state(fn (array $attributes) => [
            'permissions' => [],
        ]);
    }

    /**
     * Indicate that the role has minimal permissions.
     */
    public function minimalPermissions(): static
    {
        return $this->state(fn (array $attributes) => [
            'permissions' => [
                'project.view',
                'customer.view',
            ],
        ]);
    }

    /**
     * Indicate that the role has read-only permissions.
     */
    public function readOnly(): static
    {
        return $this->state(fn (array $attributes) => [
            'permissions' => [
                'project.view',
                'user.view',
                'report.view',
                'customer.view',
                'billing.view',
                'settings.view',
            ],
        ]);
    }
}

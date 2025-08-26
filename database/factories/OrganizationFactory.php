<?php

namespace Database\Factories;

use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class OrganizationFactory extends Factory
{
    protected $model = Organization::class;

    public function definition(): array
    {
        $spanishData = config('spanish_data');
        
        // Seleccionar provincia aleatoria de Aragón
        $province = $this->faker->randomElement(array_keys($spanishData['aragon']['provinces']));
        
        // Seleccionar municipio aleatorio de la provincia
        $municipality = $this->faker->randomElement($spanishData['aragon']['provinces'][$province]['municipalities']);
        
        // Seleccionar tipo de cooperativa aleatorio
        $cooperativeType = $this->faker->randomElement($spanishData['spanish_companies']);
        
        // Crear nombre de la cooperativa
        $name = $cooperativeType . ' de ' . $municipality;
        
        // Generar slug
        $slug = Str::slug($name);
        
        // Generar dominio
        $domain = Str::slug($municipality) . '.' . $this->faker->randomElement($spanishData['spanish_domains']);
        
        // Generar email de contacto
        $contactEmail = 'info@' . Str::slug($municipality) . '.' . $this->faker->randomElement($spanishData['spanish_domains']);
        
        // Generar teléfono con prefijo de la provincia
        $phonePrefix = $spanishData['aragon']['provinces'][$province]['phone_prefix'];
        $contactPhone = '+' . $phonePrefix . ' ' . $this->faker->numberBetween(100000, 999999);
        
        return [
            'name' => $name,
            'slug' => $slug,
            'domain' => $domain,
            'contact_email' => $contactEmail,
            'contact_phone' => $contactPhone,
            'css_files' => null,
            'active' => $this->faker->boolean(90),
        ];
    }

    /**
     * Crear organización específica de Zaragoza
     */
    public function zaragoza(): static
    {
        return $this->state(function (array $attributes) {
            $spanishData = config('spanish_data');
            $municipality = $this->faker->randomElement($spanishData['aragon']['provinces']['Zaragoza']['municipalities']);
            $cooperativeType = $this->faker->randomElement($spanishData['spanish_companies']);
            
            $name = $cooperativeType . ' de ' . $municipality;
            $slug = Str::slug($name);
            $domain = Str::slug($municipality) . '.coop.es';
            $contactEmail = 'info@' . Str::slug($municipality) . '.coop.es';
            $contactPhone = '+976 ' . $this->faker->numberBetween(100000, 999999);
            
            return [
                'name' => $name,
                'slug' => $slug,
                'domain' => $domain,
                'contact_email' => $contactEmail,
                'contact_phone' => $contactPhone,
            ];
        });
    }

    /**
     * Crear organización específica de Huesca
     */
    public function huesca(): static
    {
        return $this->state(function (array $attributes) {
            $spanishData = config('spanish_data');
            $municipality = $this->faker->randomElement($spanishData['aragon']['provinces']['Huesca']['municipalities']);
            $cooperativeType = $this->faker->randomElement($spanishData['spanish_companies']);
            
            $name = $cooperativeType . ' de ' . $municipality;
            $slug = Str::slug($name);
            $domain = Str::slug($municipality) . '.coop.es';
            $contactEmail = 'info@' . Str::slug($municipality) . '.coop.es';
            $contactPhone = '+974 ' . $this->faker->numberBetween(100000, 999999);
            
            return [
                'name' => $name,
                'slug' => $slug,
                'domain' => $domain,
                'contact_email' => $contactEmail,
                'contact_phone' => $contactPhone,
            ];
        });
    }

    /**
     * Crear organización específica de Teruel
     */
    public function teruel(): static
    {
        return $this->state(function (array $attributes) {
            $spanishData = config('spanish_data');
            $municipality = $this->faker->randomElement($spanishData['aragon']['provinces']['Teruel']['municipalities']);
            $cooperativeType = $this->faker->randomElement($spanishData['spanish_companies']);
            
            $name = $cooperativeType . ' de ' . $municipality;
            $slug = Str::slug($name);
            $domain = Str::slug($municipality) . '.coop.es';
            $contactEmail = 'info@' . Str::slug($municipality) . '.coop.es';
            $contactPhone = '+978 ' . $this->faker->numberBetween(100000, 999999);
            
            return [
                'name' => $name,
                'slug' => $slug,
                'domain' => $domain,
                'contact_email' => $contactEmail,
                'contact_phone' => $contactPhone,
            ];
        });
    }

    /**
     * Crear organización de energía solar
     */
    public function solar(): static
    {
        return $this->state(function (array $attributes) {
            $spanishData = config('spanish_data');
            $province = $this->faker->randomElement(array_keys($spanishData['aragon']['provinces']));
            $municipality = $this->faker->randomElement($spanishData['aragon']['provinces'][$province]['municipalities']);
            
            $name = 'Cooperativa Solar de ' . $municipality;
            $slug = Str::slug($name);
            $domain = Str::slug($municipality) . '.solar.es';
            $contactEmail = 'info@' . Str::slug($municipality) . '.solar.es';
            
            return [
                'name' => $name,
                'slug' => $slug,
                'domain' => $domain,
                'contact_email' => $contactEmail,
            ];
        });
    }

    /**
     * Crear organización de energía eólica
     */
    public function eolica(): static
    {
        return $this->state(function (array $attributes) {
            $spanishData = config('spanish_data');
            $province = $this->faker->randomElement(array_keys($spanishData['aragon']['provinces']));
            $municipality = $this->faker->randomElement($spanishData['aragon']['provinces'][$province]['municipalities']);
            
            $name = 'Cooperativa Eólica de ' . $municipality;
            $slug = Str::slug($name);
            $domain = Str::slug($municipality) . '.eolica.es';
            $contactEmail = 'info@' . Str::slug($municipality) . '.eolica.es';
            
            return [
                'name' => $name,
                'slug' => $slug,
                'domain' => $domain,
                'contact_email' => $contactEmail,
            ];
        });
    }
}


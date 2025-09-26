<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\InvitationToken;
use App\Models\User;
use App\Models\Organization;
use App\Models\OrganizationRole;
use Carbon\Carbon;
use Illuminate\Support\Str;

class InvitationTokenSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::take(5)->get();
        $organizations = Organization::take(3)->get();
        $organizationRoles = OrganizationRole::take(5)->get();
        
        if ($users->isEmpty()) {
            $this->command->warn('⚠️ No hay usuarios disponibles. Saltando InvitationTokenSeeder.');
            return;
        }

        if ($organizations->isEmpty()) {
            $this->command->warn('⚠️ No hay organizaciones disponibles. Saltando InvitationTokenSeeder.');
            return;
        }

        if ($organizationRoles->isEmpty()) {
            $this->command->warn('⚠️ No hay roles de organización disponibles. Saltando InvitationTokenSeeder.');
            return;
        }

        $tokens = [];

        // Crear tokens de invitación para diferentes estados
        $statuses = ['pending', 'used', 'expired'];
        $emails = [
            'nuevo.miembro@example.com',
            'colaborador@example.com',
            'voluntario@example.com',
            'lider@example.com',
            'coordinador@example.com',
            'admin@example.com',
            'usuario@example.com',
            'miembro@example.com',
            'participante@example.com',
            'socio@example.com',
        ];

        foreach ($emails as $index => $email) {
            $organization = $organizations->random();
            $organizationRole = $organizationRoles->random();
            $invitedBy = $users->random();
            $status = $statuses[array_rand($statuses)];
            
            $expiresAt = Carbon::now()->addDays(rand(1, 30));
            $usedAt = null;
            
            if ($status === 'used') {
                $usedAt = Carbon::now()->subDays(rand(1, 15));
            } elseif ($status === 'expired') {
                $expiresAt = Carbon::now()->subDays(rand(1, 10));
            }

            $tokens[] = [
                'token' => Str::random(32),
                'email' => $email,
                'organization_role_id' => $organizationRole->id,
                'organization_id' => $organization->id,
                'invited_by' => $invitedBy->id,
                'expires_at' => $expiresAt,
                'used_at' => $usedAt,
                'status' => $status,
            ];
        }

        foreach ($tokens as $token) {
            InvitationToken::create($token);
        }

        $this->command->info('✅ InvitationTokenSeeder ejecutado correctamente');
        $this->command->info('📊 Tokens de invitación creados: ' . count($tokens));
        $this->command->info('📧 Emails únicos: ' . count($emails));
        $this->command->info('🏢 Organizaciones utilizadas: ' . $organizations->count());
        $this->command->info('👥 Roles de organización utilizados: ' . $organizationRoles->count());
        $this->command->info('⏳ Tokens pendientes: ' . collect($tokens)->where('status', 'pending')->count());
        $this->command->info('✅ Tokens usados: ' . collect($tokens)->where('status', 'used')->count());
        $this->command->info('❌ Tokens expirados: ' . collect($tokens)->where('status', 'expired')->count());
        $this->command->info('🔑 Tokens únicos generados con Str::random(32)');
    }
}
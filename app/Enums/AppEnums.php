<?php

namespace App\Enums;

/**
 * Constantes centralizadas para todos los enums de la aplicación
 * Esto evita errores de string y facilita validaciones y selects
 */
class AppEnums
{
    /**
     * Tipos de perfil de cliente
     */
    public const CUSTOMER_PROFILE_TYPES = [
        'individual' => 'Individual',
        'tenant' => 'Inquilino',
        'company' => 'Empresa',
        'ownership_change' => 'Cambio de Titularidad',
    ];

    /**
     * Tipos de identificación legal
     */
    public const LEGAL_ID_TYPES = [
        'dni' => 'DNI',
        'nie' => 'NIE',
        'passport' => 'Pasaporte',
        'cif' => 'CIF',
    ];

    /**
     * Tipos de contrato
     */
    public const CONTRACT_TYPES = [
        'own' => 'Propietario',
        'tenant' => 'Inquilino',
        'company' => 'Empresa',
        'ownership_change' => 'Cambio de Titularidad',
    ];

    /**
     * Tipos de documento legal
     */
    public const LEGAL_DOCUMENT_TYPES = [
        'dni' => 'DNI/NIE/Pasaporte',
        'iban_receipt' => 'Justificante IBAN',
        'contract' => 'Contrato',
        'invoice' => 'Factura',
        'other' => 'Otro',
    ];

    /**
     * Estados de solicitud de suscripción
     */
    public const SUBSCRIPTION_REQUEST_STATUSES = [
        'pending' => 'Pendiente',
        'approved' => 'Aprobado',
        'rejected' => 'Rechazado',
        'in_review' => 'En Revisión',
    ];

    /**
     * Tipos de solicitud de suscripción
     */
    public const SUBSCRIPTION_REQUEST_TYPES = [
        'new_subscription' => 'Nueva Suscripción',
        'ownership_change' => 'Cambio de Titularidad',
        'tenant_request' => 'Solicitud de Inquilino',
    ];

    /**
     * Tipos de logros/achievements
     */
    public const ACHIEVEMENT_TYPES = [
        'energy' => 'Energético',
        'participation' => 'Participación',
        'community' => 'Comunidad',
        'milestone' => 'Hito',
    ];

    /**
     * Estados de token de invitación
     */
    public const INVITATION_TOKEN_STATUSES = [
        'pending' => 'Pendiente',
        'used' => 'Usado',
        'expired' => 'Expirado',
        'revoked' => 'Revocado',
    ];

    /**
     * Roles de membresía de equipo
     */
    public const TEAM_MEMBERSHIP_ROLES = [
        'member' => 'Miembro',
        'admin' => 'Administrador',
        'moderator' => 'Moderador',
    ];

    /**
     * Tipos de desafío
     */
    public const CHALLENGE_TYPES = [
        'individual' => 'Individual',
        'team' => 'Equipo',
        'organization' => 'Organización',
    ];

    /**
     * Tipos de dispositivo de usuario
     */
    public const USER_DEVICE_TYPES = [
        'web' => 'Navegador Web',
        'mobile' => 'Móvil',
        'tablet' => 'Tablet',
        'desktop' => 'Escritorio',
    ];

    /**
     * Plataformas de dispositivos
     */
    public const USER_DEVICE_PLATFORMS = [
        'iOS' => 'iOS',
        'Android' => 'Android',
        'Windows' => 'Windows',
        'macOS' => 'macOS',
        'Linux' => 'Linux',
        'Chrome OS' => 'Chrome OS',
        'Web' => 'Navegador Web',
    ];

    /**
     * Tipos de consentimiento
     */
    public const CONSENT_TYPES = [
        'terms_and_conditions' => 'Términos y Condiciones',
        'privacy_policy' => 'Política de Privacidad',
        'cookies_policy' => 'Política de Cookies',
        'marketing_communications' => 'Comunicaciones de Marketing',
        'data_processing' => 'Procesamiento de Datos',
        'newsletter' => 'Newsletter',
        'analytics' => 'Analytics y Seguimiento',
        'third_party_sharing' => 'Compartir con Terceros',
    ];

    /**
     * Obtener las claves de un enum específico
     */
    public static function getKeys(string $enumName): array
    {
        $constant = "self::{$enumName}";
        if (defined($constant)) {
            return array_keys(constant($constant));
        }
        return [];
    }

    /**
     * Obtener los valores de un enum específico
     */
    public static function getValues(string $enumName): array
    {
        $constant = "self::{$enumName}";
        if (defined($constant)) {
            return constant($constant);
        }
        return [];
    }

    /**
     * Verificar si un valor es válido para un enum específico
     */
    public static function isValid(string $enumName, string $value): bool
    {
        return in_array($value, self::getKeys($enumName));
    }

    /**
     * Obtener el label de un valor específico de un enum
     */
    public static function getLabel(string $enumName, string $value): ?string
    {
        $values = self::getValues($enumName);
        return $values[$value] ?? null;
    }
}

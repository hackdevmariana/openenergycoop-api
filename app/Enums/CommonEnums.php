<?php

namespace App\Enums;

class CommonEnums
{
    // Profile Types
    const PROFILE_TYPES = [
        'individual' => 'Individual',
        'tenant' => 'Inquilino',
        'company' => 'Empresa',
        'ownership_change' => 'Cambio de Titularidad',
    ];

    // Legal ID Types
    const LEGAL_ID_TYPES = [
        'dni' => 'DNI',
        'nie' => 'NIE',
        'passport' => 'Pasaporte',
        'cif' => 'CIF',
    ];

    // Contract Types
    const CONTRACT_TYPES = [
        'own' => 'Propio',
        'tenant' => 'Inquilino',
        'company' => 'Empresa',
        'ownership_change' => 'Cambio de Titularidad',
    ];

    // Document Types
    const DOCUMENT_TYPES = [
        'dni' => 'DNI',
        'iban_receipt' => 'Comprobante IBAN',
        'contract' => 'Contrato',
        'invoice' => 'Factura',
        'other' => 'Otro',
    ];
}

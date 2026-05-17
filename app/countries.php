<?php
/**
 * Catalogos por pais. Multi-pais sin tabla porque cambian raras veces.
 * Si crece, mover a tabla.
 */

const COUNTRIES = [
    'US' => 'Estados Unidos',
    'DO' => 'Republica Dominicana',
    'MX' => 'Mexico',
    'CO' => 'Colombia',
    'VE' => 'Venezuela',
    'CU' => 'Cuba',
    'HT' => 'Haiti',
    'PR' => 'Puerto Rico',
    'CA' => 'Canada',
    'ES' => 'Espana',
    'BR' => 'Brasil',
    'AR' => 'Argentina',
    'PE' => 'Peru',
    'GT' => 'Guatemala',
    'HN' => 'Honduras',
    'SV' => 'El Salvador',
    'NI' => 'Nicaragua',
    'OTHER' => 'Otro',
];

/** Tipos de caso por pais */
const CASE_TYPES = [
    'US' => [
        'I-130'   => 'I-130 - Peticion familiar',
        'I-485'   => 'I-485 - Ajuste de estatus',
        'I-129F'  => 'I-129F - Visa K-1 (prometido/a)',
        'I-751'   => 'I-751 - Remocion de condiciones',
        'I-765'   => 'I-765 - Permiso de trabajo (EAD)',
        'I-140'   => 'I-140 - Peticion empleo',
        'I-589'   => 'I-589 - Asilo',
        'N-400'   => 'N-400 - Naturalizacion',
        'I-918'   => 'I-918 - Visa U',
        'I-360'   => 'I-360 - VAWA / Religiosos',
        'I-601'   => 'I-601 - Perdon (waiver)',
        'I-912'   => 'I-912 - Exencion de tarifa',
        'TPS'     => 'TPS - Estatus de Proteccion Temporal',
        'DACA'    => 'DACA - Accion Diferida',
        'OTHER'   => 'Otro',
    ],
    'DO' => [
        'RES_TEMP'  => 'Residencia Temporal',
        'RES_PERM'  => 'Residencia Permanente',
        'NATURALIZACION' => 'Naturalizacion',
        'VISA_NEG'  => 'Visa de negocios',
        'VISA_TUR'  => 'Visa de turismo',
        'REUN_FAM'  => 'Reunificacion familiar',
        'OTHER'     => 'Otro',
    ],
    'MX' => [
        'RES_TEMP'  => 'Residencia Temporal',
        'RES_PERM'  => 'Residencia Permanente',
        'VISA_VIS'  => 'Visa de visitante',
        'NATURALIZACION' => 'Naturalizacion',
        'OTHER'     => 'Otro',
    ],
];

const DOCUMENT_CATEGORIES = [
    'passport'      => 'Pasaporte',
    'birth_cert'    => 'Acta de nacimiento',
    'marriage_cert' => 'Acta de matrimonio',
    'divorce_cert'  => 'Divorcio',
    'i797'          => 'I-797 / Notice',
    'evidence'      => 'Evidencia',
    'translation'   => 'Traduccion',
    'photo'         => 'Foto',
    'tax_return'    => 'Declaracion impuestos',
    'employment'    => 'Empleo',
    'other'         => 'Otro',
];

const CASE_STATUSES = [
    'intake'     => 'Recepcion',
    'preparing'  => 'Preparando',
    'filed'      => 'Presentado',
    'rfe'        => 'RFE - Solicitud de evidencia',
    'approved'   => 'Aprobado',
    'denied'     => 'Denegado',
    'withdrawn'  => 'Retirado',
    'closed'     => 'Cerrado',
];

const CASE_PRIORITIES = [
    'low'    => 'Baja',
    'normal' => 'Normal',
    'high'   => 'Alta',
    'urgent' => 'Urgente',
];

function country_name($code) {
    return COUNTRIES[$code] ?? $code;
}

function case_types_for($country) {
    return CASE_TYPES[$country] ?? CASE_TYPES['US'];
}

function case_status_label($code) {
    return CASE_STATUSES[$code] ?? $code;
}

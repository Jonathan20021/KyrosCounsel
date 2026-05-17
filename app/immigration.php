<?php
/**
 * Templates de evidencia + filing fees por tipo de caso (USCIS / RD).
 * Datos de referencia 2024-2025. Actualizable via admin.
 */

const FILING_FEES = [
    // USA - USCIS (referenciales 2024)
    'I-130'  => 535,
    'I-485'  => 1440,
    'I-129F' => 535,
    'I-751'  => 595,
    'I-765'  => 410,
    'I-140'  => 715,
    'I-589'  => 0,
    'N-400'  => 760,
    'I-918'  => 0,
    'I-360'  => 515,
    'I-601'  => 1050,
    'I-912'  => 0,
    'TPS'    => 50,
    'DACA'   => 555,
    // RD - aproximados
    'RES_TEMP'       => 200,
    'RES_PERM'       => 350,
    'NATURALIZACION' => 100,
    'VISA_NEG'       => 250,
    'VISA_TUR'       => 100,
    'REUN_FAM'       => 250,
    // MX
    'VISA_VIS'       => 50,
];

const SERVICE_CENTERS = [
    'NSC' => 'Nebraska Service Center',
    'TSC' => 'Texas Service Center',
    'CSC' => 'California Service Center',
    'VSC' => 'Vermont Service Center',
    'NBC' => 'National Benefits Center',
    'PSC' => 'Potomac Service Center',
];

/**
 * Plantillas de evidencia por tipo de caso.
 * Cada item: [nombre, categoria, requerido].
 */
const EVIDENCE_TEMPLATES = [
    'I-130' => [
        ['Pasaporte del peticionario',                'passport',     1],
        ['Pasaporte del beneficiario',                'passport',     1],
        ['Acta de nacimiento del peticionario',       'birth_cert',   1],
        ['Acta de nacimiento del beneficiario',       'birth_cert',   1],
        ['Acta de matrimonio (si aplica)',            'marriage_cert',0],
        ['Foto tipo pasaporte del peticionario',      'photo',        1],
        ['Foto tipo pasaporte del beneficiario',      'photo',        1],
        ['Evidencia de matrimonio bona fide',         'evidence',     1],
        ['Pago I-130 ($535)',                         'evidence',     1],
        ['Formulario G-28 firmado',                   'evidence',     1],
    ],
    'I-485' => [
        ['Acta de nacimiento del solicitante',        'birth_cert',   1],
        ['Pasaporte del solicitante',                 'passport',     1],
        ['I-94 / record de entrada',                  'evidence',     1],
        ['I-693 (examen medico)',                     'evidence',     1],
        ['I-864 affidavit of support + evidencia',    'evidence',     1],
        ['Foto tipo pasaporte (2 copias)',            'photo',        1],
        ['Tax returns ultimos 3 anos del sponsor',    'tax_return',   1],
        ['I-797 receipt notice de I-130',             'i797',         1],
        ['Pago I-485 ($1440)',                        'evidence',     1],
    ],
    'N-400' => [
        ['Tarjeta verde (front + back)',              'evidence',     1],
        ['Pasaporte y todas las paginas con sellos',  'passport',     1],
        ['Tax returns ultimos 5 anos',                'tax_return',   1],
        ['Foto tipo pasaporte',                       'photo',        1],
        ['Evidencia de residencia continua',          'evidence',     1],
        ['Evidencia de presencia fisica',             'evidence',     1],
        ['Pago N-400 ($760)',                         'evidence',     1],
    ],
    'I-589' => [
        ['Pasaporte / documentos identidad',          'passport',     1],
        ['Declaracion personal escrita (asilo)',      'evidence',     1],
        ['Evidencia de persecucion',                  'evidence',     1],
        ['Country conditions report',                 'evidence',     1],
        ['Reportes medicos (si aplica)',              'evidence',     0],
        ['Cartas de testigos',                        'evidence',     0],
        ['Foto tipo pasaporte',                       'photo',        1],
    ],
    'I-129F' => [
        ['Pasaporte del peticionario US citizen',     'passport',     1],
        ['Pasaporte del prometido extranjero',        'passport',     1],
        ['Foto tipo pasaporte (ambos)',               'photo',        1],
        ['Evidencia de relacion (encuentros)',        'evidence',     1],
        ['Evidencia de intencion de matrimonio',      'evidence',     1],
        ['Pago I-129F ($535)',                        'evidence',     1],
    ],
    'I-751' => [
        ['Tarjeta de residente condicional',          'evidence',     1],
        ['Acta de matrimonio',                        'marriage_cert',1],
        ['Evidencia de matrimonio bona fide',         'evidence',     1],
        ['Tax returns conjuntos',                     'tax_return',   1],
        ['Documentos compartidos (lease, cuentas)',   'evidence',     1],
        ['Foto tipo pasaporte (ambos)',               'photo',        1],
        ['Pago I-751 ($595)',                         'evidence',     1],
    ],
    'I-765' => [
        ['I-94 / record de entrada',                  'evidence',     1],
        ['Pasaporte',                                 'passport',     1],
        ['Foto tipo pasaporte (2 copias)',            'photo',        1],
        ['EAD anterior si aplica',                    'evidence',     0],
        ['Evidencia de elegibilidad (categoria)',     'evidence',     1],
    ],
    'I-140' => [
        ['Carta de oferta de empleo',                 'employment',   1],
        ['Tax returns del empleador',                 'tax_return',   1],
        ['PERM Labor Certification (si aplica)',      'evidence',     1],
        ['Diplomas + transcripts del beneficiario',   'evidence',     1],
        ['Cartas de experiencia laboral',             'employment',   1],
    ],
    // Tareas estandar para casos del lado RD
    'I-130_PARENT' => [
        ['Acta de nacimiento del peticionario',                            'birth_cert', 1],
        ['Acta de nacimiento del beneficiario',                            'birth_cert', 1],
        ['Pasaporte del beneficiario',                                     'passport',   1],
        ['Evidencia de ciudadania del peticionario (pasaporte US, naturalizacion)', 'evidence', 1],
        ['Foto tipo pasaporte (ambos)',                                    'photo',      1],
        ['Pago I-130 ($535)',                                              'evidence',   1],
    ],
    'TPS' => [
        ['Pasaporte vigente del pais designado',     'passport',     1],
        ['Evidencia de residencia continua en US desde fecha designada', 'evidence', 1],
        ['Evidencia de presencia fisica continua',   'evidence',     1],
        ['Foto tipo pasaporte (2 copias)',           'photo',        1],
        ['Antecedentes penales',                     'evidence',     1],
        ['Pago I-821 ($50) + I-765 ($410) si aplica','evidence',     1],
        ['Formulario G-28',                          'evidence',     1],
    ],
    'DACA' => [
        ['Pasaporte o documento identidad pais de origen', 'passport', 1],
        ['Evidencia de llegada antes de los 16 anos',      'evidence', 1],
        ['Evidencia residencia continua desde 06/15/2007', 'evidence', 1],
        ['Diploma escolar, GED, o evidencia de estudios',  'evidence', 1],
        ['Foto tipo pasaporte (2 copias)',                 'photo',    1],
        ['Antecedentes penales',                           'evidence', 1],
        ['Pago I-821D + I-765 ($555 total)',               'evidence', 1],
    ],
    'I-918' => [
        ['Certificacion Form I-918, Supplement B firmada por agencia',     'evidence', 1],
        ['Declaracion personal del peticionario (en su idioma + traducida)','evidence', 1],
        ['Evidencia de victima de delito calificado',                       'evidence', 1],
        ['Evidencia de cooperacion con autoridades',                        'evidence', 1],
        ['Evidencia de daño fisico o mental sustancial',                    'evidence', 1],
        ['Pasaporte o documento identidad',                                  'passport', 1],
        ['Foto tipo pasaporte',                                              'photo',    1],
    ],
    'I-360' => [
        ['Pasaporte del peticionario',                       'passport',      1],
        ['Acta de matrimonio',                               'marriage_cert', 1],
        ['Evidencia de residencia conjunta con el abusador', 'evidence',      1],
        ['Evidencia de matrimonio bona fide',                'evidence',      1],
        ['Evidencia de buen caracter moral',                 'evidence',      1],
        ['Evidencia de abuso (orden de proteccion, fotos, reportes policiales, evaluaciones medicas)', 'evidence', 1],
        ['Declaracion personal detallada',                   'evidence',      1],
        ['Foto tipo pasaporte',                              'photo',         1],
    ],
    'I-601' => [
        ['Form I-601 completado',                                  'evidence', 1],
        ['Evidencia de inadmisibilidad (que requiere el waiver)',  'evidence', 1],
        ['Evidencia de extreme hardship al ciudadano US/LPR',      'evidence', 1],
        ['Documentos del familiar US: tax returns, evidencia de empleo, vivienda', 'evidence', 1],
        ['Reportes medicos/psicologicos del familiar afectado',    'evidence', 1],
        ['Cartas de apoyo de comunidad',                           'evidence', 1],
        ['Pago I-601 ($1050)',                                     'evidence', 1],
    ],
    // RD
    'RES_PERM' => [
        ['Acta de nacimiento apostillada',            'birth_cert',   1],
        ['Pasaporte vigente',                         'passport',     1],
        ['Antecedentes penales del pais origen',      'evidence',     1],
        ['Certificado medico',                        'evidence',     1],
        ['Acta de matrimonio (si aplica)',            'marriage_cert',0],
        ['Foto 2x2 (4 copias)',                       'photo',        1],
        ['Comprobante de medios economicos',          'evidence',     1],
    ],
    'NATURALIZACION' => [
        ['Cedula de residencia',                      'evidence',     1],
        ['Pasaporte',                                 'passport',     1],
        ['Comprobante de domicilio',                  'evidence',     1],
        ['Antecedentes penales',                      'evidence',     1],
        ['Foto 2x2',                                  'photo',        1],
    ],
];

function evidence_template($case_type) {
    return EVIDENCE_TEMPLATES[$case_type] ?? [];
}

/**
 * Plantillas de tareas estandar por tipo de caso.
 * Cada item: [titulo, dias_para_due_date, prioridad].
 */
const TASK_TEMPLATES = [
    'I-130' => [
        ['Recolectar evidencia matrimonio bona fide',  7,  'high'],
        ['Solicitar acta nacimiento beneficiario',    14,  'normal'],
        ['Preparar formulario I-130',                 21,  'high'],
        ['Revisar paquete con cliente',               25,  'normal'],
        ['Presentar peticion ante USCIS',             30,  'urgent'],
    ],
    'I-485' => [
        ['Verificar I-94 y record de entrada',         5,  'high'],
        ['Programar examen medico (I-693)',           10,  'high'],
        ['Recolectar tax returns ultimos 3 anos',     14,  'normal'],
        ['Preparar I-864 affidavit of support',       21,  'high'],
        ['Preparar paquete I-485',                    28,  'urgent'],
        ['Presentar ante USCIS',                      35,  'urgent'],
    ],
    'N-400' => [
        ['Verificar elegibilidad (5 anos residencia)', 3,  'high'],
        ['Recolectar tax returns 5 anos',             10,  'normal'],
        ['Preparar N-400 + evidencia',                21,  'high'],
        ['Practicar civics test con cliente',         28,  'normal'],
        ['Presentar N-400',                           30,  'urgent'],
    ],
    'I-589' => [
        ['Entrevista detallada con cliente',           3,  'urgent'],
        ['Redactar declaracion personal',             14,  'urgent'],
        ['Recolectar country conditions reports',     21,  'high'],
        ['Cartas de testigos (3+ minimo)',            28,  'high'],
        ['Presentar I-589 antes del plazo de 1 ano',  30,  'urgent'],
    ],
    'I-129F' => [
        ['Recolectar evidencia de relacion',           7,  'high'],
        ['Verificar encuentros en persona',           10,  'high'],
        ['Preparar I-129F + paquete',                 21,  'high'],
        ['Presentar peticion',                        25,  'urgent'],
    ],
    'I-751' => [
        ['Recolectar evidencia matrimonio post-residencia', 7, 'high'],
        ['Tax returns conjuntos',                     14,  'normal'],
        ['Preparar I-751',                            21,  'high'],
        ['Presentar dentro de la ventana de 90 dias', 25,  'urgent'],
    ],
    'I-765' => [
        ['Verificar categoria de elegibilidad',        3,  'normal'],
        ['Tomar fotos pasaporte',                      7,  'normal'],
        ['Preparar I-765',                            14,  'normal'],
        ['Presentar',                                 18,  'high'],
    ],
    'TPS' => [
        ['Verificar elegibilidad por pais designado',  2,  'urgent'],
        ['Recolectar evidencia de presencia fisica',  10,  'high'],
        ['Tomar fotos pasaporte',                      7,  'normal'],
        ['Preparar I-821 + I-765',                    21,  'high'],
        ['Presentar antes de fecha limite TPS',       28,  'urgent'],
    ],
    'DACA' => [
        ['Verificar fecha de llegada (antes de 16 anos)', 3, 'urgent'],
        ['Recolectar evidencia residencia continua',     14, 'high'],
        ['Recolectar evidencia educativa',                21, 'high'],
        ['Antecedentes penales',                          14, 'high'],
        ['Preparar I-821D + I-765',                       28, 'urgent'],
        ['Presentar paquete',                             30, 'urgent'],
    ],
    'I-918' => [
        ['Solicitar I-918 Supplement B a la agencia',  3, 'urgent'],
        ['Redactar declaracion personal detallada',   14, 'urgent'],
        ['Recolectar evidencia del delito',           21, 'high'],
        ['Documentar daño fisico/mental',             21, 'high'],
        ['Cartas de testigos',                        28, 'normal'],
        ['Presentar I-918',                           35, 'urgent'],
    ],
    'I-360' => [
        ['Documentar abuso (fotos, reportes, ordenes)', 7, 'urgent'],
        ['Redactar declaracion personal',             14, 'urgent'],
        ['Cartas de testigos sobre el abuso',         21, 'high'],
        ['Evaluacion psicologica si disponible',      21, 'normal'],
        ['Evidencia de buen caracter moral',          28, 'normal'],
        ['Presentar I-360',                           35, 'urgent'],
    ],
    'I-601' => [
        ['Identificar grounds de inadmisibilidad',     3, 'urgent'],
        ['Documentar extreme hardship al familiar US', 14, 'urgent'],
        ['Recolectar evidencia medica/psicologica',    21, 'high'],
        ['Tax returns y evidencia financiera',         14, 'high'],
        ['Cartas de apoyo de comunidad',               21, 'normal'],
        ['Preparar I-601',                             28, 'high'],
        ['Presentar waiver',                           30, 'urgent'],
    ],
    'RES_PERM' => [
        ['Apostillar acta de nacimiento',              7,  'high'],
        ['Solicitar antecedentes penales',            14,  'high'],
        ['Examen medico',                             21,  'normal'],
        ['Presentar solicitud DGM',                   30,  'urgent'],
    ],
    'NATURALIZACION' => [
        ['Verificar tiempo como residente',            3,  'high'],
        ['Recolectar antecedentes penales',           10,  'high'],
        ['Preparar paquete',                          21,  'normal'],
        ['Presentar solicitud',                       28,  'urgent'],
    ],
];

function task_template($case_type) {
    return TASK_TEMPLATES[$case_type] ?? [];
}

function filing_fee($case_type) {
    return FILING_FEES[$case_type] ?? 0;
}

/**
 * USCIS receipt: 3 letras + 10 digitos. Ej: EAC2412345678
 */
function is_valid_uscis_receipt($s) {
    return is_string($s) && preg_match('/^[A-Z]{3}\d{10}$/', strtoupper(trim($s)));
}

function uscis_service_center_from_receipt($s) {
    $s = strtoupper(trim($s));
    $prefix = substr($s, 0, 3);
    $map = ['EAC' => 'VSC', 'WAC' => 'CSC', 'LIN' => 'NSC', 'SRC' => 'TSC',
            'MSC' => 'NBC', 'IOE' => 'ELIS', 'YSC' => 'PSC'];
    return $map[$prefix] ?? null;
}

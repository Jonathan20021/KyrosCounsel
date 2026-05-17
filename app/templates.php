<?php
/**
 * Sistema de plantillas con merge fields tipo {{client.first_name}}
 * Variables disponibles segun contexto.
 */

const MERGE_VARS_DOC = [
    'tenant.name'      => 'Nombre del bufete',
    'tenant.country'   => 'Pais',
    'client.first_name'=> 'Nombre del cliente',
    'client.last_name' => 'Apellido del cliente',
    'client.full_name' => 'Nombre completo',
    'client.email'     => 'Email del cliente',
    'client.phone'     => 'Telefono',
    'case.number'      => 'Numero del caso',
    'case.title'       => 'Titulo del caso',
    'case.type'        => 'Tipo (I-130, I-485, etc.)',
    'case.status'      => 'Estado del caso',
    'case.uscis_receipt' => 'USCIS Receipt',
    'case.opened_at'   => 'Fecha de apertura',
    'case.filed_at'    => 'Fecha de presentacion',
    'case.interview_at'=> 'Fecha de entrevista',
    'attorney.name'    => 'Nombre del abogado',
    'attorney.email'   => 'Email del abogado',
    'today'            => 'Fecha de hoy',
    'portal_url'       => 'Link del portal cliente',
];

/**
 * Reemplaza {{vars}} en string con datos del contexto.
 * $ctx: array con keys client, case, attorney, tenant.
 */
function template_render($text, $ctx = []) {
    $vars = template_build_vars($ctx);
    return preg_replace_callback('/\{\{\s*([\w\.]+)\s*\}\}/', function ($m) use ($vars) {
        return $vars[$m[1]] ?? '';
    }, $text);
}

function template_build_vars($ctx) {
    $tenant = current_tenant();
    $vars = [
        'today'         => date('d/m/Y'),
        'tenant.name'   => $tenant['name'] ?? '',
        'tenant.country'=> country_name($tenant['country'] ?? ''),
    ];
    if (!empty($ctx['client'])) {
        $c = $ctx['client'];
        $vars['client.first_name'] = $c['first_name'] ?? '';
        $vars['client.last_name']  = $c['last_name']  ?? '';
        $vars['client.full_name']  = trim(($c['first_name'] ?? '') . ' ' . ($c['last_name'] ?? ''));
        $vars['client.email']      = $c['email'] ?? '';
        $vars['client.phone']      = $c['phone'] ?? '';
    }
    if (!empty($ctx['case'])) {
        $cs = $ctx['case'];
        $vars['case.number']        = $cs['case_number'] ?? '';
        $vars['case.title']         = $cs['title'] ?? '';
        $vars['case.type']          = $cs['case_type'] ?? '';
        $vars['case.status']        = case_status_label($cs['status'] ?? '');
        $vars['case.uscis_receipt'] = $cs['uscis_receipt'] ?? '';
        $vars['case.opened_at']     = $cs['opened_at'] ?? '';
        $vars['case.filed_at']      = $cs['filed_at'] ?? '';
        $vars['case.interview_at']  = $cs['interview_at'] ?? '';
    }
    if (!empty($ctx['attorney'])) {
        $a = $ctx['attorney'];
        $vars['attorney.name']  = $a['name'] ?? '';
        $vars['attorney.email'] = $a['email'] ?? '';
    }
    if (!empty($ctx['portal_url'])) {
        $vars['portal_url'] = $ctx['portal_url'];
    }
    return $vars;
}

/** Plantillas seed por defecto (se insertan al onboarding o pueden ser creadas manualmente) */
const DEFAULT_TEMPLATES = [
    [
        'slug' => 'welcome', 'category' => 'welcome',
        'name' => 'Bienvenida al cliente',
        'subject' => 'Bienvenido a {{tenant.name}}, {{client.first_name}}',
        'body_html' => '<p>Hola <strong>{{client.first_name}}</strong>,</p><p>Bienvenido a {{tenant.name}}. Hemos creado tu expediente y estamos listos para empezar a trabajar en tu caso.</p><p>Tu numero de caso es <strong>{{case.number}}</strong> ({{case.type}}).</p><p>Puedes seguir el progreso en cualquier momento aqui: <a href="{{portal_url}}">Portal del cliente</a></p><p>Saludos cordiales,<br>{{attorney.name}}</p>',
    ],
    [
        'slug' => 'status_filed', 'category' => 'status_update',
        'name' => 'Caso presentado ante USCIS',
        'subject' => 'Buenas noticias: tu caso {{case.number}} fue presentado',
        'body_html' => '<p>Hola <strong>{{client.first_name}}</strong>,</p><p>Tenemos buenas noticias. Hoy presentamos formalmente tu caso <strong>{{case.type}}</strong> ante USCIS.</p><p>Receipt number: <strong>{{case.uscis_receipt}}</strong></p><p>El proximo paso es esperar a que USCIS procese tu solicitud. Te avisaremos en cuanto tengamos noticias.</p><p>Saludos,<br>{{attorney.name}}</p>',
    ],
    [
        'slug' => 'document_request', 'category' => 'document_request',
        'name' => 'Solicitar documentos al cliente',
        'subject' => 'Necesitamos algunos documentos para tu caso',
        'body_html' => '<p>Hola <strong>{{client.first_name}}</strong>,</p><p>Para continuar con tu caso <strong>{{case.number}}</strong>, necesitamos que nos hagas llegar los siguientes documentos:</p><ul><li>[Lista aqui]</li></ul><p>Por favor envialos a <a href="mailto:{{attorney.email}}">{{attorney.email}}</a> o subelos al portal: <a href="{{portal_url}}">Portal del cliente</a></p><p>Gracias,<br>{{attorney.name}}</p>',
    ],
    [
        'slug' => 'interview_reminder', 'category' => 'reminder',
        'name' => 'Recordatorio de entrevista USCIS',
        'subject' => 'Recordatorio: entrevista USCIS el {{case.interview_at}}',
        'body_html' => '<p>Hola <strong>{{client.first_name}}</strong>,</p><p>Te recordamos que tienes una entrevista con USCIS el <strong>{{case.interview_at}}</strong>.</p><p>Por favor confirma tu asistencia y revisa la lista de documentos que debes llevar.</p><p>Cualquier duda contactanos.</p><p>Saludos,<br>{{attorney.name}}</p>',
    ],
];

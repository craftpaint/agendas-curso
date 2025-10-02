<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Services\WhatsappService;
use App\Services\CrmService;

class UtilsHelper {

    protected $sendPulseWhatsapp;
    protected $sendPulseCrm;

    public function __construct(WhatsappService $sendPulseWhatsapp, CrmService $sendPulseCrm) {
        $this->sendPulseWhatsapp = $sendPulseWhatsapp;
        $this->sendPulseCrm = $sendPulseCrm;
    }

    public function enviarConfirmacionWhatsappCliente($id_cita) {
        $cita = DB::table('tb_cita')->where('id_cita', $id_cita)->first();
        $cliente = DB::table('tb_cliente')->where('id_cliente', $cita->id_cliente)->first();
        $agente = DB::table('users')->where('id', $cita->id_agente_callcenter)->first();
        $sede = DB::table('tb_sede')->where('id_sede', $cita->id_sede)->first();

        if ($agente->id_chatbot_sendpulse && $agente->id_user_sendpulse && $agente->id_plantilla_sendpulse) {
            $datosUsuario = $this->sendPulseWhatsapp->searchContactByPhone($cliente->telefono_cliente, $agente->id_chatbot_sendpulse);
            //Corrección de estructura de datos
            $fechaFormateada = date('Y/m/d', strtotime($cita->reserva_cita));
            $partes = explode('-', $cita->rango_horario);
            $horaFormateada = trim($partes[0] ?? '');

            $data = [
                'bot_id' => $agente->id_chatbot_sendpulse,
                'phone' => $cliente->telefono_cliente,
                'template' => [
                    'name' => $agente->id_plantilla_sendpulse,
                    'components' => [
                        [
                            'type' => 'body',
                            'parameters' => [
                                [
                                    'type' => 'text',
                                    'text' => $cliente->nombre_cliente
                                ],
                                [
                                    'type' => 'text',
                                    'text' => $cliente->apellido_cliente
                                ],
                                [
                                    'type' => 'text',
                                    'text' => $agente->name
                                ],
                                [
                                    'type' => 'text',
                                    'text' => $fechaFormateada
                                ],
                                [
                                    'type' => 'text',
                                    'text' => $horaFormateada
                                ],
                                [
                                    'type' => 'text',
                                    'text' => $sede->nombre_sede
                                ],
                                [
                                    'type' => 'text',
                                    'text' => $sede->direccion_sede
                                ]
                            ]
                        ]
                    ],
                    'language' => [
                        'policy' => 'deterministic',
                        'code' => 'es'
                    ]
                ]
            ];

            //Enviar mensaje de confirmación por Whatsapp al cliente
            if($datosUsuario){
                $TemplateEnviado = $this->sendPulseWhatsapp->sendWhatsappTemplateByPhone($data);
                if (!$TemplateEnviado) {
                    Log::error("No se pudo enviar la plantilla de confirmación de Whatsapp al cliente");
                    return false;
                }

                //Se asigna el id de whatsapp a la cita
                DB::table('tb_cita')
                    ->where('tb_cita.id_cita', $id_cita)
                    ->update([
                        'tb_cita.id_whatsapp_sendpulse' => $datosUsuario['data']['id'],
                        'tb_cita.updated_at' => Carbon::now()
                    ]);

                $operadorAsignado = $this->sendPulseWhatsapp->assignOperatorToContact($datosUsuario['data']['id'], $agente->id_user_sendpulse);
                if (!$operadorAsignado) {
                    Log::error("No se pudo asignar el operador al contacto de Whatsapp.");
                    return false;
                }
            } else {
                $contactoCreado = $this->sendPulseWhatsapp->createContactWhatsapp($cliente->telefono_cliente, $cliente->nombre_cliente, [], [], $agente->id_chatbot_sendpulse);
                $TemplateEnviado = $this->sendPulseWhatsapp->sendWhatsappTemplateByPhone($data);
                if (!$contactoCreado || !$TemplateEnviado) {
                    Log::error("No se pudo crear el contacto de Whatsapp y tampoco enviar la plantilla de confirmación de cita al cliente");
                    return false;
                }
                
                $datosUsuario = $this->sendPulseWhatsapp->searchContactByPhone($cliente->telefono_cliente, $agente->id_chatbot_sendpulse);
                if (!$datosUsuario) {
                    Log::error("No se encontró el contacto de Whatsapp creado.");
                    return false;
                }

                //Se asigna el id de whatsapp a la cita
                DB::table('tb_cita')
                    ->where('tb_cita.id_cita', $id_cita)
                    ->update([
                        'tb_cita.id_whatsapp_sendpulse' => $datosUsuario['data']['id'],
                        'tb_cita.updated_at' => Carbon::now()
                    ]);

                $operadorAsignado = $this->sendPulseWhatsapp->assignOperatorToContact($datosUsuario['data']['id'], $agente->id_user_sendpulse);
                if (!$operadorAsignado) {
                    Log::error("No se pudo asignar el operador al contacto de Whatsapp.");
                    return false;
                }
            }

            // Crear usuario de CRM y crear el trato
            $datosUsuarioCRM = $this->sendPulseCrm->searchContactByExternalContactId($datosUsuario['data']['id']);
            if ($datosUsuarioCRM) {
                $tratoCreado = $this->sendPulseCrm->createDealCrm($agente->id_user_sendpulse, $cliente->nombre_cliente, $cliente->apellido_cliente, $sede->nombre_sede, $datosUsuarioCRM['data']['id']);
                if (!$tratoCreado) {
                    Log::error("No se pudo crear el trato en el CRM.");
                    return false;
                }

                // Se asigna el ID del trato en la cita
                DB::table('tb_cita')
                    ->where('tb_cita.id_cita', $id_cita)
                    ->update([
                        'tb_cita.id_trato_sendpulse' => $tratoCreado['data']['id'],
                        'tb_cita.updated_at' => Carbon::now()
                    ]);

                $tratoAsignado = $this->sendPulseCrm->assignDealToContact($tratoCreado['data']['id'], $datosUsuarioCRM['data']['id']);
                if (!$tratoAsignado) {
                    Log::error("No se pudo asignar el trato al contacto en el CRM.");
                    return false;
                }
            } else {
                $contactoCrmCreado = $this->sendPulseCrm->createContactCrm($cliente->nombre_cliente, $cliente->apellido_cliente, $agente->id_user_sendpulse, $datosUsuario['data']['id']);
                $contactoCrmEncontrado = $this->sendPulseCrm->searchContactByExternalContactId($datosUsuario['data']['id']);
                if (!$contactoCrmCreado || !$contactoCrmEncontrado) {
                    Log::error("No se pudo crear el contacto en el CRM o no se encontró después de crearlo.");
                    return false;
                }

                $messengerAsignado = $this->sendPulseCrm->assignMessengerContactCrm($cliente->telefono_cliente, $agente->id_chatbot_sendpulse, $datosUsuario['data']['id'], $contactoCrmEncontrado['data']['id']);
                if (!$messengerAsignado) {
                    Log::error("No se pudo asignar el chat del bot al contacto de CRM.");
                    return false;
                }

                $tratoCreado = $this->sendPulseCrm->createDealCrm($agente->id_user_sendpulse, $cliente->nombre_cliente, $cliente->apellido_cliente, $sede->nombre_sede, $contactoCrmEncontrado['data']['id']);
                if (!$tratoCreado) {
                    Log::error("No se pudo crear el trato en el CRM.");
                    return false;
                }

                // Se asigna el ID del trato en la cita
                DB::table('tb_cita')
                    ->where('tb_cita.id_cita', $id_cita)
                    ->update([
                        'tb_cita.id_trato_sendpulse' => $tratoCreado['data']['id'],
                        'tb_cita.updated_at' => Carbon::now()
                    ]);

                $tratoAsignado = $this->sendPulseCrm->assignDealToContact($tratoCreado['data']['id'], $contactoCrmEncontrado['data']['id']);
                if (!$tratoAsignado) {
                    Log::error("No se pudo asignar el trato al contacto en el CRM.");
                    return false;
                }
            }
        }
        return true;
    }

    public function limpiezaHtmlSimit($html) {
        try {
            $html = stripslashes($html);
            $html = html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $html = preg_replace('/[\x00-\x1F\x7F]/u', '', $html);

            $dom = new \DOMDocument();
            libxml_use_internal_errors(true);
            $dom->loadHTML('<?xml encoding="UTF-8">' . $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
            $xpath = new \DOMXPath($dom);

            // Eliminar el elemento con aria-labelledby="descripcionEstadoDeCuenta"
            foreach ($xpath->query('//*[@aria-labelledby="descripcionEstadoDeCuenta"]') as $element) {
                $element->parentNode->removeChild($element);
            }

            // Eliminar el div que contiene el input con id="txtBusqueda"
            foreach ($xpath->query('//input[@id="txtBusqueda"]/ancestor::div[1]') as $div) {
                $div->parentNode->removeChild($div);
            }

            // Eliminar los divs con id="enviarCorreo" e id="historialCursos"
            foreach ($xpath->query('//div[@id="enviarCorreo" or @id="historialCursos"]') as $div) {
                $div->parentNode->removeChild($div);
            }

            // Eliminar los div que contienen la clase "justify-content-end"
            foreach ($xpath->query('//div[contains(@class, "justify-content-end")]') as $div) {
                $div->parentNode->removeChild($div);
            }

            // Eliminar la columna del checkbox (th con id="chkMulta" y todas las celdas correspondientes)
            foreach ($xpath->query('//th[@id="chkMulta"]') as $th) {
                $th->parentNode->removeChild($th);
            }

            foreach ($xpath->query('//table[@id="multaTable"]//td[.//input[contains(@id, "chkMulta")]]') as $td) {
                $td->parentNode->removeChild($td);
            }

            foreach ($xpath->query('//td[@data-label="Selecciona para pagar"]') as $td) {
                $td->parentNode->removeChild($td);
            }

            // Eliminar clases de ancho fijo de las columnas
            $widthClasses = ['w-180', 'w-135', 'w-90', 'w-105', 'w-150', 'w-23'];
            foreach ($widthClasses as $class) {
                foreach ($xpath->query('//table[@id="multaTable"]//*[contains(@class, "' . $class . '")]') as $element) {
                    $currentClass = $element->getAttribute('class');
                    $newClass = str_replace($class, '', $currentClass);
                    $element->setAttribute('class', trim($newClass));
                }
            }

            // Ajustar la tabla para que sea responsive sin scroll
            foreach ($xpath->query('//table[@id="multaTable"]') as $table) {
                $table->setAttribute('style', 'width: 100%');
                // Eliminar clases que puedan forzar un ancho fijo
                $currentClass = $table->getAttribute('class');
                $newClass = str_replace(['table-multas-responsive', 'pagination-header'], '', $currentClass);
                $table->setAttribute('class', trim($newClass . ' table table-hover'));
            }

            // Ajustar el contenedor de la tabla
            foreach ($xpath->query('//div[contains(@class, "table-responsive")]') as $div) {
                $div->setAttribute('style', 'overflow-x: visible; width: 100%');
            }

            // Simplificar las celdas y eliminar contenido innecesario
            foreach ($xpath->query('//table[@id="multaTable"]//td//p[@data-step="10"]') as $p) {
                $p->parentNode->removeChild($p);
            }

            foreach ($xpath->query('//table[@id="multaTable"]//td//div[@data-step="11"]') as $div) {
                $div->parentNode->removeChild($div);
            }

            // Ajustar el ancho de las columnas restantes
            $headers = $xpath->query('//table[@id="multaTable"]//th');
            foreach ($headers as $header) {
                $header->removeAttribute('style');
                $header->setAttribute('style', 'min-width: auto; max-width: none;');
            }

            $cells = $xpath->query('//table[@id="multaTable"]//td');
            foreach ($cells as $cell) {
                $cell->removeAttribute('style');
                $cell->setAttribute('style', 'min-width: auto; max-width: none; white-space: normal;');
            }

            // Eliminar listas de progreso y filas vacías
            foreach ($xpath->query('//ul[contains(@class,"resprogressbar") and contains(@class,"list-unstyled")]') as $ul) {
                $ul->parentNode->removeChild($ul);
            }

            $trs = $xpath->query('//tr');
            for ($i = 0; $i < $trs->length; $i++) {
                $tr = $trs->item($i);

                if (trim($tr->textContent) === '') {
                    $prev = $tr->previousSibling;
                    $next = $tr->nextSibling;

                    while ($prev && $prev->nodeName !== 'tr') {
                        $prev = $prev->previousSibling;
                    }

                    while ($next && $next->nodeName !== 'tr') {
                        $next = $next->nextSibling;
                    }

                    if ($prev && $next) {
                        foreach (iterator_to_array($next->childNodes) as $child) {
                            if ($child->nodeName === 'td' || $child->nodeName === 'th') {
                                $prev->appendChild($child->cloneNode(true));
                            }
                        }

                        $tr->parentNode->removeChild($tr);
                        $next->parentNode->removeChild($next);
                    } else {
                        $tr->parentNode->removeChild($tr);
                    }
                }
            }
            
            return $dom->saveHTML();
        } catch (\Throwable $e) {
            Log::error("Error en limpiezaHtmlSimit: " . $e->getMessage());
        }
    }
}
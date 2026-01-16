<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ValidateMultiComparendo
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->has('comparendos')) {
            $comparendos = $request->input('comparendos');
            
            // Validar cantidad
            if (count($comparendos) > config('multi-comparendo.max_comparendos')) {
                return response()->json([
                    'validate' => false,
                    'text' => config('multi-comparendo.mensajes.limite_alcanzado', 
                        ['max' => config('multi-comparendo.max_comparendos')])
                ]);
            }
            
            // Validar campos requeridos
            foreach ($comparendos as $index => $comparendo) {
                if (empty($comparendo['reserva_cita']) || empty($comparendo['id_sede_horario'])) {
                    return response()->json([
                        'validate' => false,
                        'text' => "Comparendo " . ($index + 1) . ": " . 
                                 config('multi-comparendo.mensajes.campos_requeridos')
                    ]);
                }
            }
        }
        
        return $next($request);
    }
}
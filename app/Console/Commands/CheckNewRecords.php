<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class CheckNewRecords extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'records:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verifica nuevos registros en las últimas 5 minutos y los guarda en la caché';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            // Calcula los registros de los últimos 5 minutos
            $fecha_inicio = now()->setTimezone(config('app.timezone'))->subMinutes(5);
            $ahora = now()->setTimezone(config('app.timezone'));
            $registros_todas_sedes = DB::table('tb_cita')
                ->where('created_at', '>=', $fecha_inicio)
                ->count();

            // Guarda los datos en la caché por 5 minutos
            Cache::put('new_records_global', $registros_todas_sedes, now()->addMinutes(5));

            // Guarda la hora exacta de la última ejecución del cron
            Cache::put('last_cron_execution', now());

            $this->info("Registros procesados: $registros_todas_sedes.");
            $this->info("fecha de ejecución:  $ahora ");
        } catch (\Exception $e) {
            $this->error("Error al verificar los registros: " . $e->getMessage());
        }

        return 0;
    }
}

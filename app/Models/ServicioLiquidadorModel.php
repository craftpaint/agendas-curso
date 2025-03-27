<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DateTimeInterface;


class ServicioLiquidadorModel extends Model
{
    use HasFactory;
    protected $table = 'tb_servicio_liquidador';
    protected $primaryKey = 'id_servicio_liquidador';
    protected $fillable = [
        'nombre_estado',
        'desc_estado',
        'nombre_servicio_liquidador',
        'valor_servicio_liquidador',
        'color_servicio_liquidador',
    ];
    protected $dates = [
        'created_at',
        'updated_at'
    ];
    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DateTimeInterface;


class LiquidadorModel extends Model
{
    use HasFactory;
    protected $table = 'tb_liquidador';
    protected $primaryKey = 'id_liquidador';
    protected $fillable = [
        'id_cita',
        'estado_liquidador',
        'comentario_liquidador',
        'pago_liquidador',
        'Created_at',
        'Updated_at'
    ];
    protected $dates = [
        'Created_at',
        'Updated_at'
    ];
    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}

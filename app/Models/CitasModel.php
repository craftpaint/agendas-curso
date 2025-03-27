<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DateTimeInterface;
use Carbon\Carbon;

class CitasModel extends Model
{
    use HasFactory;
    protected $table = 'tb_cita';
    protected $primaryKey = 'id_cita';
    protected $fillable = [
        'id_cliente',
        'id_sede',
        'id_estado',
        'id_estado_verificado',
        'rango_horario',
        'reserva_cita',
        'desc_cita',
        'created_at',
        'updated_at'
    ];
    protected $dates = [
        'created_at',
        'updated_at'
    ];
    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    // Mutator para created_at
    public function getCreatedAtAttribute($value)
    {
        return Carbon::createFromTimestamp(strtotime($value))
            ->timezone('America/Bogota')
            ->toDateTimeString()
        ;
    }

    // public function setCreatedAtAttribute($value)
    // {
    //     $this->attributes['created_at'] = Carbon::now('America/Bogota');
    // }

    // // Mutator para updated_at
    // public function setUpdatedAtAttribute($value)
    // {
    //     $this->attributes['updated_at'] = Carbon::now('America/Bogota');
    // }
}

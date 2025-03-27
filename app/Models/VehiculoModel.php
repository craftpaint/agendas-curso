<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DateTimeInterface;
class VehiculoModel extends Model
{
    use HasFactory;
    protected $table = 'tb_vehiculo';
    protected $primaryKey = 'id_vehiculo';
    protected $fillable = [
            'id_cliente'
        ,   'tipo_vehiculo'
        ,   'placa_vehiculo'
        ,   'modelo_vehiculo'
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

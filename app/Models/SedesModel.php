<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DateTimeInterface;
class SedesModel extends Model
{
    use HasFactory;
    protected $table = 'tb_sede';
    protected $primaryKey = 'id_sede';
    protected $fillable = [
            'idrun_sede'
        ,   'nombre_sede'
        ,   'direccion_sede'
        ,   'tel_sede'
        ,   'estado_sede'
        ,   'festivos_sede'
        ,   'id_servicio'
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

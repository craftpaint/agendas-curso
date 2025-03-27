<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DateTimeInterface;
class ServiciosModel extends Model
{
    use HasFactory;
    protected $table = 'tb_servicio';
    protected $primaryKey = 'id_servicio';
    protected $fillable = [
            'tipo_servicio'
        ,   'desc_servicio'
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

<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DateTimeInterface;
class SeguimientoModel extends Model
{
    use HasFactory;
    protected $table = 'tb_seguimiento';
    protected $primaryKey = 'id_seguimiento ';
    protected $fillable = [
            'titulo_seguimiento'
        ,   'nota_seguimiento'
        ,   'id_cita'
        ,   'id_user'
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

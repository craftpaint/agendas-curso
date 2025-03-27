<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DateTimeInterface;
class ClientesModel extends Model
{
    use HasFactory;
    protected $table = 'tb_cliente';
    protected $primaryKey = 'id_cliente';
    protected $fillable = [
            'nombre_cliente'
        ,   'apellido_cliente'
        ,   'tipo_doc_cliente'
        ,   'doc_cliente'
        ,   'telefono_cliente'
        ,   'email_cliente'
        ,   'desc_cliente'
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

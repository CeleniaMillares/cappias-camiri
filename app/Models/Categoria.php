<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Concerns\HasTitleCase;

class Categoria extends Model {
    use HasUuids, SoftDeletes, HasTitleCase;
    protected $table = 'categorias';
    protected $primaryKey = 'id_categoria';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['nombre', 'descripcion', 'activo'];

    protected function titleCaseAttributes(): array
    {
        return ['nombre'];
    }

    public function productos() {
        return $this->hasMany(Producto::class, 'id_categoria', 'id_categoria');
    }
}
<?php

/**
 * Created by Reliese Model.
 * Date: Thu, 10 Sep 2020 16:07:16 +0000.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class Section
 * 
 * @property int $id
 * @property int $rapports_id
 * @property int $create_at
 * @property int $update_at
 * @property string $titre
 * @property string $contenu
 * 
 * @property \App\Models\Rapport $rapport
 *
 * @package App\Models
 */
class Section extends Eloquent
{
	public $timestamps = false;

	protected $casts = [
		'rapports_id' => 'int',
		'create_at' => 'int',
		'update_at' => 'int'
	];

	protected $fillable = [
		'rapports_id',
		'create_at',
		'update_at',
		'titre',
		'contenu'
	];

	public function rapport()
	{
		return $this->belongsTo(\App\Models\Rapport::class, 'rapports_id');
	}
}
